<?php

declare(strict_types=1);

namespace MageOS\RMA\Model\ResourceModel\ItemCondition;

use MageOS\RMA\Model\ItemCondition as Model;
use MageOS\RMA\Model\ResourceModel\ItemCondition as ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @var string
     */
    protected $_eventPrefix = 'rma_item_condition_collection';

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
