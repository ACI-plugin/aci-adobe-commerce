<?php

namespace Aci\Payment\Gateway\Request\BackOffice\Refund;

use Aci\Payment\Model\Utilities\DataFormatter;
use Magento\Sales\Model\Order\Creditmemo;
use Aci\Payment\Helper\Constants;
use TryzensIgnite\Base\Gateway\Request\BackOffice\Refund\RefundDataBuilder as IgniteRefundDataBuilder;

/**
 * Builds capture request data
 */
class RefundDataBuilder extends IgniteRefundDataBuilder
{
    /**
     * @var DataFormatter
     */
    private DataFormatter $dataFormatter;

    /**
     * @param DataFormatter $dataFormatter
     */
    public function __construct(
        DataFormatter $dataFormatter
    ) {
        $this->dataFormatter = $dataFormatter;
        parent::__construct($dataFormatter);
    }

    /**
     * Override the request key properties
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->requestKeys['refunded_amount'] = Constants::KEY_ACI_PAYMENT_AMOUNT;
    }

    /**
     * Get Refunded Request Data
     *
     * @param Creditmemo $creditMemo
     * @return array<mixed>
     */
    public function getRequestData(Creditmemo $creditMemo): array
    {
        $requestData = parent::getRequestData($creditMemo);
        if (isset($requestData[$this->requestKeys['refunded_amount']])
            && $requestData[$this->requestKeys['refunded_amount']]) {
            $requestData[$this->requestKeys['refunded_amount']] = $this->dataFormatter->formatNumber(
                $requestData[$this->requestKeys['refunded_amount']],
                2
            );
        }
        $requestData[Constants::KEY_ACI_PAYMENT_CURRENCY] = $creditMemo->getOrderCurrencyCode();
        return $requestData;
    }
}
