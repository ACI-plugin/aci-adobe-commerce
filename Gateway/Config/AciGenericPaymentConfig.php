<?php

namespace Aci\Payment\Gateway\Config;

use TryzensIgnite\Base\Gateway\Config\Config as BaseConfig;
use Aci\Payment\Helper\Constants;

/**
 * Payment configuration class for Aci general settings
 */
class AciGenericPaymentConfig extends BaseConfig
{
    public const XML_PATH_DEFAULT_LOCALE               = 'general/locale/code';

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
        $credentials = $this->getCredentials();
        return $credentials['apiKey'] ?? '';
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

    /**
     * Check if debug is enabled
     *
     * @return mixed|null
     */
    public function isDebugEnabled(): mixed
    {
        return $this->getValue(Constants::KEY_DEBUG);
    }
}
