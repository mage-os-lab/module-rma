<?php

declare(strict_types=1);

namespace MageOS\RMA\Api\Data;

interface CommentInterface
{
    public const string ENTITY_ID = 'entity_id';
    public const string RMA_ID = 'rma_id';
    public const string AUTHOR_TYPE = 'author_type';
    public const string AUTHOR_NAME = 'author_name';
    public const string COMMENT = 'comment';
    public const string IS_VISIBLE_TO_CUSTOMER = 'is_visible_to_customer';
    public const string CREATED_AT = 'created_at';

    public const string AUTHOR_TYPE_CUSTOMER = 'customer';
    public const string AUTHOR_TYPE_ADMIN = 'admin';

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
     * @return string
     */
    public function getAuthorType(): string;

    /**
     * @param string $authorType
     * @return $this
     */
    public function setAuthorType(string $authorType): self;

    /**
     * @return string
     */
    public function getAuthorName(): string;

    /**
     * @param string $authorName
     * @return $this
     */
    public function setAuthorName(string $authorName): self;

    /**
     * @return string
     */
    public function getComment(): string;

    /**
     * @param string $comment
     * @return $this
     */
    public function setComment(string $comment): self;

    /**
     * @return bool
     */
    public function getIsVisibleToCustomer(): bool;

    /**
     * @param bool $isVisible
     * @return $this
     */
    public function setIsVisibleToCustomer(bool $isVisible): self;

    /**
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt(string $createdAt): self;
}
