<?php
namespace Aci\Payment\Model\ResourceModel\RecurringOrder;

use Aci\Payment\Model\RecurringOrder;
use Aci\Payment\Model\ResourceModel\RecurringOrder as ResourceRecurringOrder;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * CMS page collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Event prefix for recurring order
     *
     * @var string
     */
    protected $_eventPrefix = 'recurring_order_collection';

    /**
     * Event object key for recurring order
     *
     * @var string
     */
    protected $_eventObject = 'recurring_order_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(RecurringOrder::class, ResourceRecurringOrder::class);
    }
}
