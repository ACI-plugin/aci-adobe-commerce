<?php

namespace Aci\Payment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Payment;
use Aci\Payment\Helper\Utilities;
use Aci\Payment\Helper\Constants;
use Magento\Sales\Api\InvoiceRepositoryInterface;

/**
 * Builds capture request data
 */
class CaptureDataBuilder implements BuilderInterface
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
     * Builds capture request data
     *
     * @param array<mixed> $buildSubject
     * @return array<mixed>
     */
    public function build(array $buildSubject): array
    {
        $paymentDataObject = SubjectReader::readPayment($buildSubject);
        /** @var Payment $payment */
        $payment = $paymentDataObject->getPayment();
        $order = $payment->getOrder();
        /** @var Invoice $latestInvoice */
        $latestInvoice = $order->getInvoiceCollection()->getLastItem();

        return [
            Constants::KEY_ACI_PAYMENT_AMOUNT      =>
                $this->utilities->formatNumber(floatval($latestInvoice->getBaseGrandTotal())),
            Constants::KEY_ACI_PAYMENT_CURRENCY    => $order->getOrderCurrencyCode(),
        ];
    }
}
