<?php

declare(strict_types=1);

namespace MageOS\RMA\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Sales\Model\Order\Config as OrderConfig;

class OrderStatuses implements OptionSourceInterface
{
    /**
     * @param OrderConfig $orderConfig
     */
    public function __construct(
        protected readonly OrderConfig $orderConfig
    ) {
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [];

        foreach ($this->orderConfig->getStatuses() as $code => $label) {
            $options[] = [
                'value' => $code,
                'label' => $label,
            ];
        }

        return $options;
    }
}
