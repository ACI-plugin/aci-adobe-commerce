<?php
namespace Aci\Payment\Model\Transaction;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Command\ResultInterface;
use TryzensIgnite\Base\Api\QuoteManagerInterface;
use TryzensIgnite\Base\Api\OrderManagerInterface;
use Aci\Payment\Model\Order\OrderManager;
use Aci\Payment\Model\Api\AciPaymentResponseManager;
use TryzensIgnite\Base\Model\Transaction\TransactionManager as IgniteTransactionManager;
use Aci\Payment\Model\Request\GetStatusRequestManager;
use Aci\Payment\Model\Data\SavedCard as SavedCardData;
use TryzensIgnite\Onsite\Model\Ui\CcConfigProvider;
use Aci\Payment\Helper\Utilities;
use Aci\Payment\Helper\Constants;
use Aci\Payment\Logger\AciLogger;
use Aci\Payment\Model\Request\CreateSubscriptionRequestManager;
use Aci\Payment\Api\RecurringOrderRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

/**
 *
 * Manages Aci Transactions
 */
class AciTransactionManager extends IgniteTransactionManager
{

    /**
     * @var OrderManager
     */
    private OrderManager $aciOrderManager;

    /**
     * @var SavedCardData
     */
    private SavedCardData $savedCardData;

    /**
     * @var Utilities
     */
    protected Utilities $utilities;

    /**
     * @var AciLogger
     */
    private AciLogger $logger;

    /**
     * @var CreateSubscriptionRequestManager
     */
    private CreateSubscriptionRequestManager $createSubscriptionRequestManager;
    /**
     * @var RecurringOrderRepositoryInterface
     */
    private RecurringOrderRepositoryInterface $recurringOrderRepository;

    /**
     * @var GetStatusRequestManager
     */
    protected GetStatusRequestManager $requestManager;

    /**
     * @var CartRepositoryInterface
     */
    protected CartRepositoryInterface $cartRepository;

    /**
     * @param OrderManagerInterface $orderManager
     * @param QuoteManagerInterface $quoteManager
     * @param GetStatusRequestManager $requestManager
     * @param AciPaymentResponseManager $responseManager
     * @param OrderManager $aciOrderManager
     * @param SavedCardData $savedCardData
     * @param Utilities $utilities
     * @param AciLogger $logger
     * @param CreateSubscriptionRequestManager $createSubscriptionRequestManager
     * @param RecurringOrderRepositoryInterface $recurringOrderRepository
     * @param CartRepositoryInterface $cartRepository
     */
    //phpcs:disable
    public function __construct(
        OrderManagerInterface $orderManager,
        QuoteManagerInterface $quoteManager,
        GetStatusRequestManager $requestManager,
        AciPaymentResponseManager $responseManager,
        OrderManager $aciOrderManager,
        SavedCardData     $savedCardData,
        Utilities $utilities,
        AciLogger $logger,
        CreateSubscriptionRequestManager $createSubscriptionRequestManager,
        RecurringOrderRepositoryInterface $recurringOrderRepository,
        CartRepositoryInterface $cartRepository
    ) {
        $this->aciOrderManager = $aciOrderManager;
        $this->savedCardData   = $savedCardData;
        $this->utilities = $utilities;
        $this->logger = $logger;
        $this->createSubscriptionRequestManager = $createSubscriptionRequestManager;
        $this->recurringOrderRepository = $recurringOrderRepository;
        $this->requestManager = $requestManager;
        $this->cartRepository = $cartRepository;
        parent::__construct(
            $orderManager,
            $quoteManager,
            $responseManager,
            $savedCardData,
            $cartRepository
        );
    }

