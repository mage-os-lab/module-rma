<?php

declare(strict_types=1);

namespace MageOS\RMA\Block\Guest\Rma;

use MageOS\RMA\Model\Config\Source\ItemCondition as ItemConditionSource;
use MageOS\RMA\Model\Config\Source\Reason as ReasonSource;
use MageOS\RMA\Model\Config\Source\ResolutionType as ResolutionTypeSource;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Create extends Template
{
    /**
     * @param Context $context
     * @param RequestInterface $request
     * @param ReasonSource $reasonSource
     * @param ResolutionTypeSource $resolutionTypeSource
     * @param ItemConditionSource $itemConditionSource
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        Context $context,
        protected readonly RequestInterface $request,
        protected readonly ReasonSource $reasonSource,
        protected readonly ResolutionTypeSource $resolutionTypeSource,
        protected readonly ItemConditionSource $itemConditionSource,
        protected readonly Json $json,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return OrderInterface|null
     */
    public function getOrder(): ?OrderInterface
    {
        $order = $this->request->getParam('current_order');
        return $order instanceof OrderInterface ? $order : null;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getReasons(): array
    {
        return $this->reasonSource->toOptionArray();
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getResolutionTypes(): array
    {
        return $this->resolutionTypeSource->toOptionArray();
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getConditions(): array
    {
        return $this->itemConditionSource->toOptionArray();
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getConfigJson(): string
    {
        $order = $this->getOrder();

        return $this->json->serialize([
            'itemsAjaxUrl' => $this->getItemsAjaxUrl(),
            'conditions' => $this->getConditions(),
            'preloadOrderId' => $order ? (int)$order->getEntityId() : null,
        ]);
    }

    /**
     * @return string
     */
    public function getItemsAjaxUrl(): string
    {
        return $this->getUrl('rma/guest/orderItems');
    }

    /**
     * @return string
     */
    public function getPostUrl(): string
    {
        return $this->getUrl('rma/guest/save');
    }
}
