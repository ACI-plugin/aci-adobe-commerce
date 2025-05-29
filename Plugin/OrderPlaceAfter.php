<?php
namespace Aci\Payment\Plugin;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Model\Order;
use Aci\Payment\Api\RecurringOrderRepositoryInterface;
use Aci\Payment\Model\ManageSubscriptionFrequency;
use Aci\Payment\Model\RecurringOrder;
use Aci\Payment\Model\RecurringOrderFactory;
use Aci\Payment\Api\Data\RecurringOrderInterface;

class OrderPlaceAfter
{
    /**
     * @var CustomerSession
     */
    protected CustomerSession $customerSession;

    /**
     * @var ManageSubscriptionFrequency
     */
    private ManageSubscriptionFrequency $frequencySession;

    /**
     * @var RecurringOrderRepositoryInterface
     */
    private RecurringOrderRepositoryInterface $recurringOrderRepository;

    /**
     * @var RecurringOrderFactory
     */
    private RecurringOrderFactory $recurringOrderFactory;

    /**
     * @param CustomerSession $customerSession
     * @param ManageSubscriptionFrequency $frequencySession
     * @param RecurringOrderRepositoryInterface $recurringOrderRepository
     * @param RecurringOrderFactory $recurringOrderFactory
     */
    public function __construct(
        CustomerSession                   $customerSession,
        ManageSubscriptionFrequency       $frequencySession,
        RecurringOrderRepositoryInterface $recurringOrderRepository,
        RecurringOrderFactory             $recurringOrderFactory,
    ) {
        $this->customerSession = $customerSession;
        $this->frequencySession = $frequencySession;
        $this->recurringOrderRepository = $recurringOrderRepository;
        $this->recurringOrderFactory = $recurringOrderFactory;
    }

    /**
     * After place plugin for Order
     *
     * @param OrderManagementInterface $subject
     * @param Order $order
     * @return Order
     * @throws LocalizedException
     */
    public function afterPlace(OrderManagementInterface $subject, Order $order): Order
    {
        // Get the order ID
        $orderId = $order->getId();
        $incrementId = $order->getIncrementId();
        if ($this->customerSession->isLoggedIn() && $this->frequencySession->getSubscriptionFrequencyFromSession()) {
            $customerId = $this->customerSession->getCustomerId();
            $status = RecurringOrder::SUB_STATUS_PENDING;
            $recurringOrder = $this->recurringOrderFactory->create();
            $recurringOrder->addData([
                RecurringOrderInterface::CUSTOMER_ID => $customerId,
                RecurringOrderInterface::ORDER_ID => $orderId,
                RecurringOrderInterface::INCREMENT_ID => $incrementId,
                RecurringOrderInterface::SUBSCRIPTION_ID => null,
                RecurringOrderInterface::STATUS => $status,
                RecurringOrderInterface::REGISTRATION_ID => null,
                RecurringOrderInterface::JOB_EXPRESSION  =>
                    $this->frequencySession->getSubscriptionJobExpressionFromSession() ,
                RecurringOrderInterface::RECURRING_UNIT =>
                    $this->frequencySession->getSubscriptionUnitFromSession(),
                RecurringOrderInterface::RECURRING_FREQUENCY =>
                    $this->frequencySession->getSubscriptionFrequencyFromSession()
            ]);
            $this->recurringOrderRepository->save($recurringOrder);
        }

        return $order;
    }
}
