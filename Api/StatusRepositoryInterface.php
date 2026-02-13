<?php

declare(strict_types=1);

namespace MageOS\RMA\Api;

use MageOS\RMA\Api\Data\StatusInterface;
use MageOS\RMA\Api\Data\StatusSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface StatusRepositoryInterface
{
    /**
     * @param int $entityId
     * @return StatusInterface
     * @throws NoSuchEntityException
     */
    public function get(int $entityId): StatusInterface;

    /**
     * @param StatusInterface $status
     * @return StatusInterface
     * @throws CouldNotSaveException
     */
    public function save(StatusInterface $status): StatusInterface;

    /**
     * @param StatusInterface $status
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(StatusInterface $status): bool;

    /**
     * @param int $entityId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $entityId): bool;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return StatusSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): StatusSearchResultsInterface;
}
