<?php

declare(strict_types=1);

namespace Aci\Payment\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Model\Url as CustomerUrlManager;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use Aci\Payment\Model\Subscription\Config;
use TryzensIgnite\Base\Model\Utilities\Config as UtilityConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Aci\Payment\Model\Ui\AciCcConfigProvider;
use Aci\Payment\Helper\Constants;
use Aci\Payment\Gateway\Config\AciGenericPaymentConfig;

/**
 * Adds subscription enabled status to checkout config
 *
 */
class SubscriptionConfigProvider implements ConfigProviderInterface
{
    /**
     * @var ManageSubscriptionFrequency
     */
    protected ManageSubscriptionFrequency $manageSubscriptionFrequency;

    /**
     * @var UtilityConfig
     */
    private UtilityConfig $config;

    /**
     * @var CustomerUrlManager
     */
    private CustomerUrlManager $customerUrlManager;

    /**
     * @var Serializer
     */
    protected Serializer $serializer;

    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @var AciGenericPaymentConfig
     */
    private AciGenericPaymentConfig $paymentConfig;

    /**
     * @param UtilityConfig $config
     * @param CustomerUrlManager $customerUrlManager
     * @param Serializer $serializer
     * @param ScopeConfigInterface $scopeConfig
     * @param AciGenericPaymentConfig $paymentConfig
     * @param ManageSubscriptionFrequency $manageSubscriptionFrequency
     */
    public function __construct(
        UtilityConfig $config,
        CustomerUrlManager $customerUrlManager,
        Serializer $serializer,
        ScopeConfigInterface $scopeConfig,
        AciGenericPaymentConfig $paymentConfig,
        ManageSubscriptionFrequency $manageSubscriptionFrequency
    ) {
        $this->config = $config;
        $this->customerUrlManager = $customerUrlManager;
        $this->serializer = $serializer;
        $this->scopeConfig = $scopeConfig;
        $this->paymentConfig = $paymentConfig;
        $this->manageSubscriptionFrequency = $manageSubscriptionFrequency;
    }

    /**
     * Add subscription status value to checkout config array
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
                'status' => $this->isSubscriptionActive(),
                'loginUrl' => $this->customerUrlManager->getLoginUrl(),
                'frequency' => $this->getRecurringFrequency(Config::KEY_RECURRING_FREQUENCY),
                'payment_methods' => array_merge($cardPaymentMethod, $apmPaymentMethods)
            ]
        ];
    }

    /**
     * Get Recurring Frequency
     *
     * @param string $key
     * @return array<mixed>
     */
    public function getRecurringFrequency(string $key): array
    {
        $optionData = [];
        $this->manageSubscriptionFrequency->clearSubscriptionDataFromSession();
        $recurringFrequency = $this->config->getConfig($key);
        if ($recurringFrequency) {
            $optionDataArray = (array)$this->serializer->unserialize($recurringFrequency);
            foreach ($optionDataArray as $key => $data) {
                $optionData[$key] = [
                    'displayName' => $data['display_name'],
                    'description' => $data['description'],
                    'unit' => $data['unit'],
                    'valueOfUnit' => $data['unit_value']
                ];
            }
        }
        return $optionData;
    }

    /**
     * Check subscription module is active
     *
     * @return int
     */
    public function isSubscriptionActive(): int
    {
        $isAciGenericEnabled = $this->paymentConfig->getValue(Constants::KEY_ACTIVE);
        if ($isAciGenericEnabled) {
            return (int)$this->config->getConfig(Config::KEY_ACTIVE);
        }
        return 0;
    }
}
