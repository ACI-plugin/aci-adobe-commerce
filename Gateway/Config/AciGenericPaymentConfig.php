<?php

namespace Aci\Payment\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Aci\Payment\Model\Ui\AciGenericConfigProvider;
use TryzensIgnite\Common\Gateway\Config\Config as CommonConfig;
use Magento\Payment\Gateway\Config\Config;
use TryzensIgnite\Common\Gateway\Config\PaymentConfig;
use Aci\Payment\Helper\Constants;

/**
 * Payment configuration class for Aci general settings
 */
class AciGenericPaymentConfig extends PaymentConfig
{
    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        string               $pathPattern = Config::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, AciGenericConfigProvider::CODE, $pathPattern);
    }
    // phpcs:enable
    /**
     * Get entity id from payment config
     *
     * @return string|null
     */
    public function getEntityId(): ?string
    {
        if ($this->isTestMode()) {
            return $this->getValue(Constants::KEY_TEST_ENTITY_ID);
        }
        return $this->getValue(Constants::KEY_LIVE_ENTITY_ID);
    }

    /**
     * Get Api key from payment config
     *
     * @return string|null
     */
    public function getApiKey(): ?string
    {
        if ($this->isTestMode()) {
            return $this->getValue(
                CommonConfig::KEY_TEST_PUBLIC_KEY
            );
        }
        return $this->getValue(
            CommonConfig::KEY_LIVE_PUBLIC_KEY
        );
    }

    /**
     * Get custom script from config
     *
     * @return string|null
     */
    public function getCustomScript(): ?string
    {
        return $this->getValue(
            Constants::KEY_ACI_JAVASCRIPT
        );
    }

    /**
     * Get Test Mode from config
     *
     * @return string|null
     */
    public function getTestMode(): ?string
    {
        if ($this->isTestMode()) {
            return $this->getValue(Constants::KEY_TEST_MODE_CONFIG);
        }
        return null;
    }

    /**
     * Get webhook Encryption secret from configuration.
     *
     * @return string|null
     */
    public function getWebhookEncryptionSecret(): ?string
    {
        $key = $this->isTestMode() ? Constants::KEY_TEST_WEBHOOK_ENCRYPTION_SECRET
            : Constants::KEY_LIVE_WEBHOOK_ENCRYPTION_SECRET;
        return trim((string)$this->getValue(
            $key
        ));
    }
}
