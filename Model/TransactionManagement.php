<?php

namespace Aci\Payment\Model;

use Magento\Framework\Exception\LocalizedException;
use TryzensIgnite\Subscription\Model\Config as SubscriptionModelConfig;
use TryzensIgnite\Subscription\Model\TransactionManagement as IgniteTransactionManagement;
use Aci\Payment\Helper\Constants;
use Aci\Payment\Gateway\Config\AciGenericPaymentConfig;
use TryzensIgnite\Common\Model\Http\Request;
use Aci\Payment\Helper\Utilities;
use TryzensIgnite\Subscription\Api\RecurringOrderRepositoryInterface;

/**
 * Transaction operation
 */
class TransactionManagement extends IgniteTransactionManagement
{
    public const TEST_MODE_INTERNAL = 'INTERNAL';

    /**
     * @var AciGenericPaymentConfig
     */
    protected AciGenericPaymentConfig $aciGenericPaymentConfig;

    /**
     * @var Request
     */
    protected Request $request;

    /**
     * @var Utilities
     */
    protected Utilities $utilities;

    /**
     * @var RecurringOrderRepositoryInterface
     */
    protected RecurringOrderRepositoryInterface $recurringOrderRepository;

    /**
     * @param AciGenericPaymentConfig $aciGenericPaymentConfig
     * @param Request $request
     * @param Utilities $utilities
     * @param RecurringOrderRepositoryInterface $recurringOrderRepository
     * @param SubscriptionModelConfig $config
     */
    public function __construct(
        AciGenericPaymentConfig $aciGenericPaymentConfig,
        Request $request,
        Utilities $utilities,
        RecurringOrderRepositoryInterface $recurringOrderRepository,
        SubscriptionModelConfig $config
    ) {
        $this->aciGenericPaymentConfig = $aciGenericPaymentConfig;
        $this->request = $request;
        $this->utilities = $utilities;
        $this->recurringOrderRepository = $recurringOrderRepository;
        parent::__construct($config);
    }

    /**
     * Cancel subscription payment
     *
     * @param array<mixed> $transactionResponse
     * @param string $registrationId
     * @return bool
     * @throws LocalizedException
     */
    protected function cancelSubscriptionPayment(array $transactionResponse, string $registrationId): bool
    {
        $requiredParams = $this->getRequiredParams($transactionResponse, $registrationId);
        $cancelRequest = array_merge($requiredParams, $this->prepareCancelRequest());
        $paymentId = $this->getPaymentId($transactionResponse);
        return $this->callBackofficeOperation($cancelRequest, $paymentId);
    }

    /**
     * Refund subscription payment
     *
     * @param array<mixed> $transactionResponse
     * @param string $registrationId
     * @return bool
     * @throws LocalizedException
     */
    protected function refundSubscriptionPayment(array $transactionResponse, string $registrationId): bool
    {
        $requiredParams = $this->getRequiredParams($transactionResponse, $registrationId);
        $refundRequest = array_merge($requiredParams, $this->prepareRefundRequest());
        $paymentId = $this->getPaymentId($transactionResponse);
        return $this->callBackofficeOperation($refundRequest, $paymentId);
    }

    /**
     * Get required params for back office operation
     *
     * @param array<mixed> $transactionResponse
     * @param string $registrationId
     * @return array<mixed>
     * @throws LocalizedException
     */
    private function getRequiredParams(array $transactionResponse, string $registrationId): array
    {
        $requiredParams = [];
        $amount = $transactionResponse[Constants::KEY_ACI_PAYMENT_AMOUNT] ?? null;
        $currency = $transactionResponse[Constants::KEY_ACI_PAYMENT_CURRENCY] ?? null;
        if ($currency && $amount) {
            $requiredParams = [
                Constants::KEY_ACI_PAYMENT_CURRENCY => $currency,
                Constants::KEY_ACI_PAYMENT_AMOUNT => $amount

            ];
            if ($this->aciGenericPaymentConfig->isTestMode()) {
                $requiredParams[Constants::KEY_TEST_MODE] = $this->getTestMode($registrationId);
            }
        }
        return $requiredParams;
    }

    /**
     * Call the backoffice request
     *
     * @param array<mixed> $requestData
     * @param string $paymentId
     * @return bool
     * @throws LocalizedException
     */
    private function callBackofficeOperation(array $requestData, string $paymentId): bool
    {
        $endpoint = $this->prepareEndpoint();
        $refundPaymentUrl = $endpoint . $paymentId;
        $operationRequest = http_build_query($requestData);
        $requestUrl = $refundPaymentUrl . '?' . $operationRequest;
        $response = $this->request->sendRequest(Constants::API_METHOD_POST, $requestUrl);
        return $this->validateResponse($response);
    }

    /**
     * Get the backoffice end point
     *
     * @return string
     */
    protected function prepareEndpoint(): string
    {
        $apiEndPoint = $this->aciGenericPaymentConfig->getApiEndPoint();
        return $apiEndPoint . Constants::END_POINT_BACKOFFICE_OPERATION;
    }

    /**
     * Get payment ID
     *
     * @param array<mixed> $response
     * @return string
     */
    private function getPaymentId(array $response): string
    {
        return $response['id'] ?? '';
    }

    /**
     * Prepare cancel request
     *
     * @return array<mixed>
     */
    private function prepareCancelRequest(): array
    {
        return $this->prepareRequest(Constants::PAYMENT_TYPE_CANCEL);
    }

    /**
     * Prepare Refund request
     *
     * @return array<mixed>
     */
    private function prepareRefundRequest(): array
    {
        return $this->prepareRequest(Constants::PAYMENT_TYPE_REFUND);
    }

    /**
     * Prepare request for cancel and refund
     *
     * @param string $paymentActionType
     * @return array<mixed>
     */
    private function prepareRequest(string $paymentActionType): array
    {
        return [
            Constants::KEY_ACI_PAYMENT_ENTITY_ID => $this->aciGenericPaymentConfig->getEntityId(),
            Constants::KEY_ACI_PAYMENT_PAYMENT_TYPE => $paymentActionType
        ];
    }

    /**
     * Get the test mode from subscription table
     *
     * @param string $registrationId
     * @return string
     * @throws LocalizedException
     */
    private function getTestMode(string $registrationId): string
    {
        $recurringInterface = $this->recurringOrderRepository->getByRegistrationId($registrationId);
        return $recurringInterface->getTestMode() ?? self::TEST_MODE_INTERNAL;
    }

    /**
     * Validate the response
     *
     * @param array<mixed> $response
     * @return bool
     */
    private function validateResponse(array $response): bool
    {
        $resultCode = $response[Constants::KEY_NOTIFICATION_RESULT]
                        [Constants::KEY_NOTIFICATION_CODE] ?? null;
        if ($resultCode) {
            $this->utilities->isSuccessResponse($resultCode);
        }
        return false;
    }
}
