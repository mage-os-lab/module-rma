<?php

declare(strict_types=1);

namespace MageOS\RMA\Model\ResourceModel\ResolutionType;

use MageOS\RMA\Model\ResolutionType as Model;
use MageOS\RMA\Model\ResourceModel\ResolutionType as ResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'rma_resolution_type_collection';

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
