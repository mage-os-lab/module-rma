<?php

declare(strict_types=1);

namespace MageOS\RMA\Api\Data;

interface ItemInterface
{
    public const string ENTITY_ID = 'entity_id';
    public const string RMA_ID = 'rma_id';
    public const string ORDER_ITEM_ID = 'order_item_id';
    public const string QTY_REQUESTED = 'qty_requested';
    public const string QTY_APPROVED = 'qty_approved';
    public const string QTY_RETURNED = 'qty_returned';
    public const string CONDITION_ID = 'condition_id';
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
     * @return int
     */
    public function getRmaId(): int;

    /**
     * @param int $rmaId
     * @return $this
     */
    public function setRmaId(int $rmaId): self;

    /**
     * @return int
     */
    public function getOrderItemId(): int;

    /**
     * @param int $orderItemId
     * @return $this
     */
    public function setOrderItemId(int $orderItemId): self;

    /**
     * @return int
     */
    public function getQtyRequested(): int;

    /**
     * @param int $qtyRequested
     * @return $this
     */
    public function setQtyRequested(int $qtyRequested): self;

    /**
     * @return int|null
     */
    public function getQtyApproved(): ?int;

    /**
     * @param int|null $qtyApproved
     * @return $this
     */
    public function setQtyApproved(?int $qtyApproved): self;

    /**
     * @return int|null
     */
    public function getQtyReturned(): ?int;

    /**
     * @param int|null $qtyReturned
     * @return $this
     */
    public function setQtyReturned(?int $qtyReturned): self;

    /**
     * @return int|null
     */
    public function getConditionId(): ?int;

    /**
     * @param int|null $conditionId
     * @return $this
     */
    public function setConditionId(?int $conditionId): self;

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
