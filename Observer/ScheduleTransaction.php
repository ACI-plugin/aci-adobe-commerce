<?php

namespace Aci\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Aci\Payment\Model\WebhookEvents;

/**
 * Observer for processing recurring order schedules from webhook notifications
 */
class ScheduleTransaction implements ObserverInterface
{
    /**
     * @var WebhookEvents
     */
    protected WebhookEvents $webhookEvents;

    /**
     * @param WebhookEvents $webhookEvents
     */
    public function __construct(
        WebhookEvents $webhookEvents
    ) {
        $this->webhookEvents = $webhookEvents;
    }

    /**
     * Execute webhook transaction
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer): void
    {
        $params = $observer->getData('scheduleParams');
        $this->webhookEvents->processEvents($params);
    }
}
