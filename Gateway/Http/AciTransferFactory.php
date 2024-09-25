<?php
namespace Aci\Payment\Gateway\Http;

use Aci\Payment\Gateway\Config\AciGenericPaymentConfig;
use TryzensIgnite\Common\Gateway\Http\TransferFactoryInterface;
use TryzensIgnite\Common\Helper\Constants;
use Aci\Payment\Helper\Constants as AciHelperConstants;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferInterface;

/**
 * TransferFactory for storefront transaction commands
 */
class AciTransferFactory implements TransferFactoryInterface
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
     * @param PaymentDataObjectInterface $payment
     * @return TransferInterface
     */
    public function create(array $request, PaymentDataObjectInterface $payment):TransferInterface
    {
        return $this->transferBuilder
            ->setUri($this->config->getApiEndPoint() . $this->getApiUri($request[AciHelperConstants::KEY_CHECKOUT_ID]))
            ->setMethod($this->method)
            ->setBody($request)
            ->setHeaders([ 'Content-Type' => Constants::CONTENT_TYPE_JSON])
            ->build();
    }

    /**
     * Return the api uri
     *
     * @param string $checkoutId
     * @return string
     */
    protected function getApiUri(string $checkoutId): string
    {
        return sprintf(
            AciHelperConstants::END_POINT_GET_STATUS,
            $checkoutId
        );
    }
}
