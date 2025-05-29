<?php

namespace Aci\Payment\Gateway\Config;

use TryzensIgnite\Onsite\Gateway\Config\CcConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Aci\Payment\Helper\Utilities;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json as Serializer;
use Magento\Store\Model\StoreManagerInterface;
use Aci\Payment\Helper\Constants;
use TryzensIgnite\Base\Gateway\Config\Config as BaseGatewayConfig;
use TryzensIgnite\Onsite\Model\Ui\CcConfigProvider;

/**
 * Payment configuration class for card payment type
 */
class AciCcPaymentConfig extends CcConfig
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
     * @param BaseGatewayConfig $baseGatewayConfig
     * @param ScopeConfigInterface $scopeConfig
     * @param Serializer $serializer
     * @param StoreManagerInterface $storeManager
     * @param Utilities $utilities
     * @param AciGenericPaymentConfig $aciGenericPaymentConfig
     * @param AciApmPaymentConfig $aciApmPaymentConfig
     * @param string $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        BaseGatewayConfig $baseGatewayConfig,
        ScopeConfigInterface $scopeConfig,
        Serializer  $serializer,
        StoreManagerInterface $storeManager,
        Utilities $utilities,
        AciGenericPaymentConfig $aciGenericPaymentConfig,
        AciApmPaymentConfig $aciApmPaymentConfig,
        string               $methodCode = CcConfigProvider::CODE,
        string               $pathPattern = self::DEFAULT_PATH_PATTERN,
    ) {
        parent::__construct(
            $baseGatewayConfig,
            $scopeConfig,
            $serializer,
            $storeManager,
            $methodCode,
            $pathPattern
        );
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
