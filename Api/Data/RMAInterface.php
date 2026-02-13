<?php

declare(strict_types=1);

namespace MageOS\RMA\Api\Data;

interface RMAInterface
{
    public const string ENTITY_ID = 'entity_id';
    public const string INCREMENT_ID = 'increment_id';
    public const string ORDER_ID = 'order_id';
    public const string CUSTOMER_ID = 'customer_id';
    public const string STORE_ID = 'store_id';
    public const string CUSTOMER_EMAIL = 'customer_email';
    public const string CUSTOMER_NAME = 'customer_name';
    public const string STATUS_ID = 'status_id';
    public const string REASON_ID = 'reason_id';
    public const string RESOLUTION_TYPE_ID = 'resolution_type_id';
    public const string CREATED_AT = 'created_at';
    public const string UPDATED_AT = 'updated_at';

    /**
     * @return int|null
     */
    public function getEntityId(): ?int;

    /**
     * @param int $entityId
     * @return $this
     */
    public function setEntityId(int $entityId): self;

    /**
     * @return string|null
     */
    public function getIncrementId(): ?string;

    /**
     * @param string|null $incrementId
     * @return $this
     */
    public function setIncrementId(?string $incrementId): self;

    /**
     * @return int
     */
    public function getOrderId(): int;

    /**
     * @param int $orderId
     * @return $this
     */
    public function setOrderId(int $orderId): self;

    /**
     * @return int|null
     */
    public function getCustomerId(): ?int;

    /**
     * @param int|null $customerId
     * @return $this
     */
    public function setCustomerId(?int $customerId): self;

    /**
     * @return int
     */
    public function getStoreId(): int;

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId(int $storeId): self;

    /**
     * @return string
     */
    public function getCustomerEmail(): string;

    /**
     * @param string $customerEmail
     * @return $this
     */
    public function setCustomerEmail(string $customerEmail): self;

    /**
     * @return string
     */
    public function getCustomerName(): string;

    /**
     * @param string $customerName
     * @return $this
     */
    public function setCustomerName(string $customerName): self;

    /**
     * @return int
     */
    public function getStatusId(): int;

    /**
     * @param int $statusId
     * @return $this
     */
    public function setStatusId(int $statusId): self;

    /**
     * @return int
     */
    public function getReasonId(): int;

    /**
     * @param int $reasonId
     * @return $this
     */
    public function setReasonId(int $reasonId): self;

    /**
     * @return int
     */
    public function getResolutionTypeId(): int;

    /**
     * @param int $resolutionTypeId
     * @return $this
     */
    public function setResolutionTypeId(int $resolutionTypeId): self;

    /**
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt(string $createdAt): self;

    /**
     * @return string|null
     */
    public function getUpdatedAt(): ?string;

    /**
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt(string $updatedAt): self;
}
