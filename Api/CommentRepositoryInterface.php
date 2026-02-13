<?php

declare(strict_types=1);

namespace MageOS\RMA\Api;

use MageOS\RMA\Api\Data\CommentInterface;
use MageOS\RMA\Api\Data\CommentSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface CommentRepositoryInterface
{
    /**
     * @param int $entityId
     * @return CommentInterface
     * @throws NoSuchEntityException
     */
    public function get(int $entityId): CommentInterface;

    /**
     * @param CommentInterface $comment
     * @return CommentInterface
     * @throws CouldNotSaveException
     */
    public function save(CommentInterface $comment): CommentInterface;

    /**
     * @param CommentInterface $comment
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(CommentInterface $comment): bool;

    /**
     * @param int $entityId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $entityId): bool;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return CommentSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): CommentSearchResultsInterface;
}
