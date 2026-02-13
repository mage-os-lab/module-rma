<?php

declare(strict_types=1);

namespace MageOS\RMA\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface ItemSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return ItemInterface[]
     */
    public function getItems(): array;

    /**
     * @param ItemInterface[] $items
     * @return $this
     */
    public function setItems(array $items): self;
}
