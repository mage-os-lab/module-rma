<?php

declare(strict_types=1);

namespace MageOS\RMA\Controller\Customer;

use MageOS\RMA\Service\OrderEligibility;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderItems implements HttpGetActionInterface
{
    /**
     * @param JsonFactory $resultJsonFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param CustomerSession $customerSession
     * @param OrderEligibility $orderEligibility
     * @param RequestInterface $request
     */
    public function __construct(
        protected readonly JsonFactory $resultJsonFactory,
        protected readonly OrderRepositoryInterface $orderRepository,
        protected readonly CustomerSession $customerSession,
        protected readonly OrderEligibility $orderEligibility,
        protected readonly RequestInterface $request
    ) {
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        if (!$this->customerSession->isLoggedIn()) {
            return $this->resultJsonFactory->create()->setData([
                'items' => [],
                'error' => (string)__('Not authorized.'),
            ]);
        }

        $orderId = (int)$this->request->getParam('order_id');
        $customerId = (int)$this->customerSession->getCustomerId();

        if (!$orderId) {
            return $this->resultJsonFactory->create()->setData([
                'items' => [],
                'error' => (string)__('No order ID provided.'),
            ]);
        }

        try {
            $order = $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException) {
            return $this->resultJsonFactory->create()->setData([
                'items' => [],
                'error' => (string)__('Order not found.'),
            ]);
        }

        // Verify order belongs to current customer
        if ((int)$order->getCustomerId() !== $customerId) {
            return $this->resultJsonFactory->create()->setData([
                'items' => [],
                'error' => (string)__('You are not authorized to access this order.'),
            ]);
        }

        $items = $this->orderEligibility->getEligibleItems($order);

        return $this->resultJsonFactory->create()->setData([
            'items' => $items,
        ]);
    }
}
