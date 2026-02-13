<?php

declare(strict_types=1);

namespace MageOS\RMA\Controller\Customer;

use MageOS\RMA\Service\OrderEligibility;
use MageOS\RMA\Service\RmaSubmitService;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Exception;

class Save implements HttpPostActionInterface
{
    /**
     * @param RedirectFactory $resultRedirectFactory
     * @param RequestInterface $request
     * @param MessageManagerInterface $messageManager
     * @param OrderRepositoryInterface $orderRepository
     * @param CustomerSession $customerSession
     * @param OrderEligibility $orderEligibility
     * @param RmaSubmitService $rmaSubmitService
     */
    public function __construct(
        protected readonly RedirectFactory $resultRedirectFactory,
        protected readonly RequestInterface $request,
        protected readonly MessageManagerInterface $messageManager,
        protected readonly OrderRepositoryInterface $orderRepository,
        protected readonly CustomerSession $customerSession,
        protected readonly OrderEligibility $orderEligibility,
        protected readonly RmaSubmitService $rmaSubmitService
    ) {
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if (!$this->customerSession->isLoggedIn()) {
            return $resultRedirect->setPath('customer/account/login');
        }

        $data = $this->request->getPostValue();

        if (!$data) {
            return $resultRedirect->setPath('rma/customer/history');
        }

        $orderId = (int)($data['order_id'] ?? 0);
        $customerId = (int)$this->customerSession->getCustomerId();

        if (!$orderId) {
            $this->messageManager->addErrorMessage(__('Please select an order.'));
            return $resultRedirect->setPath('rma/customer/create');
        }

        // Load and verify order
        try {
            $order = $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException) {
            $this->messageManager->addErrorMessage(__('The selected order does not exist.'));
            return $resultRedirect->setPath('rma/customer/create');
        }

        if ((int)$order->getCustomerId() !== $customerId) {
            $this->messageManager->addErrorMessage(__('You are not authorized to create a return for this order.'));
            return $resultRedirect->setPath('rma/customer/create');
        }

        // Verify eligibility
        if (!$this->orderEligibility->isOrderEligible($order)) {
            $this->messageManager->addErrorMessage(__('This order is not eligible for a return.'));
            return $resultRedirect->setPath('rma/customer/create');
        }

        // Validate items
        $itemsData = $data['items'] ?? [];
        $selectedItems = $this->rmaSubmitService->getSelectedItems($itemsData);

        if (empty($selectedItems)) {
            $this->messageManager->addErrorMessage(__('Please select at least one item to return.'));
            return $resultRedirect->setPath('rma/customer/create', ['order_id' => $orderId]);
        }

        try {
            $rma = $this->rmaSubmitService->createRma(
                $order,
                $customerId,
                (string)$order->getCustomerEmail(),
                (string)($order->getCustomerName() ?: __('Guest')),
                (int)($data['reason_id'] ?? 0),
                (int)($data['resolution_type_id'] ?? 0),
                $selectedItems
            );

            $this->messageManager->addSuccessMessage(__('Your return request has been submitted successfully.'));
            return $resultRedirect->setPath('rma/customer/view', ['id' => $rma->getEntityId()]);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while submitting your return request.'));
        }

        return $resultRedirect->setPath('rma/customer/create', ['order_id' => $orderId]);
    }
}
