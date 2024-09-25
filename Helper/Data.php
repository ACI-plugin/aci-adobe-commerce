<?php
namespace Aci\Payment\Helper;

use Aci\Payment\Gateway\Config\AciCcPaymentConfig;
use Aci\Payment\Gateway\Config\AciApmPaymentConfig;
use Aci\Payment\Gateway\Config\AciGenericPaymentConfig;
use Aci\Payment\Model\Adminhtml\Source\PaymentAction;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

/**
 * Helper class for generating data for API Call
 */
class Data
{
    /**
     * @var AciCcPaymentConfig
     */
    private AciCcPaymentConfig $ccPaymentConfig;

    /**
     * @var AciGenericPaymentConfig
     */
    private AciGenericPaymentConfig $genericPaymentConfig;

    /**

     * @var RemoteAddress
     */
    private RemoteAddress $remoteAddress;

    /**
     * @var AciApmPaymentConfig
     */
    protected AciApmPaymentConfig $aciApmPaymentConfig;

    /**
     * Data Helper constructor.
     * @param AciCcPaymentConfig $ccPaymentConfig
     * @param AciGenericPaymentConfig $genericPaymentConfig
     * @param RemoteAddress $remoteAddress
     * @param AciApmPaymentConfig $aciApmPaymentConfig
     */
    public function __construct(
        AciCcPaymentConfig $ccPaymentConfig,
        AciGenericPaymentConfig $genericPaymentConfig,
        RemoteAddress $remoteAddress,
        AciApmPaymentConfig $aciApmPaymentConfig
    ) {
        $this->ccPaymentConfig = $ccPaymentConfig;
        $this->genericPaymentConfig = $genericPaymentConfig;
        $this->remoteAddress = $remoteAddress;
        $this->aciApmPaymentConfig = $aciApmPaymentConfig;
    }

    /**
     * Prepare checkout payment widget URL.
     *
     * @return string
     */
    public function getPrepareCheckoutUrl(): string
    {
        $urlEndpoint = $this->genericPaymentConfig->getApiEndPoint();
        return $urlEndpoint.Constants::END_POINT_PAYMENT_WIDGET;
    }

    /**
     * Get payment type code based on payment action for card type payments.
     *
     * @return string
     */
    public function getCcPaymentType(): string
    {
        $paymentAction = $this->ccPaymentConfig->getValue(Constants::KEY_PAYMENT_ACTION);
        return $this->getPaymentType($paymentAction);
    }

    /**
     * Get payment type code based on payment action for APM type payments.
     *
     * @param string $brandName
     * @return string
     * @throws NoSuchEntityException
     */
    public function getApmPaymentType(string $brandName): string
    {
        $paymentAction = $this->aciApmPaymentConfig->getPaymentAction($brandName);
        return $this->getPaymentType($paymentAction);
    }

    /**
     * Get Payment Type from payment action
     *
     * @param string $paymentAction
     * @return string
     */
    protected function getPaymentType(string $paymentAction): string
    {
        return match ($paymentAction) {
            PaymentAction::ACTION_AUTHORIZE => Constants::PAYMENT_TYPE_AUTH,
            PaymentAction::ACTION_SALE => Constants::PAYMENT_TYPE_SALE,
            default => Constants::DEFAULT_PAYMENT_TYPE_APM
        };
    }

    /**
     * Return Customer IP Address.
     *
     * @return bool|string
     */
    public function getCustomerIpAddress(): bool|string
    {
        return $this->remoteAddress->getRemoteAddress();
    }
}
