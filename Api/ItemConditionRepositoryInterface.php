<?php

declare(strict_types=1);

namespace MageOS\RMA\Api;

use MageOS\RMA\Api\Data\ItemConditionInterface;
use MageOS\RMA\Api\Data\ItemConditionSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface ItemConditionRepositoryInterface
{
    /**
     * @param int $entityId
     * @return ItemConditionInterface
     * @throws NoSuchEntityException
     */
    public function get(int $entityId): ItemConditionInterface;

    /**
     * @param ItemConditionInterface $itemCondition
     * @return ItemConditionInterface
     * @throws CouldNotSaveException
     */
    public function save(ItemConditionInterface $itemCondition): ItemConditionInterface;

    /**
     * @param ItemConditionInterface $itemCondition
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ItemConditionInterface $itemCondition): bool;

    /**
     * @param int $entityId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $entityId): bool;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return ItemConditionSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): ItemConditionSearchResultsInterface;
}
