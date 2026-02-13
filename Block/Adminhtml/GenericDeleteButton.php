<?php

declare(strict_types=1);

namespace MageOS\RMA\Block\Adminhtml;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class GenericDeleteButton implements ButtonProviderInterface
{
    /**
     * @param Context $context
     * @param string $confirmMessage
     */
    public function __construct(
        protected readonly Context $context,
        protected readonly string $confirmMessage = 'Are you sure you want to delete this?'
    ) {
    }

    /**
     * @return array
     */
    public function getButtonData(): array
    {
        $entityId = (int)$this->context->getRequest()->getParam('entity_id');

        if (!$entityId) {
            return [];
        }

        return [
            'label' => __('Delete'),
            'class' => 'delete',
            'on_click' => 'deleteConfirm(\''
                . __($this->confirmMessage)
                . '\', \''
                . $this->context->getUrlBuilder()->getUrl('*/*/delete', ['entity_id' => $entityId])
                . '\', {data: {}})',
            'sort_order' => 20,
        ];
    }
}
