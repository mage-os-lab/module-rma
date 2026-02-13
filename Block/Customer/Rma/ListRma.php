<?php

declare(strict_types=1);

namespace MageOS\RMA\Block\Customer\Rma;

use MageOS\RMA\Model\ResourceModel\RMA\Collection;
use MageOS\RMA\Model\ResourceModel\RMA\CollectionFactory;
use MageOS\RMA\Service\LabelResolver;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Theme\Block\Html\Pager;

class ListRma extends Template
{
    protected ?Collection $rmaCollection = null;

    /**
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param CustomerSession $customerSession
     * @param LabelResolver $labelResolver
     * @param OrderRepositoryInterface $orderRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        protected readonly CollectionFactory $collectionFactory,
        protected readonly CustomerSession $customerSession,
        protected readonly LabelResolver $labelResolver,
        protected readonly OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return Collection|null
     */
    public function getRmaCollection(): ?Collection
    {
        $customerId = $this->customerSession->getCustomerId();

        if (!$customerId) {
            return null;
        }

        if ($this->rmaCollection === null) {
            $this->rmaCollection = $this->collectionFactory->create();
            $this->rmaCollection->addFieldToFilter('customer_id', $customerId);
            $this->rmaCollection->setOrder('created_at', 'desc');
        }

        return $this->rmaCollection;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout(): static
    {
        parent::_prepareLayout();

        $collection = $this->getRmaCollection();
        $pager = $this->getChildBlock('rma.customer.history.pager');

        if ($collection && $pager instanceof Pager) {
            $pager->setCollection($collection);
            $collection->load();
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml(): string
    {
        return $this->getChildHtml('rma.customer.history.pager');
    }

    /**
     * @param object $rma
     * @return string
     */
    public function getViewUrl(object $rma): string
    {
        return $this->getUrl('rma/customer/view', ['id' => $rma->getEntityId()]);
    }

    /**
     * @return string
     */
    public function getCreateUrl(): string
    {
        return $this->getUrl('rma/customer/create');
    }

    /**
     * @param int $statusId
     * @return string
     */
    public function getStatusLabel(int $statusId): string
    {
        return $this->labelResolver->resolve(LabelResolver::TYPE_STATUS, $statusId);
    }

    /**
     * @param int $orderId
     * @return string
     */
    public function getOrderIncrementId(int $orderId): string
    {
        try {
            return $this->orderRepository->get($orderId)->getIncrementId();
        } catch (NoSuchEntityException) {
            return (string)$orderId;
        }
    }
}
