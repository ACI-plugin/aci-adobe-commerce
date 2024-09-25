<?php
namespace Aci\Payment\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Generate test mode field options
 */
class TestMode implements OptionSourceInterface
{
    /**
     * Environment option constants.
     */
    public const LABEL_TESTMODE_INTERNAL      =   'INTERNAL';
    public const VALUE_TESTMODE_INTERNAL      =   'INTERNAL';

    public const LABEL_TESTMODE_EXTERNAL   =   'EXTERNAL';
    public const VALUE_TESTMODE_EXTERNAL   =   'EXTERNAL';

    /**
     * Possible TestMode Values
     *
     * @return array <mixed>
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::VALUE_TESTMODE_INTERNAL,
                'label' => self::LABEL_TESTMODE_INTERNAL
            ],
            [
                'value' => self::VALUE_TESTMODE_EXTERNAL,
                'label' => self::LABEL_TESTMODE_EXTERNAL
            ]
        ];
    }
}
