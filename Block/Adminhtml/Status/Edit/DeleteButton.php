<?php

declare(strict_types=1);

namespace MageOS\RMA\Block\Adminhtml\Status\Edit;

use MageOS\RMA\Api\StatusRepositoryInterface;
use MageOS\RMA\Block\Adminhtml\GenericDeleteButton;
use MageOS\RMA\Model\RMA\StatusCodes;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;

class DeleteButton extends GenericDeleteButton
{
    /**
     * @param Context $context
     * @param StatusRepositoryInterface $statusRepository
     * @param string $confirmMessage
     */
    public function __construct(
        Context $context,
        protected readonly StatusRepositoryInterface $statusRepository,
        string $confirmMessage = 'Are you sure you want to delete this status?'
    ) {
        parent::__construct($context, $confirmMessage);
    }

    /**
     * @return array
     */
    public function getButtonData(): array
    {
        if ($this->isProtectedStatus()) {
            return [];
        }

        return parent::getButtonData();
    }

    /**
     * @return bool
     */
    protected function isProtectedStatus(): bool
    {
        $entityId = (int)$this->context->getRequest()->getParam('entity_id');

        if (!$entityId) {
            return false;
        }

        try {
            $status = $this->statusRepository->get($entityId);

            return StatusCodes::isProtected($status->getCode());
        } catch (NoSuchEntityException) {
            return false;
        }
    }
}
