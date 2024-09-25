<?php

namespace Aci\Payment\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Aci\Payment\Helper\Utilities;
use Aci\Payment\Helper\Constants;

/**
 * Class SubscriptionPaymentDataBuilder
 * Builds payment data for subscription api call
 */
class SubscriptionPaymentDataBuilder implements BuilderInterface
{
    /**
     * @var Utilities
     */
    protected Utilities $utilities;

    /**
     * @param Utilities $utilities
     */
    public function __construct(
        Utilities $utilities,
    ) {
        $this->utilities = $utilities;
    }

    /**
     * Builds payment data
     *
     * @param array<mixed> $buildSubject
     * @return array<mixed>
     */
    public function build(array $buildSubject): array
    {
        $registrationId = $buildSubject[Constants::KEY_REGISTRATION_ID];
        if (!$registrationId) {
            $registrationId =  $buildSubject['requestParams']
            [Constants::KEY_NOTIFICATION_PAYLOAD]
            [Constants::KEY_REGISTRATION_ID];
        }
        $grandTotal = $buildSubject['requestParams'][Constants::KEY_ACI_PAYMENT_AMOUNT]
            ?? $buildSubject['requestParams'][Constants::KEY_NOTIFICATION_PAYLOAD]
        [Constants::KEY_ACI_PRESENTATION_AMOUNT]
            ?? null;
        $currencyCode = $buildSubject['requestParams'][Constants::KEY_ACI_PAYMENT_CURRENCY]
            ?? $buildSubject['requestParams'][Constants::KEY_NOTIFICATION_PAYLOAD]
        [Constants::KEY_ACI_PRESENTATION_CURRENCY]
            ?? null;
        $paymentType = $buildSubject['requestParams'][Constants::KEY_ACI_PAYMENT_PAYMENT_TYPE]
            ?? $buildSubject['requestParams']
        [Constants::KEY_NOTIFICATION_PAYLOAD]
        [Constants::KEY_ACI_PAYMENT_PAYMENT_TYPE]
            ?? null;
        return [
            Constants::KEY_ACI_PAYMENT_AMOUNT      =>
                $this->utilities->formatNumber(floatval($grandTotal)),
            Constants::KEY_ACI_PAYMENT_CURRENCY    => $currencyCode,
            Constants::KEY_ACI_PAYMENT_PAYMENT_TYPE => $paymentType,
            Constants::KEY_REGISTRATION_ID => $registrationId,
        ];
    }
}
