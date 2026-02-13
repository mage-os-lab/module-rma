<?php

declare(strict_types=1);

namespace MageOS\RMA\Controller\Adminhtml\Status;

use Magento\Framework\App\Action\HttpPostActionInterface;
use MageOS\RMA\Api\StatusRepositoryInterface;
use MageOS\RMA\Controller\Adminhtml\Status as BaseController;
use MageOS\RMA\Model\RMA\StatusCodes;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Exception;

class Delete extends BaseController implements HttpPostActionInterface
{
    /**
     * @param Context $context
     * @param StatusRepositoryInterface $statusRepository
     */
    public function __construct(
        Context $context,
        protected readonly StatusRepositoryInterface $statusRepository
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
            $this->messageManager->addErrorMessage(__('We can\'t find a status to delete.'));
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $status = $this->statusRepository->get($id);

            if (StatusCodes::isProtected($status->getCode())) {
                $this->messageManager->addErrorMessage(
                    __('The status "%1" is used by the system and cannot be deleted.', $status->getLabel())
                );
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
            }

            $this->statusRepository->delete($status);
            $this->messageManager->addSuccessMessage(__('You deleted the status.'));
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
