<?php

declare(strict_types=1);

namespace MageOS\RMA\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class RmaActions extends Column
{
    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        protected readonly UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            if (!isset($item['entity_id'])) {
                continue;
            }

            $item[$this->getData('name')] = [
                'edit' => [
                    'href' => $this->urlBuilder->getUrl('rma/rma/edit', [
                        'entity_id' => $item['entity_id'],
                    ]),
                    'label' => __('View'),
                ],
                'delete' => [
                    'href' => $this->urlBuilder->getUrl('rma/rma/delete', [
                        'entity_id' => $item['entity_id'],
                    ]),
                    'label' => __('Delete'),
                    'confirm' => [
                        'title' => __('Delete RMA #%1', $item['increment_id'] ?? $item['entity_id']),
                        'message' => __('Are you sure you want to delete this RMA request?'),
                    ],
                    'post' => true,
                ],
            ];
        }

        return $dataSource;
    }
}
