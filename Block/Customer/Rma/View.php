<?php

declare(strict_types=1);

namespace MageOS\RMA\Block\Customer\Rma;

use MageOS\RMA\Api\Data\RMAInterface;
use MageOS\RMA\Model\ResourceModel\Item\CollectionFactory as ItemCollectionFactory;
use MageOS\RMA\Service\LabelResolver;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class View extends Template
{
    /**
     * @param Context $context
     * @param LabelResolver $labelResolver
     * @param ItemCollectionFactory $itemCollectionFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        protected readonly LabelResolver $labelResolver,
        protected readonly ItemCollectionFactory $itemCollectionFactory,
        protected readonly OrderRepositoryInterface $orderRepository,
        protected readonly OrderItemRepositoryInterface $orderItemRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return RMAInterface|null
     */
    public function getRma(): ?RMAInterface
    {
        return $this->getRequest()->getParam('rma_entity');
    }

    /**
     * @return string
     */
    public function getOrderIncrementId(): string
    {
        $rma = $this->getRma();
        if (!$rma) {
            return '';
        }

        try {
            return $this->orderRepository->get($rma->getOrderId())->getIncrementId();
        } catch (NoSuchEntityException) {
            return (string)$rma->getOrderId();
        }
    }

    /**
     * @return string
     */
    public function getStatusLabel(): string
    {
        $rma = $this->getRma();

        return $rma ? $this->labelResolver->resolve(LabelResolver::TYPE_STATUS, $rma->getStatusId()) : '';
    }

    /**
     * @return string
     */
    public function getReasonLabel(): string
    {
        $rma = $this->getRma();

        return $rma ? $this->labelResolver->resolve(LabelResolver::TYPE_REASON, $rma->getReasonId()) : '';
    }

    /**
     * @return string
     */
    public function getResolutionTypeLabel(): string
    {
        $rma = $this->getRma();

        return $rma ? $this->labelResolver->resolve(LabelResolver::TYPE_RESOLUTION_TYPE, $rma->getResolutionTypeId()) : '';
    }

    /**
     * @return array
     */
    public function getRmaItems(): array
    {
        $rma = $this->getRma();
        if (!$rma) {
            return [];
        }

        $collection = $this->itemCollectionFactory->create();
        $collection->addFieldToFilter('rma_id', $rma->getEntityId());

        $items = [];
        foreach ($collection as $item) {
            $items[] = $this->buildItemData($item);
        }

        return $items;
    }

    /**
     * @param object $item
     * @return array
     */
    protected function buildItemData(object $item): array
    {
        [$name, $sku] = $this->resolveOrderItemInfo($item->getOrderItemId());

        return [
            'name' => $name,
            'sku' => $sku,
            'qty_requested' => $item->getQtyRequested(),
            'condition_label' => $this->resolveConditionLabel($item->getConditionId()),
        ];
    }

    /**
     * @param int $orderItemId
     * @return array
     */
    protected function resolveOrderItemInfo(int $orderItemId): array
    {
        try {
            $orderItem = $this->orderItemRepository->get($orderItemId);

            return [$orderItem->getName(), $orderItem->getSku()];
        } catch (NoSuchEntityException) {
            return [__('N/A'), __('N/A')];
        }
    }

    /**
     * @param int|null $conditionId
     * @return string
     */
    protected function resolveConditionLabel(?int $conditionId): string
    {
        if (!$conditionId) {
            return '-';
        }

        $label = $this->labelResolver->resolve(LabelResolver::TYPE_ITEM_CONDITION, $conditionId);

        return $label ?: '-';
    }

    /**
     * @return string
     */
    public function getBackUrl(): string
    {
        return $this->getUrl('rma/customer/history');
    }
}
