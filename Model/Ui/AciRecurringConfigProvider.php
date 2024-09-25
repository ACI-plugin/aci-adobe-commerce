<?php

namespace Aci\Payment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Aci\Payment\Helper\Constants;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class AciRecurringConfigProvider - Configuration class for Recurring Order
 */
class AciRecurringConfigProvider implements ConfigProviderInterface
{
    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve supported payment methods - ACI
     *
     * @return array<mixed>
     */
    public function getConfig(): array
    {
        $apmPaymentMethods = [];
        $subscribePaymentMethods = $this->scopeConfig->getValue(Constants::PATH_TO_SUPPORTED_PAYMENT_METHODS);
        if ($subscribePaymentMethods) {
            $apmPaymentMethods = explode(',', $subscribePaymentMethods);
        }
        $cardPaymentMethod = [
            AciCcConfigProvider::CODE
        ];
        return [
            'recurring' => [
                'payment_methods' => array_merge($cardPaymentMethod, $apmPaymentMethods)
            ]
        ];
    }
}
