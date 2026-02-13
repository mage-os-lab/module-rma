<?php

declare(strict_types=1);

namespace MageOS\RMA\Model\Config\Source;

use MageOS\RMA\Model\ResourceModel\Reason\CollectionFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\StoreManagerInterface;

class Reason extends AbstractLookupSource
{
    /**
     * @param CollectionFactory $collectionFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        protected readonly CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($storeManager);
    }

    /**
     * @return AbstractCollection
     */
    protected function createCollection(): AbstractCollection
    {
        return $this->collectionFactory->create();
    }

    /**
     * @return string
     */
    protected function getLabelTable(): string
    {
        return 'rma_reason_label';
    }

    /**
     * @return string
     */
    protected function getLabelForeignKey(): string
    {
        return 'reason_id';
    }
}
