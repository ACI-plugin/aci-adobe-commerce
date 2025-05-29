<?php

namespace Aci\Payment\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Aci\Payment\Helper\Constants;
use TryzensIgnite\Base\Gateway\Request\TransactionIdDataBuilder as IgniteTransactionIdDataBuilder;

/**
 * Builds magento transaction request
 */
class TransactionIdDataBuilder extends IgniteTransactionIdDataBuilder
{
    /**
     * Override the request key properties
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->requestKeys['transactionId'] = 'TransactionId';
    }

    /**
     * Get Transaction ID for AUTH service
     *
     * @param array<mixed> $request
     * @return string
     */
    public function getRequestData(array $request): string
    {
        $payment = SubjectReader::readPayment($request);
        return $request[Constants::TRANSACTION_ID] ??
            $payment->getPayment()->getAdditionalInformation(Constants::TRANSACTION_ID);
    }
}
