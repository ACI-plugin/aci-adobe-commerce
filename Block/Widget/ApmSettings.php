<?php
namespace Aci\Payment\Block\Widget;

use Magento\Backend\Block\Widget;

/**
 * Widget for APM settings
 */
class ApmSettings extends Widget
{
    /**
     * Define block template
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->setTemplate('Aci_Payment::widget/apm-settings.phtml');
        parent::_construct();
    }
}
