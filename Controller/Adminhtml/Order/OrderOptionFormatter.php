<?php

declare(strict_types=1);

namespace MageOS\RMA\Controller\Adminhtml\Order;

use Magento\Sales\Api\Data\OrderInterface;

trait OrderOptionFormatter
{
    /**
     * @param OrderInterface $order
     * @return array
     */
    protected function formatOrderOption(OrderInterface $order): array
    {
        return [
            'value' => (string)$order->getEntityId(),
            'label' => sprintf(
                '#%s — %s (%s)',
                $order->getIncrementId(),
                $order->getCustomerName() ?: __('Guest'),
                $order->getCustomerEmail()
            ),
            'path' => '',
            'order_id' => (int)$order->getEntityId(),
            'increment_id' => $order->getIncrementId(),
            'customer_id' => $order->getCustomerId() ? (int)$order->getCustomerId() : null,
            'customer_name' => $order->getCustomerName() ?: (string)__('Guest'),
            'customer_email' => $order->getCustomerEmail(),
            'store_id' => (int)$order->getStoreId(),
            'optgroup' => false,
        ];
    }
}
