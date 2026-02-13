<?php

declare(strict_types=1);

namespace MageOS\RMA\Controller\Guest;

use MageOS\RMA\Service\GuestOrderService;
use MageOS\RMA\Service\OrderEligibility;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;

class OrderItems implements HttpGetActionInterface
{
    /**
     * @param JsonFactory $resultJsonFactory
     * @param GuestOrderService $guestOrderService
     * @param OrderEligibility $orderEligibility
     * @param RequestInterface $request
     */
    public function __construct(
        protected readonly JsonFactory $resultJsonFactory,
        protected readonly GuestOrderService $guestOrderService,
        protected readonly OrderEligibility $orderEligibility,
        protected readonly RequestInterface $request
    ) {
    }

    /**
     * @return ResultInterface
     * @throws InputException
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     */
    public function execute(): ResultInterface
    {
        $orderResult = $this->guestOrderService->loadValidOrder($this->request);

        if ($orderResult instanceof ResultInterface) {
            return $this->resultJsonFactory->create()->setData([
                'items' => [],
                'error' => (string)__('Invalid guest order session.'),
            ]);
        }

        /** @var OrderInterface $order */
        $order = $orderResult;

        $items = $this->orderEligibility->getEligibleItems($order);

        return $this->resultJsonFactory->create()->setData([
            'items' => $items,
        ]);
    }
}
