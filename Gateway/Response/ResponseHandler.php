<?php

namespace Aci\Payment\Gateway\Response;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use TryzensIgnite\Common\Api\QuoteManagerInterface;
use Aci\Payment\Helper\Constants;

/**
 * Class ResponseHandler
 * Payment Response Handler
 */
class ResponseHandler implements HandlerInterface
{

    /**
     * @var QuoteManagerInterface
     */
    protected QuoteManagerInterface $quoteManager;

    /**
     * ResponseHandler constructor.
     * @param QuoteManagerInterface $quoteManager
     */
    public function __construct(
        QuoteManagerInterface $quoteManager
    ) {
        $this->quoteManager = $quoteManager;
    }

    /**
     * Handles payment transaction response
     *
     * @param array<mixed> $handlingSubject
     * @param array<mixed> $response
     * @return void
     * @throws LocalizedException
     */
    public function handle(array $handlingSubject, array $response): void
    {
        $paymentDataObject = SubjectReader::readPayment($handlingSubject);
        $payment = $paymentDataObject->getPayment();
        // set transaction not to processing by default wait for notification
        /** @phpstan-ignore-next-line */
        $payment->setIsTransactionPending(true);
        if ($response && isset($response[Constants::KEY_CHECKOUT_ID])) {
            $this->quoteManager->setPaymentAdditionalInformation($response[Constants::KEY_CHECKOUT_ID]);
        }
        /** @phpstan-ignore-next-line */
        $payment->setIsTransactionClosed(false);
        /** @phpstan-ignore-next-line */
        $payment->setShouldCloseParentTransaction(false);
    }
}
