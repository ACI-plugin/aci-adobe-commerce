<?php

namespace Aci\Payment\Block\Customer;

use Exception;
use Aci\Payment\Helper\Constants;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\AbstractCardRenderer;
use Aci\Payment\Model\Ui\AciCcConfigProvider;
use Aci\Payment\Gateway\Config\AciCcPaymentConfig;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\CcConfigProvider;

/**
 * Renders customer saved cards
 */
class CardRenderer extends AbstractCardRenderer
{
    /**
     * @var AciCcPaymentConfig
     */
    private AciCcPaymentConfig $paymentConfig;

    /**
     * @var array|string[]
     */
    private array $availableCardTypes = [
        'amex'                  => 'AE',
        'visa'                  => 'VI',
        'master'                => 'MC'
    ];

    /**
     * @var string
     */
    private string $cardType = '';

    /**
     * @param AciCcPaymentConfig $paymentConfig
     * @param Context $context
     * @param CcConfigProvider $iconsProvider
     * @param array<mixed> $data
     */
    public function __construct(
        AciCcPaymentConfig $paymentConfig,
        Context $context,
        CcConfigProvider $iconsProvider,
        array $data
    ) {
        parent::__construct($context, $iconsProvider, $data);
        $this->paymentConfig = $paymentConfig;
    }
    /**
     * Can render specified token
     *
     * @param PaymentTokenInterface $token
     * @return boolean
     */
    public function canRender(PaymentTokenInterface $token): bool
    {
        if ($this->paymentConfig->isActive()) {
            return $token->getPaymentMethodCode() === AciCcConfigProvider::CODE;
        }
        return false;
    }

    /**
     * Gets card's last 4 digits
     *
     * @return string
     */
    public function getNumberLast4Digits(): string
    {
        $tokenCardDetails =  $this->getTokenDetails();
        if ($tokenCardDetails && isset($tokenCardDetails[Constants::MASKED_CARD_NUMBER])) {
            $last4Digits = $tokenCardDetails[Constants::MASKED_CARD_NUMBER];
        }
        return !empty($last4Digits) ? $last4Digits : '';
    }

    /**
     * Gets card's expiry date
     *
     * @return string
     * @throws Exception
     */
    public function getExpDate(): string
    {
        $tokenCardDetails =  $this->getTokenDetails();

        if (is_array($tokenCardDetails)
            && array_key_exists(Constants::CARD_EXPIRATION_MONTH, $tokenCardDetails)
            && array_key_exists(Constants::CARD_EXPIRATION_YEAR, $tokenCardDetails)) {
            return $tokenCardDetails[Constants::CARD_EXPIRATION_MONTH] . '/' .
                $tokenCardDetails[Constants::CARD_EXPIRATION_YEAR];
        }
        return '';
    }

    /**
     * Get type of the saving card
     *
     * @return string
     */
    public function getCardType(): string
    {
        $tokenCardDetails = $this->getTokenDetails();
        $tokenCardIssuer = '';
        /**
         * @var mixed $tokenCardDetails
         */
        if ($tokenCardDetails) {
            $tokenCardIssuer = $tokenCardDetails[Constants::CARD_ISSUER];

            foreach ($this->availableCardTypes as $key => $val) {
                if ($key == strtolower($tokenCardIssuer)) {
                    $this->cardType = $val;
                    return $val;
                }
            }
        }
        return $tokenCardIssuer;
    }

    /**
     * Gets card icon url
     *
     * @return mixed
     */
    public function getIconUrl(): mixed
    {
        return $this->getIconForType($this->cardType)['url'];
    }

    /**
     * Gets card icon height
     *
     * @return int
     */
    public function getIconHeight(): int
    {
        return $this->getIconForType($this->cardType)['height'];
    }

    /**
     * Gets card icon width
     *
     * @return int
     */
    public function getIconWidth(): int
    {
        return $this->getIconForType($this->cardType)['width'];
    }
}
