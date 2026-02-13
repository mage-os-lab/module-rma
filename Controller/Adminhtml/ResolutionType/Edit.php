<?php

declare(strict_types=1);

namespace MageOS\RMA\Controller\Adminhtml\ResolutionType;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use MageOS\RMA\Api\ResolutionTypeRepositoryInterface;
use MageOS\RMA\Controller\Adminhtml\ResolutionType as BaseController;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;

class Edit extends BaseController implements HttpGetActionInterface
{
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ResolutionTypeRepositoryInterface $resolutionTypeRepository
     */
    public function __construct(
        Context $context,
        protected readonly PageFactory $resultPageFactory,
        protected readonly ResolutionTypeRepositoryInterface $resolutionTypeRepository
    ) {
        parent::__construct($context);
    }

    /**
     * @return Page|ResultInterface|ResponseInterface|Redirect
     */
    public function execute(): Page|ResultInterface|ResponseInterface|Redirect
    {
        $id = (int)$this->getRequest()->getParam('entity_id');

        if ($id) {
            try {
                $resolutionType = $this->resolutionTypeRepository->get($id);
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('This resolution type no longer exists.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/');
            }
        }

        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage);

        $resultPage->getConfig()->getTitle()->prepend(
            $id ? __('Edit Resolution Type: %1', $resolutionType->getLabel()) : __('New Resolution Type')
        );

        return $resultPage;
    }
}
