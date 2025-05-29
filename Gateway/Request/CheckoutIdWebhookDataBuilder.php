<?php

namespace Aci\Payment\Gateway\Request;

use Aci\Payment\Helper\Constants;

/**
 * Builds checkout ID for Get transaction request - webhook
 */
class CheckoutIdWebhookDataBuilder extends TransactionIdDataBuilder
{
    /**
     * Get Transaction ID from payment builder object
     *
     * @param array<mixed> $buildSubject
     * @return string
     */
    public function getRequestData(array $buildSubject): string
    {
        return $buildSubject[Constants::TRANSACTION_ID] ?? '';
    }
}
