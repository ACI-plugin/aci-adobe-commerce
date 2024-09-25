<?php

namespace Aci\Payment\Block\Adminhtml\System\Config\Form;

use Magento\Framework\Data\Form\Element\AbstractElement;
use TryzensIgnite\Common\Block\Adminhtml\System\Config\Form\DownloadLogs as IgniteDownloadLogs;

/**
 * Frontend model for log download field for Aci payment.
 */
class DownloadLogs extends IgniteDownloadLogs
{
    /**
     * Get download button
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        return sprintf(
            '<a href="%s"  style="display: block;padding-top: 12px;">%s</a>',
            rtrim($this->_urlBuilder->getUrl('acipayment/downloadlogs/downloadlogs')),
            __('Download Logs')
        );
    }
}
