<?php

declare(strict_types=1);

namespace MageOS\RMA\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Item extends AbstractDb
{
    /**
     * @var string
     */
    protected string $_eventPrefix = 'rma_item_resource_model';

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('rma_item', 'entity_id');
        $this->_useIsObjectNew = true;
    }
}
