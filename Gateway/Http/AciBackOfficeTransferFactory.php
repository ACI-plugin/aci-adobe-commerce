<?php
namespace Aci\Payment\Gateway\Http;

use Aci\Payment\Gateway\Config\AciGenericPaymentConfig;
use TryzensIgnite\Common\Gateway\Http\TransferFactoryInterface;
use TryzensIgnite\Common\Helper\Constants;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferInterface;
use Aci\Payment\Helper\Constants as AciConstants;

/**
 * TransferFactory for Back Office Operation
 */
class AciBackOfficeTransferFactory implements TransferFactoryInterface
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
     * @param TransferBuilder $transferBuilder
     * @param AciGenericPaymentConfig $config
     */
    public function __construct(
        TransferBuilder $transferBuilder,
        AciGenericPaymentConfig $config
    ) {
        $this->transferBuilder = $transferBuilder;
        $this->config = $config;
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
        $checkoutId = '';
        if (isset($request[AciConstants::KEY_CHECKOUT_ID])) {
            $checkoutId = $request[AciConstants::KEY_CHECKOUT_ID];
            unset($request[AciConstants::KEY_CHECKOUT_ID]);
        }
        return $this->transferBuilder
            ->setUri($this->config->getApiEndPoint() . AciConstants::END_POINT_BACKOFFICE_OPERATION. $checkoutId)
            ->setMethod(AciConstants::API_METHOD_POST)
            ->setBody($request)
            ->setHeaders([ 'Content-Type' => Constants::CONTENT_TYPE_JSON])
            ->build();
    }
}