    /**
     * Process transaction response
     *
     * @param array<mixed> $params
     * @param string $method
     * @return array<mixed>|bool
     * @throws LocalizedException
     */
    public function processSuccessResponse(array $params, string $method): array|bool
    {
        $checkoutId = $params['id'] ?? null;
        $lastOrderId = $this->orderManager->getLastOrderId();
        $order = $this->orderManager->getOrder($lastOrderId);
        $quoteId = (int)$order->getQuoteId();
        /** @var Quote $quote */
        $quote = $this->cartRepository->get($quoteId);
        $processStatus = ['status' => false];
        $isAuthOnly = false;
        $isCaptured = false;
        $manualReview = false;
        try {
            //Call Get Status.
            $transactionResponse = $this->getStatus($params);

            //If the response is not in correct format, return
            if (!is_array($transactionResponse)) {
                return $processStatus;
            }
            $resultCode = $transactionResponse['result']['code'] ?? null;
            if (!$resultCode) {
                return $processStatus;
            }
            //Check if regex decision is correctly set in the response. If not, return.
            $responseStatus = $this->utilities->validateResponse($resultCode);
            $this->logger->info('Response status after validation - ' . $responseStatus);
            if ($responseStatus === Constants::REJECTED || $responseStatus === Constants::FAILED) {
                $this->orderManager->cancelMagentoOrder(
                    $lastOrderId,
                    $checkoutId
                );
                return $processStatus;
            }
            if ($lastOrderId) {
                //Validate transaction amount from the response with the order amount.
                /** @var  AciPaymentResponseManager $responseManager */
                $responseManager = $this->responseManager;
                $lastIncrementId = (string)$responseManager->getOrderIncrementIdFromResponse($transactionResponse);
                $isValidTransaction = $this->aciOrderManager->isValidOrder($transactionResponse, $lastIncrementId);
                if (!$isValidTransaction) {
                    $this->logger->error(__('Error when validating order details from Get Status response.
                    Order is invalid'));
                    $this->orderManager->updateOrderStatus(
                        sprintf(
                            'Payment failed/Suspected Fraud. Amount mismatch noticed. TransactionId: %s',
                            $checkoutId
                        ),
                        OrderManagerInterface::ORDER_STATE_PAYMENT_REVIEW,
                        OrderManagerInterface::ORDER_STATUS_PAYMENT_REVIEW,
                        $lastOrderId
                    );
                    return $processStatus;
                }
                if ($responseStatus === Constants::PENDING) {
                    $manualReview = true;
                }
                if (!$manualReview){
                    $isAuthOnly = $responseManager->isAuthOnly($transactionResponse);
                    $isCaptured = $responseManager->isCaptureMode($transactionResponse);
                }
                $paymentBrand = $this->aciOrderManager->getPaymentBrandFromResponse($transactionResponse);
                //Process order
                $lastProcessedOrderId = (int)$this->aciOrderManager->processOrder(
                    $transactionResponse,
                    $paymentBrand,
                    $isAuthOnly,
                    $isCaptured,
                    $manualReview
                );

                if ($lastProcessedOrderId) {
                    $customerId = $this->orderManager->getCustomerId($lastProcessedOrderId);
                    //Disable current quote
                    $this->quoteManager->disableQuote($quote);
                    if ($customerId && $responseManager->isTokenAvailable($transactionResponse)) {
                        $method = CcConfigProvider::CODE;
                        $this->savedCardData->savePaymentCard($transactionResponse, $customerId, $method);
                    }
                    $this->callSchedulerApi($transactionResponse, $lastOrderId, $lastIncrementId);
                    $processStatus = ['status' => true];
                } else {
                    $this->orderManager->cancelMagentoOrder(
                        $lastOrderId,
                        $checkoutId
                    );
                }
                return $processStatus;
            }
            return $processStatus;
        } catch (Exception $e) {
            $this->logger->error(__('Exception occurred, while processing response -
            AciTransactionManager::processSuccessResponse() - ' . $e->getMessage()));
            throw new LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Calls GetTransaction and retry if fails
     *
     * @param array<mixed> $requestParams
     * @return array|bool|ResultInterface|mixed[]|null
     * @throws LocalizedException
     */
    public function getStatus(array $requestParams): array|ResultInterface|bool|null
    {
        return $this->requestManager->process($requestParams);
    }

    /**
     * Return transaction id from response parameter array
     *
     * @param array<mixed> $requestParams
     * @return mixed|null
     */
    public function getTransactionId(array $requestParams): mixed
    {
        return $requestParams[Constants::URL_PARAM_ID] ?? null;
    }

    /**
     * Calls Scheduler creation API and retry if fails
     *
     * @param array<mixed> $transactionResponse
     * @param int $lastOrderId
     * @param string $lastIncrementId
     * @return void
     * @throws LocalizedException
     */
    public function callSchedulerApi(array $transactionResponse, int $lastOrderId, string $lastIncrementId): void
    {
        if (isset($transactionResponse['standingInstruction']['recurringType'])) {
            if ($transactionResponse['standingInstruction']['recurringType'] == 'SUBSCRIPTION') {
                $recurringOrder = $this->recurringOrderRepository->getByOrderId($lastOrderId);
                $recurringOrder->setRegistrationId($transactionResponse['registrationId']);
                $recurringOrder->setLastOrderId($lastOrderId);
                $recurringOrder->setLastIncrementId($lastIncrementId);
                $this->recurringOrderRepository->save($recurringOrder);
                $this->createSubscriptionRequestManager->process($transactionResponse);
            }
        }
    }
}
