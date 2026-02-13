<?php

declare(strict_types=1);

namespace MageOS\RMA\Controller\Adminhtml\ResolutionType;

use Magento\Framework\App\Action\HttpPostActionInterface;
use MageOS\RMA\Api\ResolutionTypeRepositoryInterface;
use MageOS\RMA\Controller\Adminhtml\ResolutionType as BaseController;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Exception;

class Delete extends BaseController implements HttpPostActionInterface
{
    /**
     * @param Context $context
     * @param ResolutionTypeRepositoryInterface $resolutionTypeRepository
     */
    public function __construct(
        Context $context,
        protected readonly ResolutionTypeRepositoryInterface $resolutionTypeRepository
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
            $this->messageManager->addErrorMessage(__('We can\'t find a resolution type to delete.'));
            return $resultRedirect->setPath('*/*/');
        }

        try {
            $resolutionType = $this->resolutionTypeRepository->get($id);
            $this->resolutionTypeRepository->delete($resolutionType);
            $this->messageManager->addSuccessMessage(__('You deleted the resolution type.'));
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
