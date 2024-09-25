<?php
namespace Aci\Payment\Block\Adminhtml\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;

/**
 * To Disable the admin field
 */
class Disabled extends Field
{
    /**
     * Disable the field on the fly
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $element->setDisabled('disabled'); /* @phpstan-ignore-line */
        return $element->getElementHtml();
    }
}
