<?php

declare(strict_types=1);

namespace MageOS\RMA\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;

trait ReturnInputTrait
{
    /**
     * @param array $input
     * @param array $requiredFields
     * @throws GraphQlInputException
     */
    protected function validateRequiredFields(array $input, array $requiredFields): void
    {
        foreach ($requiredFields as $field => $label) {
            if (empty($input[$field])) {
                throw new GraphQlInputException(__('%1 is required.', $label));
            }
        }
    }

    /**
     * @param array $itemsInput
     * @return array
     */
    protected function buildSelectedItems(array $itemsInput): array
    {
        $selected = [];
        foreach ($itemsInput as $item) {
            $selected[(int)$item['order_item_id']] = [
                'qty_requested' => (int)$item['qty_requested'],
                'condition_id' => isset($item['condition_id']) ? (int)$item['condition_id'] : null,
            ];
        }

        return $selected;
    }
}
