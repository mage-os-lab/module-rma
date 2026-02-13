<?php

declare(strict_types=1);

namespace MageOS\RMA\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface RMASearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return RMAInterface[]
     */
    public function getItems(): array;

    /**
     * @param RMAInterface[] $items
     * @return $this
     */
    public function setItems(array $items): self;
}
