<?php

declare(strict_types=1);

namespace MageOS\RMA\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Comment extends AbstractDb
{
    /**
     * @var string
     */
    protected string $_eventPrefix = 'rma_comment_resource_model';

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('rma_comment', 'entity_id');
        $this->_useIsObjectNew = true;
    }
}
