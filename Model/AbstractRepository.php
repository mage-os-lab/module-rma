<?php

declare(strict_types=1);

namespace MageOS\RMA\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Exception;

abstract class AbstractRepository
{
    /**
     * @param AbstractDb $resourceModel
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        protected readonly AbstractDb $resourceModel,
        protected readonly CollectionProcessorInterface $collectionProcessor
    ) {
    }

    /**
     * @return string
     */
    abstract protected function getEntityLabel(): string;

    /**
     * @return AbstractModel
     */
    abstract protected function createEntity(): AbstractModel;

    /**
     * @return AbstractCollection
     */
    abstract protected function createCollection(): AbstractCollection;

    /**
     * @return SearchResultsInterface
     */
    abstract protected function createSearchResults(): SearchResultsInterface;

    /**
     * @param int $entityId
     * @return AbstractModel
     * @throws NoSuchEntityException
     */
    protected function loadEntity(int $entityId): AbstractModel
    {
        $entity = $this->createEntity();
        $this->resourceModel->load($entity, $entityId);

        if (!$entity->getEntityId()) {
            throw new NoSuchEntityException(
                __('The %1 with id "%2" does not exist.', $this->getEntityLabel(), $entityId)
            );
        }

        return $entity;
    }

    /**
     * @param AbstractModel $entity
     * @return AbstractModel
     * @throws CouldNotSaveException
     */
    protected function saveEntity(AbstractModel $entity): AbstractModel
    {
        try {
            $this->resourceModel->save($entity);
        } catch (Exception $e) {
            throw new CouldNotSaveException(
                __('Could not save the %1: %2', $this->getEntityLabel(), $e->getMessage()),
                $e
            );
        }

        return $entity;
    }

    /**
     * @param AbstractModel $entity
     * @return bool
     * @throws CouldNotDeleteException
     */
    protected function deleteEntity(AbstractModel $entity): bool
    {
        try {
            $this->resourceModel->delete($entity);
        } catch (Exception $e) {
            throw new CouldNotDeleteException(
                __('Could not delete the %1: %2', $this->getEntityLabel(), $e->getMessage()),
                $e
            );
        }

        return true;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    protected function performGetList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $collection = $this->createCollection();
        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResults = $this->createSearchResults();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }
}
