<?php
namespace Aci\Payment\Plugin\Method;

use Magento\Payment\Model\Method\Adapter;
use Aci\Payment\Gateway\Config\AciGenericPaymentConfig;
use Aci\Payment\Helper\Constants;

/**
 * Class AdapterPlugin - Class to override isActive method
 */
class AdapterPlugin
{

    /**
     * @var AciGenericPaymentConfig
     */
    private AciGenericPaymentConfig $paymentConfig;

    /**
     * MerchantDataBuilder constructor.
     *
     * @param AciGenericPaymentConfig $paymentConfig
     */
    public function __construct(
        AciGenericPaymentConfig $paymentConfig,
    ) {
        $this->paymentConfig = $paymentConfig;
    }

    /**
     * Method to check if Aci Generic is enabled.
     *
     * @param Adapter $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsActive(Adapter $subject, bool $result): bool
    {
        if ($result) {
            $isAciGenericEnabled = $this->paymentConfig->getValue(
                Constants::KEY_ACTIVE,
            );
            $result = (bool)$isAciGenericEnabled;
        }
        return $result;
    }
}
