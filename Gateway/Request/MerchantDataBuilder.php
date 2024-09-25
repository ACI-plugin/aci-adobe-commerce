<?php
namespace Aci\Payment\Gateway\Request;

use Aci\Payment\Gateway\Config\AciGenericPaymentConfig;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Aci\Payment\Helper\Constants;

/**
 * Builds merchant details for Aci payment method API call.
 */
class MerchantDataBuilder implements BuilderInterface
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
     * Builds ENV request
     *
     * @param array<mixed> $buildSubject
     * @return array<mixed>
     */
    public function build(array $buildSubject):array
    {
        $requestData = [
            Constants::KEY_ACI_PAYMENT_ENTITY_ID => $this->paymentConfig->getEntityId()
        ];
        $testMode = $this->paymentConfig->getTestMode();
        if ($testMode) {
            $requestData[Constants::KEY_TEST_MODE] = $testMode;
        }
        return $requestData;
    }
}
