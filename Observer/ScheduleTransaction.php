<?php

namespace Aci\Payment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Aci\Payment\Model\WebhookEvents;
use TryzensIgnite\Common\Api\QuoteManagerInterface;

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
     * @var mixed|QuoteManagerInterface
     */
    private mixed $quoteManager;

    /**
     * @param WebhookEvents $webhookEvents
     * @param QuoteManagerInterface $quoteManager
     */
    public function __construct(
        WebhookEvents $webhookEvents,
        QuoteManagerInterface $quoteManager
    ) {
        $this->webhookEvents = $webhookEvents;
        $this->quoteManager             =   $quoteManager;
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
        $this->quoteManager->disableQuote();
    }
}
