<?php

namespace Aci\Payment\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Aci\Payment\Helper\Utilities;
use Aci\Payment\Helper\Constants;

/**
 * Class PaymentDataBuilder
 * Builds payment data
 */
class PaymentDataBuilder implements BuilderInterface
{
    /**
     * @var Utilities
     */
    protected Utilities $utilities;

    /**
     * @param Utilities $utilities
     */
    public function __construct(
        Utilities $utilities
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
        $payment = SubjectReader::readPayment($buildSubject);
        $order = $payment->getOrder();
        $grandTotal = 0;
        if (isset($buildSubject['quote']) && $buildSubject['quote']) {
            $quote = $buildSubject['quote'];
            $grandTotal = $quote->getBaseGrandTotal();
        }
        if ($orderGrandTotal = $order->getGrandTotalAmount()) {
            $grandTotal = $orderGrandTotal;
        }
        return [
            Constants::KEY_ACI_PAYMENT_AMOUNT      =>
                $this->utilities->formatNumber(floatval($grandTotal)),
            Constants::KEY_ACI_PAYMENT_CURRENCY    => $order->getCurrencyCode(),
            Constants::KEY_ACI_PAYMENT_MERCHANT_TRANSACTION_ID => $order->getOrderIncrementId()
        ];
    }
}
