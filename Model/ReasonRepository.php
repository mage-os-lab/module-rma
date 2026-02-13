<?php

declare(strict_types=1);

namespace MageOS\RMA\Model;

use MageOS\RMA\Api\Data\ReasonInterface;
use MageOS\RMA\Api\Data\ReasonSearchResultsInterface;
use MageOS\RMA\Api\Data\ReasonSearchResultsInterfaceFactory;
use MageOS\RMA\Api\ReasonRepositoryInterface;
use MageOS\RMA\Model\ResourceModel\Reason as ResourceModel;
use MageOS\RMA\Model\ResourceModel\Reason\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class ReasonRepository extends AbstractRepository implements ReasonRepositoryInterface
{
    /**
     * @param ResourceModel $resourceModel
     * @param ReasonFactory $reasonFactory
     * @param CollectionFactory $collectionFactory
     * @param ReasonSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceModel $resourceModel,
        protected readonly ReasonFactory $reasonFactory,
        protected readonly CollectionFactory $collectionFactory,
        protected readonly ReasonSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        parent::__construct($resourceModel, $collectionProcessor);
    }

    /**
     * @return string
     */
    protected function getEntityLabel(): string
    {
        return 'RMA Reason';
    }

    /**
     * @return AbstractModel
     */
    protected function createEntity(): AbstractModel
    {
        return $this->reasonFactory->create();
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
     * @return ReasonInterface
     * @throws NoSuchEntityException
     */
    public function get(int $entityId): ReasonInterface
    {
        return $this->loadEntity($entityId);
    }

    /**
     * @param ReasonInterface $reason
     * @return ReasonInterface
     * @throws CouldNotSaveException
     */
    public function save(ReasonInterface $reason): ReasonInterface
    {
        return $this->saveEntity($reason);
    }

    /**
     * @param ReasonInterface $reason
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ReasonInterface $reason): bool
    {
        return $this->deleteEntity($reason);
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
     * @return ReasonSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): ReasonSearchResultsInterface
    {
        return $this->performGetList($searchCriteria);
    }
}
