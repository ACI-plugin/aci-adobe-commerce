<?php
declare(strict_types=1);

namespace Aci\Payment\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;
use Aci\Payment\Model\SubscriptionConstants as Constants;

/**
 * Subscription - Unit field
 * @method setName(string $value)
 */
class SubscriptionFrequencyUnit extends Select
{
    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return SubscriptionFrequencyUnit
     */
    public function setInputName(string $value): SubscriptionFrequencyUnit
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param string $value
     * @return SubscriptionFrequencyUnit
     */
    public function setInputId(string $value): SubscriptionFrequencyUnit
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    /**
     * Options for Unit Field
     *
     * @return array<mixed>
     */
    private function getSourceOptions(): array
    {
        return [
            ['label' => Constants::SUB_FREQ_LBL_CHOOSE, 'value' => Constants::SUB_FREQ_VAL_CHOOSE],
            ['label' => Constants::SUB_FREQ_LBL_DAY, 'value' => Constants::SUB_FREQ_VAL_DAY],
            ['label' => Constants::SUB_FREQ_LBL_WEEK, 'value' => Constants::SUB_FREQ_VAL_WEEK],
            ['label' => Constants::SUB_FREQ_LBL_MONTH, 'value' => Constants::SUB_FREQ_VAL_MONTH],
            ['label' => Constants::SUB_FREQ_LBL_YEAR, 'value' => Constants::SUB_FREQ_VAL_YEAR]
        ];
    }
}
