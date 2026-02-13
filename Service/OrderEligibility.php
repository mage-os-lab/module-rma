<?php

declare(strict_types=1);

namespace MageOS\RMA\Service;

use Magento\Sales\Model\ResourceModel\Order\Collection;
use MageOS\RMA\Helper\ModuleConfig;
use MageOS\RMA\Model\ResourceModel\Item\CollectionFactory as RmaItemCollectionFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

class OrderEligibility
{
    /**
     * @param ModuleConfig $moduleConfig
     * @param RmaItemCollectionFactory $rmaItemCollectionFactory
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        protected readonly ModuleConfig $moduleConfig,
        protected readonly RmaItemCollectionFactory $rmaItemCollectionFactory,
        protected readonly OrderCollectionFactory $orderCollectionFactory,
        protected readonly OrderRepositoryInterface $orderRepository,
        protected readonly TimezoneInterface $timezone
    ) {
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    public function isOrderEligible(OrderInterface $order): bool
    {
        $storeId = (int)$order->getStoreId();

        // 1. Module enabled for this store's website
        if (!$this->moduleConfig->isEnabled($storeId)) {
            return false;
        }

        // 2. Order status is allowed
        $allowedStatuses = $this->moduleConfig->getAllowedOrderStatuses($storeId);
        if (!in_array($order->getStatus(), $allowedStatuses, true)) {
            return false;
        }

        // 3. Within return period
        if (!$this->isWithinReturnPeriod($order)) {
            return false;
        }

        // 4. Has at least one item with available qty
        $eligibleItems = $this->getEligibleItems($order);
        if (empty($eligibleItems)) {
            return false;
        }

        return true;
    }

    /**
     * @param OrderInterface $order
     * @return array
     */
    public function getEligibleItems(OrderInterface $order): array
    {
        $orderId = (int)$order->getEntityId();
        $alreadyRequested = $this->getAlreadyRequestedQty($orderId);

        $items = [];
        foreach ($order->getItems() as $orderItem) {
            // Skip parent items of configurable/bundle products
            if ($orderItem->getParentItemId()) {
                continue;
            }

            // Skip virtual and downloadable products
            $productType = $orderItem->getProductType();
            if (in_array($productType, ['virtual', 'downloadable'], true)) {
                continue;
            }

            $orderItemId = (int)$orderItem->getItemId();
            $qtyOrdered = (int)$orderItem->getQtyOrdered();
            $qtyAlreadyRequested = $alreadyRequested[$orderItemId] ?? 0;
            $qtyAvailable = $qtyOrdered - $qtyAlreadyRequested;

            if ($qtyAvailable <= 0) {
                continue;
            }

            $items[] = [
                'order_item_id' => $orderItemId,
                'name' => $orderItem->getName(),
                'sku' => $orderItem->getSku(),
                'qty_ordered' => $qtyOrdered,
                'qty_already_requested' => $qtyAlreadyRequested,
                'qty_available' => $qtyAvailable,
            ];
        }

        return $items;
    }

    /**
     * @param int $customerId
     * @param int $storeId
     * @return Collection
     */
    public function getCustomerEligibleOrders(int $customerId, int $storeId): Collection
    {
        $allowedStatuses = $this->moduleConfig->getAllowedOrderStatuses($storeId);
        $returnPeriod = $this->moduleConfig->getReturnPeriod($storeId);

        $collection = $this->orderCollectionFactory->create();
        $collection->addFieldToFilter('customer_id', $customerId);
        $collection->addFieldToFilter('store_id', $storeId);

        if (!empty($allowedStatuses)) {
            $collection->addFieldToFilter('status', ['in' => $allowedStatuses]);
        }

        if ($returnPeriod > 0) {
            $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$returnPeriod} days"));
            $collection->addFieldToFilter('created_at', ['gteq' => $cutoffDate]);
        }
        $collection->setOrder('created_at', 'desc');

        return $collection;
    }

    /**
     * @param OrderInterface $order
     * @return bool
     */
    protected function isWithinReturnPeriod(OrderInterface $order): bool
    {
        $storeId = (int)$order->getStoreId();
        $returnPeriod = $this->moduleConfig->getReturnPeriod($storeId);

        if ($returnPeriod <= 0) {
            return true;
        }

        $orderDate = strtotime($order->getCreatedAt());
        $cutoffDate = strtotime("-{$returnPeriod} days");

        return $orderDate >= $cutoffDate;
    }

    /**
     * @param int $orderId
     * @return array
     */
    public function getAlreadyRequestedQty(int $orderId): array
    {
        $collection = $this->rmaItemCollectionFactory->create();

        $collection->getSelect()->join(
            ['rma' => $collection->getTable('rma_entity')],
            'main_table.rma_id = rma.entity_id',
            []
        )->where('rma.order_id = ?', $orderId);

        $result = [];
        foreach ($collection as $item) {
            $orderItemId = (int)$item->getData('order_item_id');
            $qty = (int)$item->getData('qty_requested');
            $result[$orderItemId] = ($result[$orderItemId] ?? 0) + $qty;
        }

        return $result;
    }
}
