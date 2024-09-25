<?php
namespace Aci\Payment\Model\Adminhtml\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Generate environment field options
 */
class Environment implements OptionSourceInterface
{
    /**
     * Environment option constants.
     */
    public const LABEL_ENVIRONMENT_TEST      =   'Test';
    public const VALUE_ENVIRONMENT_TEST      =   'test_mode';

    public const LABEL_ENVIRONMENT_LIVE   =   'Live';
    public const VALUE_ENVIRONMENT_LIVE   =   'live_mode';

    /**
     * Possible environment types
     *
     * @return array <mixed>
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::VALUE_ENVIRONMENT_TEST,
                'label' => self::LABEL_ENVIRONMENT_TEST
            ],
            [
                'value' => self::VALUE_ENVIRONMENT_LIVE,
                'label' => self::LABEL_ENVIRONMENT_LIVE
            ]
        ];
    }
}
