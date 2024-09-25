<?php
namespace Aci\Payment\Gateway\Http;

use Aci\Payment\Gateway\Config\AciGenericPaymentConfig;
use Aci\Payment\Helper\Constants as AciConstants;
use TryzensIgnite\Common\Helper\Constants;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferInterface;
use TryzensIgnite\Common\Gateway\Http\WebhookTransferFactoryInterface;

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
        $isSchedulerCall = false;
        $checkoutId = '';
        if (isset($request[AciConstants::KEY_STANDING_INSTRUCTION_SOURCE]) &&
            $request[AciConstants::KEY_STANDING_INSTRUCTION_SOURCE] ==
            AciConstants::SCHEDULER_STANDING_INSTRUCTION_SOURCE) {
            $isSchedulerCall = true;
        }
        if (!$isSchedulerCall && isset($request[Constants::GET_TRANSACTION_ID])) {
            $checkoutId = $request[Constants::GET_TRANSACTION_ID];
        }
        return $this->transferBuilder
            ->setUri($this->config->getApiEndPoint() .
                $this->getApiUri($isSchedulerCall, $checkoutId))
            ->setMethod($this->method)
            ->setBody($request)
            ->setHeaders([ 'Content-Type' => Constants::CONTENT_TYPE_JSON])
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
        return sprintf(
            $this->apiEndPoint,
            $checkoutId
        );
    }
}
