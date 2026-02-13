<?php

declare(strict_types=1);

namespace MageOS\RMA\Model\Resolver;

use MageOS\RMA\Api\CommentRepositoryInterface;
use MageOS\RMA\Api\Data\CommentInterface;
use MageOS\RMA\Api\RMARepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;

class ReturnComments implements ResolverInterface
{
    use CustomerRmaAccessTrait;

    /**
     * @param RMARepositoryInterface $rmaRepository
     * @param CommentRepositoryInterface $commentRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        protected readonly RMARepositoryInterface $rmaRepository,
        protected readonly CommentRepositoryInterface $commentRepository,
        protected readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        protected readonly SortOrderBuilder $sortOrderBuilder
    ) {
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws GraphQlAuthorizationException
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null): array
    {
        $rmaId = (int)($args['rma_id'] ?? 0);
        $this->loadCustomerRma($context, $rmaId);

        $pageSize = $args['pageSize'] ?? 50;
        $currentPage = $args['currentPage'] ?? 1;

        $sortOrder = $this->sortOrderBuilder
            ->setField('created_at')
            ->setAscendingDirection()
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('rma_id', $rmaId)
            ->addFilter('is_visible_to_customer', 1)
            ->setSortOrders([$sortOrder])
            ->setPageSize($pageSize)
            ->setCurrentPage($currentPage)
            ->create();

        $searchResults = $this->commentRepository->getList($searchCriteria);

        $items = array_map(fn(CommentInterface $c) => [
            'comment_id' => $c->getEntityId(),
            'author_type' => $c->getAuthorType(),
            'author_name' => $c->getAuthorName(),
            'comment' => $c->getComment(),
            'created_at' => $c->getCreatedAt(),
        ], array_values($searchResults->getItems()));

        return [
            'items' => $items,
            'total_count' => $searchResults->getTotalCount(),
            'page_info' => [
                'page_size' => $pageSize,
                'current_page' => $currentPage,
                'total_pages' => $pageSize ? (int)ceil($searchResults->getTotalCount() / $pageSize) : 0,
            ],
        ];
    }
}
