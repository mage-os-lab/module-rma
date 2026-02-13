<?php

declare(strict_types=1);

namespace MageOS\RMA\Controller\Adminhtml\Reason;

use Magento\Framework\App\Action\HttpPostActionInterface;
use MageOS\RMA\Api\ReasonRepositoryInterface;
use MageOS\RMA\Controller\Adminhtml\Reason as BaseController;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Exception;

class Delete extends BaseController implements HttpPostActionInterface
{
    /**
     * @param Context $context
     * @param ReasonRepositoryInterface $reasonRepository
     */
    public function __construct(
        Context $context,
        protected readonly ReasonRepositoryInterface $reasonRepository
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
            $this->messageManager->addErrorMessage(__('We can\'t find a reason to delete.'));
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $reason = $this->reasonRepository->get($id);
            $this->reasonRepository->delete($reason);
            $this->messageManager->addSuccessMessage(__('You deleted the reason.'));
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
