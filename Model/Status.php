<?php

declare(strict_types=1);

namespace MageOS\RMA\Model;

use MageOS\RMA\Api\Data\StatusInterface;
use MageOS\RMA\Model\ResourceModel\Status as ResourceModel;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Exception\LocalizedException;

class Status extends AbstractModel implements StatusInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'rma_status_model';

    /**
     * @return void
     * @throws LocalizedException
     */
    protected function _construct(): void
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * @return int|null
     */
    public function getEntityId(): ?int
    {
        $id = $this->getData(self::ENTITY_ID);
        return $id !== null ? (int)$id : null;
    }

    /**
     * @param $entityId
     * @return self
     */
    public function setEntityId($entityId): self
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return (string)$this->getData(self::CODE);
    }

    /**
     * @param string $code
     * @return self
     */
    public function setCode(string $code): self
    {
        return $this->setData(self::CODE, $code);
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return (string)$this->getData(self::LABEL);
    }

    /**
     * @param string $label
     * @return self
     */
    public function setLabel(string $label): self
    {
        return $this->setData(self::LABEL, $label);
    }

    /**
     * @return int
     */
    public function getIsActive(): int
    {
        return (int)$this->getData(self::IS_ACTIVE);
    }

    /**
     * @param int $isActive
     * @return self
     */
    public function setIsActive(int $isActive): self
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * @return int
     */
    public function getSortOrder(): int
    {
        return (int)$this->getData(self::SORT_ORDER);
    }

    /**
     * @param int $sortOrder
     * @return self
     */
    public function setSortOrder(int $sortOrder): self
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    /**
     * @return array
     */
    public function getStoreLabels(): array
    {
        return $this->getData('store_labels') ?? [];
    }

    /**
     * @param array $storeLabels
     * @return self
     */
    public function setStoreLabels(array $storeLabels): self
    {
        return $this->setData('store_labels', $storeLabels);
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getStoreLabel(int $storeId): string
    {
        $labels = $this->getStoreLabels();

        return !empty($labels[$storeId]) ? (string)$labels[$storeId] : $this->getLabel();
    }
}
