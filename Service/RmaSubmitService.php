<?php

declare(strict_types=1);

namespace MageOS\RMA\Service;

use MageOS\RMA\Api\Data\RMAInterface;
use MageOS\RMA\Api\Data\RMAInterfaceFactory;
use MageOS\RMA\Api\Data\StatusInterface;
use MageOS\RMA\Api\ItemRepositoryInterface;
use MageOS\RMA\Api\RMARepositoryInterface;
use MageOS\RMA\Helper\ModuleConfig;
use MageOS\RMA\Model\ItemFactory;
use MageOS\RMA\Model\RMA\StatusCodes;
use MageOS\RMA\Model\ResourceModel\Status\CollectionFactory as StatusCollectionFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;

class RmaSubmitService
{
    /**
     * @param RMARepositoryInterface $rmaRepository
     * @param RMAInterfaceFactory $rmaFactory
     * @param ItemFactory $itemFactory
     * @param ItemRepositoryInterface $itemRepository
     * @param StatusCollectionFactory $statusCollectionFactory
     * @param ModuleConfig $moduleConfig
     */
    public function __construct(
        protected readonly RMARepositoryInterface $rmaRepository,
        protected readonly RMAInterfaceFactory $rmaFactory,
        protected readonly ItemFactory $itemFactory,
        protected readonly ItemRepositoryInterface $itemRepository,
        protected readonly StatusCollectionFactory $statusCollectionFactory,
        protected readonly ModuleConfig $moduleConfig
    ) {
    }

    /**
     * @param array $itemsData
     * @return array
     */
    public function getSelectedItems(array $itemsData): array
    {
        $selected = [];

        foreach ($itemsData as $orderItemId => $itemData) {
            if (empty($itemData['selected'])) {
                continue;
            }

            $qtyRequested = (int)($itemData['qty_requested'] ?? 0);
            if ($qtyRequested <= 0) {
                continue;
            }

            $selected[(int)$orderItemId] = [
                'qty_requested' => $qtyRequested,
                'condition_id' => !empty($itemData['condition_id']) ? (int)$itemData['condition_id'] : null,
            ];
        }

        return $selected;
    }

    /**
     * @param OrderInterface $order
     * @param int|null $customerId
     * @param string $customerEmail
     * @param string $customerName
     * @param int $reasonId
     * @param int $resolutionTypeId
     * @param array $selectedItems
     * @return RMAInterface
     * @throws CouldNotSaveException
     * @throws LocalizedException
     */
    public function createRma(
        OrderInterface $order,
        ?int $customerId,
        string $customerEmail,
        string $customerName,
        int $reasonId,
        int $resolutionTypeId,
        array $selectedItems
    ): RMAInterface {
        $storeId = (int)$order->getStoreId();

        $statusCode = $this->moduleConfig->isAutoApproveEnabled($storeId)
            ? StatusCodes::APPROVED
            : StatusCodes::NEW_REQUEST;
        $statusId = $this->getStatusIdByCode($statusCode);

        if (!$statusId) {
            throw new LocalizedException(__('Could not determine the initial RMA status.'));
        }

        $rma = $this->rmaFactory->create();
        $rma->setOrderId((int)$order->getEntityId());
        $rma->setCustomerId($customerId);
        $rma->setStoreId($storeId);
        $rma->setCustomerEmail($customerEmail);
        $rma->setCustomerName($customerName);
        $rma->setStatusId($statusId);
        $rma->setReasonId($reasonId);
        $rma->setResolutionTypeId($resolutionTypeId);

        $this->rmaRepository->save($rma);
        $this->saveItems((int)$rma->getEntityId(), $selectedItems);

        return $rma;
    }

    /**
     * @param int $rmaId
     * @param array $selectedItems
     * @return void
     * @throws CouldNotSaveException
     */
    public function saveItems(int $rmaId, array $selectedItems): void
    {
        foreach ($selectedItems as $orderItemId => $itemData) {
            $item = $this->itemFactory->create();
            $item->setRmaId($rmaId);
            $item->setOrderItemId($orderItemId);
            $item->setQtyRequested($itemData['qty_requested']);
            $item->setConditionId($itemData['condition_id']);
            $this->itemRepository->save($item);
        }
    }

    /**
     * @param string $code
     * @return int|null
     */
    protected function getStatusIdByCode(string $code): ?int
    {
        $collection = $this->statusCollectionFactory->create();
        $collection->addFieldToFilter(StatusInterface::CODE, $code);
        $collection->setPageSize(1);

        $status = $collection->getFirstItem();
        return $status->getEntityId() ? (int)$status->getEntityId() : null;
    }
}
