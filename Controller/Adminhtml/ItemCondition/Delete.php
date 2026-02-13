<?php

declare(strict_types=1);

namespace MageOS\RMA\Controller\Adminhtml\ItemCondition;

use Magento\Framework\App\Action\HttpPostActionInterface;
use MageOS\RMA\Api\ItemConditionRepositoryInterface;
use MageOS\RMA\Controller\Adminhtml\ItemCondition as BaseController;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Exception;

class Delete extends BaseController implements HttpPostActionInterface
{
    /**
     * @param Context $context
     * @param ItemConditionRepositoryInterface $itemConditionRepository
     */
    public function __construct(
        Context $context,
        protected readonly ItemConditionRepositoryInterface $itemConditionRepository
    ) {
        parent::__construct($context);
    }

    /**
     * @return ResultInterface|ResponseInterface|Redirect
     */
    public function execute(): ResultInterface|ResponseInterface|Redirect
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = (int)$this->getRequest()->getParam('entity_id');

        if (!$id) {
            $this->messageManager->addErrorMessage(__('We can\'t find an item condition to delete.'));
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $itemCondition = $this->itemConditionRepository->get($id);
            $this->itemConditionRepository->delete($itemCondition);
            $this->messageManager->addSuccessMessage(__('You deleted the item condition.'));
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
