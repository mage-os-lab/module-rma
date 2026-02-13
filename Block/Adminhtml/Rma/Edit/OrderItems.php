<?php

declare(strict_types=1);

namespace MageOS\RMA\Block\Adminhtml\Rma\Edit;

use MageOS\RMA\Api\Data\ItemInterface;
use MageOS\RMA\Api\ItemConditionRepositoryInterface;
use MageOS\RMA\Api\RMARepositoryInterface;
use MageOS\RMA\Model\ResourceModel\Item\CollectionFactory as ItemCollectionFactory;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderItemRepositoryInterface;

class OrderItems extends Template
{
    /**
     * @var string
     */
    protected $_template = 'MageOS_RMA::rma/edit/order-items.phtml';

    /**
     * @param Context $context
     * @param RMARepositoryInterface $rmaRepository
     * @param ItemCollectionFactory $itemCollectionFactory
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param ItemConditionRepositoryInterface $itemConditionRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        protected readonly RMARepositoryInterface $rmaRepository,
        protected readonly ItemCollectionFactory $itemCollectionFactory,
        protected readonly OrderItemRepositoryInterface $orderItemRepository,
        protected readonly ItemConditionRepositoryInterface $itemConditionRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return array|null
     */
    public function getRmaItems(): ?array
    {
        $rmaId = (int)$this->getRequest()->getParam('entity_id');

        if (!$rmaId) {
            return null;
        }

        $collection = $this->itemCollectionFactory->create();
        $collection->addFieldToFilter('rma_id', $rmaId);

        return $collection->getItems();
    }

    /**
     * @param int $orderItemId
     * @return string[]
     */
    public function getOrderItemInfo(int $orderItemId): array
    {
        try {
            $orderItem = $this->orderItemRepository->get($orderItemId);
            return [
                'name' => (string)$orderItem->getName(),
                'sku' => (string)$orderItem->getSku(),
            ];
        } catch (NoSuchEntityException $e) {
            return [
                'name' => (string)__('Unknown Product'),
                'sku' => '',
            ];
        }
    }

    /**
     * @param int|null $conditionId
     * @return string
     */
    public function getConditionLabel(?int $conditionId): string
    {
        if ($conditionId === null) {
            return '—';
        }

        try {
            $condition = $this->itemConditionRepository->get($conditionId);
            return (string)$condition->getLabel();
        } catch (NoSuchEntityException $e) {
            return (string)__('Unknown');
        }
    }

    /**
     * @return bool
     */
    public function isEditMode(): bool
    {
        return (bool)$this->getRequest()->getParam('entity_id');
    }
}
