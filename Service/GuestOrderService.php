<?php

declare(strict_types=1);

namespace MageOS\RMA\Service;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Helper\Guest as GuestHelper;

class GuestOrderService
{
    /**
     * @param GuestHelper $guestHelper
     * @param Registry $registry
     */
    public function __construct(
        protected readonly GuestHelper $guestHelper,
        protected readonly Registry $registry
    ) {
    }

    /**
     * @param RequestInterface $request
     * @return OrderInterface|ResultInterface
     * @throws CookieSizeLimitReachedException
     * @throws FailureToSendException
     * @throws InputException
     */
    public function loadValidOrder(RequestInterface $request): OrderInterface|ResultInterface
    {
        $result = $this->guestHelper->loadValidOrder($request);

        if ($result instanceof ResultInterface) {
            return $result;
        }

        // GuestHelper registers the order in Registry on success
        return $this->registry->registry('current_order');
    }
}
