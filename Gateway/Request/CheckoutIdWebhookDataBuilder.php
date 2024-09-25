<?php

namespace Aci\Payment\Gateway\Request;

use TryzensIgnite\Common\Helper\Constants;
use TryzensIgnite\Common\Gateway\Request\TransactionIDWebhookDataBuilder;

/**
 * Builds checkout ID for Get transaction request - webhook
 */
class CheckoutIdWebhookDataBuilder extends TransactionIDWebhookDataBuilder
{
    /**
     * Get Transaction ID from payment builder object
     *
     * @param array<mixed> $buildSubject
     * @return string
     */
    public function getTransactionId(array $buildSubject): string
    {
        return $buildSubject[Constants::TRANSACTION_ID] ?? '';
    }
}
