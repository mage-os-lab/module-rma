<?php

declare(strict_types=1);

namespace MageOS\RMA\Model\Resolver\DataProvider;

use MageOS\RMA\Api\CommentRepositoryInterface;
use MageOS\RMA\Api\Data\CommentInterface;
use MageOS\RMA\Api\Data\RMAInterface;
use MageOS\RMA\Api\ItemRepositoryInterface;
use MageOS\RMA\Service\LabelResolver;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;

class ReturnDataProvider
{
    /**
     * @param LabelResolver $labelResolver
     * @param ItemRepositoryInterface $itemRepository
     * @param CommentRepositoryInterface $commentRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        protected readonly LabelResolver $labelResolver,
        protected readonly ItemRepositoryInterface $itemRepository,
        protected readonly CommentRepositoryInterface $commentRepository,
        protected readonly OrderRepositoryInterface $orderRepository,
        protected readonly SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
    }

    /**
     * @param RMAInterface $rma
     * @return array
     */
    public function formatRma(RMAInterface $rma): array
    {
        $orderId = (int)$rma->getOrderId();

        return [
            'rma_id' => $rma->getEntityId(),
            'increment_id' => $rma->getIncrementId(),
            'order_number' => $this->getOrderIncrementId($orderId),
            'status' => $this->labelResolver->resolveAsArray(LabelResolver::TYPE_STATUS, $rma->getStatusId()),
            'reason' => $this->labelResolver->resolveAsArray(LabelResolver::TYPE_REASON, $rma->getReasonId()),
            'resolution_type' => $this->labelResolver->resolveAsArray(LabelResolver::TYPE_RESOLUTION_TYPE, $rma->getResolutionTypeId()),
            'items' => $this->getItems((int)$rma->getEntityId(), $orderId),
            'comments' => $this->getVisibleComments((int)$rma->getEntityId()),
            'created_at' => $rma->getCreatedAt(),
            'updated_at' => $rma->getUpdatedAt(),
        ];
    }

    /**
     * @param int $rmaId
     * @param int $orderId
     * @return array
     */
    protected function getItems(int $rmaId, int $orderId): array
    {
        $items = $this->loadRmaItems($rmaId);
        $orderItemsMap = $this->buildOrderItemsMap($orderId);

        $result = [];
        foreach ($items as $item) {
            $result[] = $this->buildItemData($item, $orderItemsMap);
        }

        return $result;
    }

    /**
     * @param int $rmaId
     * @return array
     */
    protected function loadRmaItems(int $rmaId): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('rma_id', $rmaId)
            ->create();

        return $this->itemRepository->getList($searchCriteria)->getItems();
    }

    /**
     * @param int $orderId
     * @return array
     */
    protected function buildOrderItemsMap(int $orderId): array
    {
        try {
            $order = $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException) {
            return [];
        }

        $map = [];
        foreach ($order->getItems() as $orderItem) {
            $map[(int)$orderItem->getItemId()] = $orderItem;
        }

        return $map;
    }

    /**
     * @param object $item
     * @param array $orderItemsMap
     * @return array
     */
    protected function buildItemData(object $item, array $orderItemsMap): array
    {
        $orderItem = $orderItemsMap[$item->getOrderItemId()] ?? null;

        return [
            'item_id' => $item->getEntityId(),
            'order_item_id' => $item->getOrderItemId(),
            'product_name' => $orderItem?->getName(),
            'product_sku' => $orderItem?->getSku(),
            'qty_requested' => $item->getQtyRequested(),
            'qty_approved' => $item->getQtyApproved(),
            'qty_returned' => $item->getQtyReturned(),
            'condition' => $this->resolveItemCondition($item->getConditionId()),
        ];
    }

    /**
     * @param int|null $conditionId
     * @return array|null
     */
    protected function resolveItemCondition(?int $conditionId): ?array
    {
        if (!$conditionId) {
            return null;
        }

        return $this->labelResolver->resolveAsArray(LabelResolver::TYPE_ITEM_CONDITION, $conditionId);
    }

    /**
     * @param int $rmaId
     * @return array
     */
    public function getVisibleComments(int $rmaId): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('rma_id', $rmaId)
            ->addFilter('is_visible_to_customer', 1)
            ->create();

        $comments = $this->commentRepository->getList($searchCriteria)->getItems();

        return array_map(fn(CommentInterface $c) => [
            'comment_id' => $c->getEntityId(),
            'author_type' => $c->getAuthorType(),
            'author_name' => $c->getAuthorName(),
            'comment' => $c->getComment(),
            'created_at' => $c->getCreatedAt(),
        ], array_values($comments));
    }

    /**
     * @param int $orderId
     * @return string|null
     */
    protected function getOrderIncrementId(int $orderId): ?string
    {
        try {
            return $this->orderRepository->get($orderId)->getIncrementId();
        } catch (NoSuchEntityException) {
            return null;
        }
    }
}
