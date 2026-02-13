<?php

declare(strict_types=1);

namespace MageOS\RMA\Model;

use MageOS\RMA\Api\Data\StatusInterface;
use MageOS\RMA\Api\Data\StatusSearchResultsInterface;
use MageOS\RMA\Api\Data\StatusSearchResultsInterfaceFactory;
use MageOS\RMA\Api\StatusRepositoryInterface;
use MageOS\RMA\Model\ResourceModel\Status as ResourceModel;
use MageOS\RMA\Model\ResourceModel\Status\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class StatusRepository extends AbstractRepository implements StatusRepositoryInterface
{
    /**
     * @param ResourceModel $resourceModel
     * @param StatusFactory $statusFactory
     * @param CollectionFactory $collectionFactory
     * @param StatusSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceModel $resourceModel,
        protected readonly StatusFactory $statusFactory,
        protected readonly CollectionFactory $collectionFactory,
        protected readonly StatusSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        parent::__construct($resourceModel, $collectionProcessor);
    }

    /**
     * @return string
     */
    protected function getEntityLabel(): string
    {
        return 'RMA Status';
    }

    /**
     * @return AbstractModel
     */
    protected function createEntity(): AbstractModel
    {
        return $this->statusFactory->create();
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
     * @return StatusInterface
     * @throws NoSuchEntityException
     */
    public function get(int $entityId): StatusInterface
    {
        return $this->loadEntity($entityId);
    }

    /**
     * @param StatusInterface $status
     * @return StatusInterface
     * @throws CouldNotSaveException
     */
    public function save(StatusInterface $status): StatusInterface
    {
        return $this->saveEntity($status);
    }

    /**
     * @param StatusInterface $status
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(StatusInterface $status): bool
    {
        return $this->deleteEntity($status);
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
     * @return StatusSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): StatusSearchResultsInterface
    {
        return $this->performGetList($searchCriteria);
    }
}
