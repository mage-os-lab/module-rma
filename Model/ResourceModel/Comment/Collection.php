<?php

declare(strict_types=1);

namespace MageOS\RMA\Model\ResourceModel\Comment;

use MageOS\RMA\Model\Comment as Model;
use MageOS\RMA\Model\ResourceModel\Comment as ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'rma_comment_collection';

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
