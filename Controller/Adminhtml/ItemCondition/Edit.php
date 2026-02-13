<?php

declare(strict_types=1);

namespace MageOS\RMA\Controller\Adminhtml\ItemCondition;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use MageOS\RMA\Api\ItemConditionRepositoryInterface;
use MageOS\RMA\Controller\Adminhtml\ItemCondition as BaseController;
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
     * @param ItemConditionRepositoryInterface $itemConditionRepository
     */
    public function __construct(
        Context $context,
        protected readonly PageFactory $resultPageFactory,
        protected readonly ItemConditionRepositoryInterface $itemConditionRepository
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
                $itemCondition = $this->itemConditionRepository->get($id);
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('This item condition no longer exists.'));
                return $this->resultRedirectFactory->create()->setPath('*/*/');
            }
        }

        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage);

        $resultPage->getConfig()->getTitle()->prepend(
            $id ? __('Edit Item Condition: %1', $itemCondition->getLabel()) : __('New Item Condition')
        );

        return $resultPage;
    }
}
