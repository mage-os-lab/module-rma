<?php

declare(strict_types=1);

namespace MageOS\RMA\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface ItemConditionSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return ItemConditionInterface[]
     */
    public function getItems(): array;

    /**
     * @param ItemConditionInterface[] $items
     * @return $this
     */
    public function setItems(array $items): self;
}
