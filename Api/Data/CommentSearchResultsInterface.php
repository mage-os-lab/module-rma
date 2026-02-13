<?php

declare(strict_types=1);

namespace MageOS\RMA\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface CommentSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return CommentInterface[]
     */
    public function getItems(): array;

    /**
     * @param CommentInterface[] $items
     * @return $this
     */
    public function setItems(array $items): self;
}
