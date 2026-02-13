<?php

declare(strict_types=1);

namespace MageOS\RMA\Model\ResourceModel\Item;

use MageOS\RMA\Model\Item as Model;
use MageOS\RMA\Model\ResourceModel\Item as ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'rma_item_collection';

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
