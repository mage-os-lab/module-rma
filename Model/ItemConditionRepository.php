<?php

declare(strict_types=1);

namespace MageOS\RMA\Model;

use MageOS\RMA\Api\Data\ItemConditionInterface;
use MageOS\RMA\Api\Data\ItemConditionSearchResultsInterface;
use MageOS\RMA\Api\Data\ItemConditionSearchResultsInterfaceFactory;
use MageOS\RMA\Api\ItemConditionRepositoryInterface;
use MageOS\RMA\Model\ResourceModel\ItemCondition as ResourceModel;
use MageOS\RMA\Model\ResourceModel\ItemCondition\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class ItemConditionRepository extends AbstractRepository implements ItemConditionRepositoryInterface
{
    /**
     * @param ResourceModel $resourceModel
     * @param ItemConditionFactory $itemConditionFactory
     * @param CollectionFactory $collectionFactory
     * @param ItemConditionSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceModel $resourceModel,
        protected readonly ItemConditionFactory $itemConditionFactory,
        protected readonly CollectionFactory $collectionFactory,
        protected readonly ItemConditionSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        parent::__construct($resourceModel, $collectionProcessor);
    }

    /**
     * @return string
     */
    protected function getEntityLabel(): string
    {
        return 'RMA Item Condition';
    }

    /**
     * @return AbstractModel
     */
    protected function createEntity(): AbstractModel
    {
        return $this->itemConditionFactory->create();
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
     * @return ItemConditionInterface
     * @throws NoSuchEntityException
     */
    public function get(int $entityId): ItemConditionInterface
    {
        return $this->loadEntity($entityId);
    }

    /**
     * @param ItemConditionInterface $itemCondition
     * @return ItemConditionInterface
     * @throws CouldNotSaveException
     */
    public function save(ItemConditionInterface $itemCondition): ItemConditionInterface
    {
        return $this->saveEntity($itemCondition);
    }

    /**
     * @param ItemConditionInterface $itemCondition
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ItemConditionInterface $itemCondition): bool
    {
        return $this->deleteEntity($itemCondition);
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
     * @return ItemConditionSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): ItemConditionSearchResultsInterface
    {
        return $this->performGetList($searchCriteria);
    }
}
