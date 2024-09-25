<?php
namespace Aci\Payment\Block\Widget;

use Magento\Backend\Block\Widget;

/**
 * Widget for uploading multiple logos
 */
class CardTypeIcons extends Widget
{
    /**
     * Define block template
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->setTemplate('Aci_Payment::widget/card-type-icons.phtml');
        parent::_construct();
    }
}
