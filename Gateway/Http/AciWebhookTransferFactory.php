<?php
namespace Aci\Payment\Gateway\Http;

use Aci\Payment\Gateway\Config\AciGenericPaymentConfig;
use Aci\Payment\Helper\Constants as AciConstants;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferInterface;

/**
 * TransferFactory for webhook transaction commands
 */
class AciWebhookTransferFactory implements WebhookTransferFactoryInterface
{
    /**
     * @var TransferBuilder
     */
    private TransferBuilder $transferBuilder;

    /**
     * @var AciGenericPaymentConfig
     */
    private AciGenericPaymentConfig $config;

    /**
     * @var string
     */
    protected string $apiEndPoint;

    /**
     * @var string
     */
    protected string $method;

    /**
     * @param TransferBuilder $transferBuilder
     * @param AciGenericPaymentConfig $config
     * @param string $apiEndPoint
     * @param string $method
     */
    public function __construct(
        TransferBuilder $transferBuilder,
        AciGenericPaymentConfig $config,
        string $apiEndPoint,
        string $method
    ) {
        $this->transferBuilder = $transferBuilder;
        $this->config= $config;
        $this->apiEndPoint= $apiEndPoint;
        $this->method = $method;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array<mixed> $request
     * @return TransferInterface
     */
    public function create(array $request):TransferInterface
    {
        $credentials = $this->config->getCredentials();
        $isSchedulerCall = false;
        $checkoutId = '';
        if (isset($request[AciConstants::KEY_STANDING_INSTRUCTION_SOURCE]) &&
            $request[AciConstants::KEY_STANDING_INSTRUCTION_SOURCE] ==
            AciConstants::SCHEDULER_STANDING_INSTRUCTION_SOURCE) {
            $isSchedulerCall = true;
        }

        if (!$isSchedulerCall && isset($request[AciConstants::GET_TRANSACTION_ID])) {
            $checkoutId = $request[AciConstants::GET_TRANSACTION_ID];
        }
        return $this->transferBuilder
            ->setUri($this->config->getApiEndPoint() .
                $this->getApiUri($isSchedulerCall, $checkoutId))
            ->setMethod($this->method)
            ->setBody($request)
            ->setHeaders($this->getHeaders($credentials))
            ->build();
    }

    /**
     * Return the api uri
     *
     * @param bool $isSchedulerCall
     * @param string $checkoutId
     * @return string
     */
    protected function getApiUri(bool $isSchedulerCall, string $checkoutId): string
    {
        if ($isSchedulerCall) {
            return AciConstants::END_POINT_SUBSCRIPTION;
        }
        $endPoint = sprintf(
            $this->apiEndPoint,
            $checkoutId
        );
        $entityId = $this->config->getEntityId();
        return $endPoint . '?entityId=' . $entityId;
    }

    /**
     * Get headers for API call
     *
     * @param array<mixed> $credentials
     * @return array<mixed>
     */
    public function getHeaders(array $credentials): array
    {
        return [
            'Content-Type' => AciConstants::ACI_PAYMENT_HEADER_CONTENT_TYPE,
            'Authorization' => 'Bearer ' . $credentials['apiKey']
        ];
    }
}
