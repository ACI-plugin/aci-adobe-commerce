<?php
namespace Aci\Payment\Model\Adminhtml\Source;

use TryzensIgnite\Base\Model\Adminhtml\Source\PaymentAction as IgnitePaymentAction;

/**
 * Gets payment action options
 */
class PaymentAction extends IgnitePaymentAction
{
    public const ACTION_AUTHORIZE_LABEL = 'Auth';
    public const ACTION_SALE_LABEL      = 'Sale';

    /**
     * Get the list of options
     *
     * @return array<mixed>
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::ACTION_AUTHORIZE,
                'label' => self::ACTION_AUTHORIZE_LABEL
            ],
            [
                'value' => self::ACTION_SALE,
                'label' => self::ACTION_SALE_LABEL
            ]
        ];
    }
}
