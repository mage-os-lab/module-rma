<?php

declare(strict_types=1);

namespace MageOS\RMA\Ui\DataProvider\Form\Modifier;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Store\Api\Data\WebsiteInterface;

class StoreLabels implements ModifierInterface
{
    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        protected readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data): array
    {
        return $data;
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta): array
    {
        if ($this->storeManager->isSingleStoreMode()) {
            return $meta;
        }

        $meta['store_labels'] = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Store View Specific Labels'),
                        'componentType' => 'fieldset',
                        'collapsible' => true,
                        'opened' => false,
                        'sortOrder' => 20,
                    ],
                ],
            ],
            'children' => $this->getStoreLabelsFields(),
        ];

        return $meta;
    }

    /**
     * @return array
     */
    protected function getStoreLabelsFields(): array
    {
        $fields = [];
        $sortOrder = 0;

        foreach ($this->storeManager->getWebsites() as $website) {
            $fields['website_' . $website->getId()] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'componentType' => 'fieldset',
                            'label' => $website->getName(),
                            'collapsible' => true,
                            'opened' => true,
                            'sortOrder' => $sortOrder++,
                        ],
                    ],
                ],
                'children' => $this->getWebsiteStoreFields($website),
            ];
        }

        return $fields;
    }

    /**
     * @param WebsiteInterface $website
     * @return array
     */
    protected function getWebsiteStoreFields(WebsiteInterface $website): array
    {
        $fields = [];
        $sortOrder = 0;

        foreach ($website->getGroups() as $group) {
            $stores = $group->getStores();
            if (empty($stores)) {
                continue;
            }

            foreach ($stores as $store) {
                $fields['store_label_' . $store->getId()] = [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Field::NAME,
                                'formElement' => Input::NAME,
                                'dataType' => 'text',
                                'label' => $store->getName(),
                                'dataScope' => 'store_labels.' . $store->getId(),
                                'sortOrder' => $sortOrder++,
                                'notice' => $group->getName(),
                            ],
                        ],
                    ],
                ];
            }
        }

        return $fields;
    }
}
