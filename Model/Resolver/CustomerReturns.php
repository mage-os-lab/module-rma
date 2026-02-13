<?php

declare(strict_types=1);

namespace MageOS\RMA\Model\Resolver;

use MageOS\RMA\Api\RMARepositoryInterface;
use MageOS\RMA\Model\Resolver\DataProvider\ReturnDataProvider;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomerReturns implements ResolverInterface
{
    /**
     * @param RMARepositoryInterface $rmaRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ReturnDataProvider $returnDataProvider
     */
    public function __construct(
        protected readonly RMARepositoryInterface $rmaRepository,
        protected readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        protected readonly ReturnDataProvider $returnDataProvider
    ) {
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws GraphQlAuthorizationException|NoSuchEntityException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null): array
    {
        if ($context->getExtensionAttributes()->getIsCustomer() === false) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        $customerId = (int)$context->getUserId();
        $pageSize = $args['pageSize'] ?? 20;
        $currentPage = $args['currentPage'] ?? 1;

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('customer_id', $customerId)
            ->setPageSize($pageSize)
            ->setCurrentPage($currentPage)
            ->create();

        $searchResults = $this->rmaRepository->getList($searchCriteria);

        $items = [];
        foreach ($searchResults->getItems() as $rma) {
            $items[] = $this->returnDataProvider->formatRma($rma);
        }

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
