<?php

namespace Aci\Payment\Gateway\Request;

use Aci\Payment\Helper\Utilities;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Aci\Payment\Helper\Constants;
use Magento\Sales\Model\Order\Payment;

/**
 * Class TransactionIDDataBuilder
 * Builds magento transaction request
 */
class CheckoutIdDataBuilder implements BuilderInterface
{

    /**
     * @var Utilities
     */
    private Utilities $utilities;

    /**
     * @param Utilities $utilities
     */
    public function __construct(
        Utilities $utilities
    ) {
        $this->utilities = $utilities;
    }

    /**
     * Builds transaction id
     *
     * @param array<mixed> $buildSubject
     * @return array<mixed>
     * @throws LocalizedException
     */
    public function build(array $buildSubject): array
    {
        if (isset($buildSubject[Constants::KEY_CHECKOUT_ID])) {
            $checkoutId = $buildSubject[Constants::KEY_CHECKOUT_ID];
        } else {
            $paymentDataObject = SubjectReader::readPayment($buildSubject);
            /** @var Payment $payment */
            $payment = $paymentDataObject->getPayment();
            $paymentAdditionalInfo = $payment->getAdditionalInformation();
            if (isset($paymentAdditionalInfo[Constants::GET_TRANSACTION_RESPONSE][Constants::KEY_CHECKOUT_ID])) {
                $checkoutId =
                    (string)$paymentAdditionalInfo[Constants::GET_TRANSACTION_RESPONSE][Constants::KEY_CHECKOUT_ID];
            } elseif (isset($paymentAdditionalInfo[Constants::GET_TRANSACTION_RESPONSE]
                [Constants::CHECKOUT_TRANSACTION_ID])) {
                $checkoutId =
                    (string)$paymentAdditionalInfo[Constants::GET_TRANSACTION_RESPONSE]
                    [Constants::CHECKOUT_TRANSACTION_ID];
            } else {
                throw new LocalizedException(
                    __('Invalid payment response :' . $this->utilities->serialize($paymentAdditionalInfo))
                );
            }
        }
        return [
            Constants::KEY_CHECKOUT_ID => $checkoutId
        ];
    }
}
