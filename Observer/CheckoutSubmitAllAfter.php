<?php

namespace Aci\Payment\Observer;

use TryzensIgnite\Common\Api\OrderManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order;
use TryzensIgnite\Common\Api\QuoteManagerInterface;
use Aci\Payment\Model\Ui\AciCcConfigProvider;
use Aci\Payment\Model\Ui\AciApmConfigProvider;

/**
 * Update order status after place order
 */
class CheckoutSubmitAllAfter implements ObserverInterface
{
    /**
     * @var OrderManagerInterface
     */
    private OrderManagerInterface $orderManager;

    /**
     * @var QuoteManagerInterface
     */
    private QuoteManagerInterface $quoteManager;

    /**
     * @var array<mixed>
     */
    protected array $aciPaymentMethods = [
        AciCcConfigProvider::CODE,
        AciApmConfigProvider::CODE
    ];

    /**
     * CheckoutSubmitAllAfter constructor.
     * @param OrderManagerInterface $orderManager
     * @param QuoteManagerInterface $quoteManager
     */
    public function __construct(
        OrderManagerInterface $orderManager,
        QuoteManagerInterface $quoteManager,
    ) {
        $this->orderManager = $orderManager;
        $this->quoteManager = $quoteManager;
    }

    /**
     * Update order status after placing order
     *
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer): void
    {
        try {
            /** @var Order $order */
            $order = $observer->getEvent()->getData('order');
            $paymentMethod = $order->getPayment() ? $order->getPayment()->getMethod() : null;
            if (!in_array($paymentMethod, $this->aciPaymentMethods)) {
                return;
            }
            $this->orderManager->updateOrderStatus(
                __('Order has been placed by Magento.'),
                'pending_payment',
                'pending_payment',
                (int)$order->getEntityId()
            );
            $this->quoteManager->unsetReserveOrderId();
        } catch (\Exception $e) {
            throw new LocalizedException(__($e->getMessage()));
        }
    }
}
