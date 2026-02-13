<?php

declare(strict_types=1);

namespace MageOS\RMA\Block\Adminhtml\Rma\Edit;

use MageOS\RMA\Api\Data\RMAInterface;
use MageOS\RMA\Api\RMARepositoryInterface;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\ScopeInterface;

class Info extends Template
{
    /**
     * @var string
     */
    protected $_template = 'MageOS_RMA::rma/edit/info.phtml';

    /**
     * @param Context $context
     * @param RMARepositoryInterface $rmaRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        protected readonly RMARepositoryInterface $rmaRepository,
        protected readonly OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return RMAInterface|null
     */
    public function getRma(): ?RMAInterface
    {
        $id = (int)$this->getRequest()->getParam('entity_id');

        if (!$id) {
            return null;
        }

        try {
            return $this->rmaRepository->get($id);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * @param int $orderId
     * @return string
     */
    public function getOrderIncrementId(int $orderId): string
    {
        try {
            $order = $this->orderRepository->get($orderId);
            return (string)$order->getIncrementId();
        } catch (NoSuchEntityException $e) {
            return '';
        }
    }

    /**
     * @param int $orderId
     * @return string
     */
    public function getOrderUrl(int $orderId): string
    {
        return $this->getUrl('sales/order/view', ['order_id' => $orderId]);
    }

    /**
     * @param int $customerId
     * @return string
     */
    public function getCustomerUrl(int $customerId): string
    {
        return $this->getUrl('customer/index/edit', ['id' => $customerId]);
    }

    /**
     * Get store name as "Website / Store / Store View" (same format as Magento order admin).
     *
     * @param int $storeId
     * @return string
     */
    public function getStoreName(int $storeId): string
    {
        try {
            $store = $this->_storeManager->getStore($storeId);
            return implode('<br>', [
                $store->getWebsite()->getName(),
                $store->getGroup()->getName(),
                $store->getName(),
            ]);
        } catch (NoSuchEntityException $e) {
            return (string)__('Unknown (ID: %1)', $storeId);
        }
    }

    /**
     * Format created_at date using admin locale and store timezone (same as order admin page).
     *
     * @param string $createdAt
     * @param int $storeId
     * @return string
     */
    public function getFormattedDate(string $createdAt, int $storeId = 0): string
    {
        try {
            $storeTimezone = $this->_localeDate->getConfigTimezone(
                ScopeInterface::SCOPE_STORE,
                $storeId
            );

            return $this->formatDate(
                $createdAt,
                \IntlDateFormatter::MEDIUM,
                true,
                $storeTimezone
            );
        } catch (\Exception $e) {
            return $createdAt;
        }
    }
}
