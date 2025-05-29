<?php
namespace Aci\Payment\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\BlockInterface;

/**
 * Frequency Dynamic Field
 */
class Frequency extends AbstractFieldArray
{
    /**
     * @var BlockInterface
     */
    protected BlockInterface $subscriptionFrequencyUnit;

    /**
     * @var BlockInterface
     */
    protected BlockInterface $textAreaRenderer;

    /**
     * Render Unit Field
     *
     * @return BlockInterface
     * @throws LocalizedException
     */
    protected function getSubscriptionUnitRenderer(): BlockInterface
    {
        if (empty($this->subscriptionFrequencyUnit)) {
            $this->subscriptionFrequencyUnit = $this->getLayout()->createBlock(
                SubscriptionFrequencyUnit::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            /** @phpstan-ignore-next-line */
            $this->subscriptionFrequencyUnit->setClass('required-entry validate-select');
        }
        return $this->subscriptionFrequencyUnit;
    }

    /**
     * Render Description Field
     *
     * @return BlockInterface
     * @throws LocalizedException
     */
    protected function getTextAreaRenderer(): BlockInterface
    {
        if (empty($this->textAreaRenderer)) {
            $this->textAreaRenderer = $this->getLayout()->createBlock(
                TextAreaRenderer::class
            );
        }
        return $this->textAreaRenderer;
    }

    /**
     * Prepare rendering the new field by adding all the needed columns
     *
     * @return void
     * @throws LocalizedException
     */
    protected function _prepareToRender(): void
    {
        $this->addColumn('display_name', ['label' => __('Display Name'), 'class' => 'required-entry']);
        $this->addColumn('description', [
            'label' => __('Description'),
            'renderer' => $this->getTextAreaRenderer()
        ]);
        $this->addColumn('unit', [
            'label' => __('Unit'),
            'renderer' => $this->getSubscriptionUnitRenderer()
        ]);
        $this->addColumn('unit_value', [
            'label' => __('Value of Unit'),
            'class' => 'required-entry validate-digits validate-not-negative-number
                            validate-digits-range digits-range-1-999'
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @return void
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $unit = $row->getUnit(); //@phpstan-ignore-line
        $options = [];
        if ($unit) {
            /** @phpstan-ignore-next-line */
            $options['option_' . $this->getSubscriptionUnitRenderer()->calcOptionHash($unit)]
                = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }
}
