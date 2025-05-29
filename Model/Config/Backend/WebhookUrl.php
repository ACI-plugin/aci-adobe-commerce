<?php
namespace Aci\Payment\Model\Config\Backend;

use TryzensIgnite\Base\Model\Config\Backend\WebhookUrl as BaseWebhookUrl;

/**
 * Class WebhookUrl - Returns value to pre-populate webhook configuration field with URL
 */
class WebhookUrl extends BaseWebhookUrl
{
    /**
     * @var array|string[]
     */
    protected array $requestValues = [
        'webhook_url_controller_path' => 'acipayment/process/notification'
    ];
}
