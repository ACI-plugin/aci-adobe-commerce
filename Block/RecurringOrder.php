<?php
namespace Aci\Payment\Block;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session;
use Aci\Payment\Model\ResourceModel\RecurringOrder\CollectionFactory;
use Magento\Framework\View\Element\Template;
use Magento\Theme\Block\Html\Pager;
use Aci\Payment\Model\ResourceModel\RecurringOrder\Collection;
use Aci\Payment\Model\RecurringOrder as ModelRecurringOrder;

/**
 * Recurring Order Block
 */
class RecurringOrder extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Aci_Payment::recurring_order.phtml';

    /**
     * @var bool|Collection
     */
    protected Collection|bool $recurringOrders;

    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $collectionFactory;

    /**
     * @var Session
     */
    protected Session $customerSession;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param CollectionFactory $collectionFactory
     * @param array<mixed> $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Recurring Orders'));
    }

    /**
     * Get customer recurring orders
     *
     * @return bool|Collection
     */
    public function getRecurringOrders(): bool|Collection
    {
        if (!($customerId = $this->customerSession->getCustomerId())) {
            return false;
        }
        if (empty($this->recurringOrders)) {
            $this->recurringOrders = $this->collectionFactory->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('status', (string)ModelRecurringOrder::SUB_STATUS_ACTIVE)
                ->addFieldToFilter('customer_id', (string)$customerId)
                ->setOrder('entity_id', 'desc');
        }
        return $this->recurringOrders;
    }

    /**
     * Prepare Layout for pagination
     *
     * @return RecurringOrder|static
     * @throws LocalizedException
     */
    protected function _prepareLayout(): RecurringOrder|static
    {
        parent::_prepareLayout();
        $recurringOrderCollection = $this->getRecurringOrders();
        if ($recurringOrderCollection) {
            /* @phpstan-ignore-next-line */
            $pager = $this->getLayout()->createBlock(
                Pager::class,
                'sales.recurring.order.pager'
            )->setCollection(
                $recurringOrderCollection
            );
            $this->setChild('pager', $pager);
            if ($recurringOrderCollection instanceof Collection) {
                $recurringOrderCollection->load();
            }
        }
        return $this;
    }

    /**
     * Get Pager child block output
     *
     * @return string
     */
    public function getPagerHtml(): string
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Prepare view order link
     *
     * @param int $orderId
     * @return string
     */
    public function getViewUrl(int $orderId): string
    {
        return $this->getUrl('sales/order/view', ['order_id' => $orderId]);
    }
}
