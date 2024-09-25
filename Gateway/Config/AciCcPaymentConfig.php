<?php

namespace Aci\Payment\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Aci\Payment\Model\Ui\AciCcConfigProvider;
use Aci\Payment\Helper\Utilities;
use Magento\Framework\Exception\NoSuchEntityException;
use TryzensIgnite\Common\Gateway\Config\PaymentConfig;
use Aci\Payment\Helper\Constants;

/**
 * Payment configuration class for card payment type
 */
class AciCcPaymentConfig extends PaymentConfig
{
    /**
     * @var Utilities
     */
    protected Utilities $utilities;

    /**
     * @var AciGenericPaymentConfig
     */
    protected AciGenericPaymentConfig $aciGenericPaymentConfig;

    /**
     * @var AciApmPaymentConfig
     */
    protected AciApmPaymentConfig $aciApmPaymentConfig;

    /**
     * @param Utilities $utilities
     * @param ScopeConfigInterface $scopeConfig
     * @param AciGenericPaymentConfig $aciGenericPaymentConfig
     * @param AciApmPaymentConfig $aciApmPaymentConfig
     * @param string $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        Utilities $utilities,
        ScopeConfigInterface $scopeConfig,
        AciGenericPaymentConfig $aciGenericPaymentConfig,
        AciApmPaymentConfig $aciApmPaymentConfig,
        string               $methodCode = AciCcConfigProvider::CODE,
        string               $pathPattern = self::DEFAULT_PATH_PATTERN,
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
        $this->utilities = $utilities;
        $this->aciGenericPaymentConfig = $aciGenericPaymentConfig;
        $this->aciApmPaymentConfig = $aciApmPaymentConfig;
    }

    /**
     * Get logo images from config
     *
     * @return array<mixed>
     * @throws NoSuchEntityException
     */
    public function getLogos(): array
    {
        $logos = $this->getValue(Constants::KEY_CARD_TYPE_ICONS);
        $logoImageFileNames = [];
        if ($logos) {
            $logoImageFileNames = $this->utilities->unSerialize($logos);
            if (!empty($logoImageFileNames)) {
                foreach ($logoImageFileNames as $key => $logo) {
                    $logoImageFileNames[$key] = $this->aciApmPaymentConfig->getMediaUrl($logo);
                }
            }
        }
        return $logoImageFileNames;
    }

    /**
     * Get Supported Card types from config
     *
     * @return string|null
     */
    public function getSupportedCardTypes(): ?string
    {
        $supportedCards = $this->getValue(Constants::KEY_SUPPORTED_CARD_TYPES);
        if ($supportedCards) {
            $cards = explode(',', $supportedCards);
            $cardsRemoveWhiteSpace = array_map('trim', $cards);
            $cardsToUC = array_map('strtoupper', $cardsRemoveWhiteSpace);
            return implode(' ', $cardsToUC);
        }
        return null;
    }

    /**
     * Get Payment configuration status based on generic module status
     *
     * @return bool
     */
    public function isActive(): bool
    {
        $isAciGenericEnabled = $this->aciGenericPaymentConfig->getValue(
            Constants::KEY_ACTIVE,
        );
        return $this->getValue(Constants::KEY_ACTIVE) && $isAciGenericEnabled;
    }
}
