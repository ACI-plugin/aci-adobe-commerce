<?php
namespace Aci\Payment\Block\Payment;

use Aci\Payment\Helper\Constants;
use Magento\Framework\View\Element\Template;
use Aci\Payment\Gateway\Config\AciGenericPaymentConfig;

/**
 * Get custom script from configuration
 */
class CustomScript extends Template
{
    /**
     * @var AciGenericPaymentConfig
     */
    private AciGenericPaymentConfig $genericPaymentConfig;

    /**
     * @param Template\Context $context
     * @param AciGenericPaymentConfig $genericPaymentConfig
     * @param array<mixed> $data
     */
    public function __construct(
        Template\Context $context,
        AciGenericPaymentConfig $genericPaymentConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->genericPaymentConfig = $genericPaymentConfig;
    }

    /**
     * Get custom script from configuration
     *
     * @return mixed|null
     */
    public function getCustomScript(): mixed
    {
        return $this->genericPaymentConfig->getValue(Constants::KEY_ACI_JAVASCRIPT);
    }

    /**
     * Get custom css from configuration
     *
     * @return mixed|null
     */
    public function getCustomCss(): mixed
    {
        return $this->genericPaymentConfig->getValue(Constants::KEY_ACI_CSS);
    }
}
