<?php

namespace MageOS\RMA\Model\ResourceModel\RMA;

use MageOS\RMA\Model\RMA as Model;
use MageOS\RMA\Model\ResourceModel\RMA as ResourceModel;
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
    protected $_eventPrefix = 'rma_entity_collection';

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
