<?php
namespace Aci\Payment\Gateway\Response;

use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment;
use Aci\Payment\Helper\Constants;

/**
 * Payment REST API Transaction Response Handler
 */
class BackofficeResponseHandler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    private SubjectReader $subjectReader;

    /**
     * @var string
     */
    private string $transactionType;

    /**
     * @param SubjectReader $subjectReader
     * @param string $transactionType
     */
    public function __construct(
        SubjectReader $subjectReader,
        string $transactionType = ''
    ) {
        $this->subjectReader = $subjectReader;
        $this->transactionType = $transactionType;
    }
    /**
     * Handles response
     *
     * @param array<mixed> $handlingSubject
     * @param array<mixed> $response
     * @return void
     * @throws LocalizedException
     */
    public function handle(array $handlingSubject, array $response): void
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        /** @var Payment $payment */
        $payment = $paymentDO->getPayment();
        $referenceId = '';
        if ($response && isset($response[Constants::KEY_CHECKOUT_ID])) {
            $paymentType = $response[Constants::KEY_ACI_PAYMENT_PAYMENT_TYPE] ?? null;
            if ($paymentType == Constants::PAYMENT_TYPE_CAPTURE ||
                $paymentType == Constants::PAYMENT_TYPE_REFUND) {
                $referenceId = $response[Constants::KEY_PAYMENT_REFERENCE_ID];
            }
        }
        if ($referenceId) {
            $key = match ($this->transactionType) {
                Constants::SERVICE_CAPTURE => Constants::KEY_CAPTURE_REF_ID,
                Constants::SERVICE_REFUND => Constants::KEY_REFUND_REF_ID,
                default => null,
            };
            if ($key) {
                $payment->setAdditionalInformation(
                    $key,
                    $referenceId
                );
            }
        }
    }
}
