<?php

namespace Aci\Payment\Gateway\Request\BackOffice\Capture;

use Magento\Sales\Model\Order;
use Aci\Payment\Helper\Constants;
use TryzensIgnite\Base\Gateway\Request\BackOffice\Capture\CaptureDataBuilder as IgniteCaptureDataBuilder;
use Aci\Payment\Model\Utilities\DataFormatter;

/**
 * Builds capture request data
 */
class CaptureDataBuilder extends IgniteCaptureDataBuilder
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
        $this->requestKeys['captured_amount'] = Constants::KEY_ACI_PAYMENT_AMOUNT;
    }

    /**
     * Get Captured Request Data
     *
     * @param Order $order
     * @return array<mixed>
     */
    public function getRequestData(Order $order): array
    {
        $captureRequest = parent::getRequestData($order);
        if (isset($captureRequest[$this->requestKeys['captured_amount']])
            && $captureRequest[$this->requestKeys['captured_amount']]) {
            $captureRequest[$this->requestKeys['captured_amount']] = $this->dataFormatter->formatNumber(
                $captureRequest[$this->requestKeys['captured_amount']],
                2
            );
        }
        $captureRequest[Constants::KEY_ACI_PAYMENT_CURRENCY] = $order->getOrderCurrencyCode();
        return $captureRequest;
    }
}
