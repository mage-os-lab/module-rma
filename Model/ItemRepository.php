<?php

declare(strict_types=1);

namespace MageOS\RMA\Model;

use MageOS\RMA\Api\Data\ItemInterface;
use MageOS\RMA\Api\Data\ItemSearchResultsInterface;
use MageOS\RMA\Api\Data\ItemSearchResultsInterfaceFactory;
use MageOS\RMA\Api\ItemRepositoryInterface;
use MageOS\RMA\Model\ResourceModel\Item as ResourceModel;
use MageOS\RMA\Model\ResourceModel\Item\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class ItemRepository extends AbstractRepository implements ItemRepositoryInterface
{
    /**
     * @param ResourceModel $resourceModel
     * @param ItemFactory $itemFactory
     * @param CollectionFactory $collectionFactory
     * @param ItemSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceModel $resourceModel,
        protected readonly ItemFactory $itemFactory,
        protected readonly CollectionFactory $collectionFactory,
        protected readonly ItemSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        parent::__construct($resourceModel, $collectionProcessor);
    }

    /**
     * @return string
     */
    protected function getEntityLabel(): string
    {
        return 'RMA Item';
    }

    /**
     * @return AbstractModel
     */
    protected function createEntity(): AbstractModel
    {
        return $this->itemFactory->create();
    }

    /**
     * @return AbstractCollection
     */
    protected function createCollection(): AbstractCollection
    {
        return $this->collectionFactory->create();
    }

    /**
     * @return SearchResultsInterface
     */
    protected function createSearchResults(): SearchResultsInterface
    {
        return $this->searchResultsFactory->create();
    }

    /**
     * @param int $entityId
     * @return ItemInterface
     * @throws NoSuchEntityException
     */
    public function get(int $entityId): ItemInterface
    {
        return $this->loadEntity($entityId);
    }

    /**
     * @param ItemInterface $item
     * @return ItemInterface
     * @throws CouldNotSaveException
     */
    public function save(ItemInterface $item): ItemInterface
    {
        return $this->saveEntity($item);
    }

    /**
     * @param ItemInterface $item
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ItemInterface $item): bool
    {
        return $this->deleteEntity($item);
    }

    /**
     * @param int $entityId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $entityId): bool
    {
        return $this->delete($this->get($entityId));
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return ItemSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): ItemSearchResultsInterface
    {
        return $this->performGetList($searchCriteria);
    }
}
