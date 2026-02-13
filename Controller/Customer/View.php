<?php

declare(strict_types=1);

namespace MageOS\RMA\Controller\Customer;

use MageOS\RMA\Api\RMARepositoryInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\View\Result\PageFactory;

class View implements HttpGetActionInterface
{
    /**
     * @param PageFactory $resultPageFactory
     * @param RedirectFactory $resultRedirectFactory
     * @param RMARepositoryInterface $rmaRepository
     * @param CustomerSession $customerSession
     * @param RequestInterface $request
     * @param MessageManagerInterface $messageManager
     */
    public function __construct(
        protected readonly PageFactory $resultPageFactory,
        protected readonly RedirectFactory $resultRedirectFactory,
        protected readonly RMARepositoryInterface $rmaRepository,
        protected readonly CustomerSession $customerSession,
        protected readonly RequestInterface $request,
        protected readonly MessageManagerInterface $messageManager
    ) {
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        if (!$this->customerSession->isLoggedIn()) {
            return $this->resultRedirectFactory->create()->setPath('customer/account/login');
        }

        $rmaId = (int)$this->request->getParam('id');

        try {
            $rma = $this->rmaRepository->get($rmaId);
        } catch (NoSuchEntityException) {
            $this->messageManager->addErrorMessage(__('This return request no longer exists.'));

            return $this->resultRedirectFactory->create()->setPath('rma/customer/history');
        }

        if ((int)$rma->getCustomerId() !== (int)$this->customerSession->getCustomerId()) {
            $this->messageManager->addErrorMessage(__('You are not authorized to view this return request.'));

            return $this->resultRedirectFactory->create()->setPath('rma/customer/history');
        }

        $this->request->setParam('rma_entity', $rma);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Return #%1', $rma->getIncrementId()));

        $navigationBlock = $resultPage->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('rma/customer/history');
        }

        return $resultPage;
    }
}
