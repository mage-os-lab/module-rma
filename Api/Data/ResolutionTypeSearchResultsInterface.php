<?php

declare(strict_types=1);

namespace MageOS\RMA\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface ResolutionTypeSearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return ResolutionTypeInterface[]
     */
    public function getItems(): array;

    /**
     * @param ResolutionTypeInterface[] $items
     * @return $this
     */
    public function setItems(array $items): self;
}
