<?php

declare(strict_types=1);

namespace MageOS\RMA\Api;

use MageOS\RMA\Api\Data\ResolutionTypeInterface;
use MageOS\RMA\Api\Data\ResolutionTypeSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface ResolutionTypeRepositoryInterface
{
    /**
     * @param int $entityId
     * @return ResolutionTypeInterface
     * @throws NoSuchEntityException
     */
    public function get(int $entityId): ResolutionTypeInterface;

    /**
     * @param ResolutionTypeInterface $resolutionType
     * @return ResolutionTypeInterface
     * @throws CouldNotSaveException
     */
    public function save(ResolutionTypeInterface $resolutionType): ResolutionTypeInterface;

    /**
     * @param ResolutionTypeInterface $resolutionType
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ResolutionTypeInterface $resolutionType): bool;

    /**
     * @param int $entityId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $entityId): bool;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return ResolutionTypeSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): ResolutionTypeSearchResultsInterface;
}
