<?php

declare(strict_types=1);

namespace MageOS\RMA\Service\Email;

use MageOS\RMA\Api\Data\RMAInterface;
use MageOS\RMA\Api\Email\SenderInterface;
use MageOS\RMA\Api\ItemConditionRepositoryInterface;
use MageOS\RMA\Api\ReasonRepositoryInterface;
use MageOS\RMA\Api\ResolutionTypeRepositoryInterface;
use MageOS\RMA\Helper\ModuleConfig;
use MageOS\RMA\Model\ResourceModel\Item\CollectionFactory as ItemCollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Framework\App\Area;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Exception;

class Sender implements SenderInterface
{
    /**
     * @param TransportBuilder $transportBuilder
     * @param StoreManagerInterface $storeManager
     * @param OrderRepositoryInterface $orderRepository
     * @param ReasonRepositoryInterface $reasonRepository
     * @param ResolutionTypeRepositoryInterface $resolutionTypeRepository
     * @param ModuleConfig $moduleConfig
     * @param LoggerInterface $logger
     * @param ItemCollectionFactory $itemCollectionFactory
     * @param OrderItemRepositoryInterface $orderItemRepository
     * @param ProductRepositoryInterface $productRepository
     * @param ImageHelper $imageHelper
     * @param ItemConditionRepositoryInterface $itemConditionRepository
     */
    public function __construct(
        protected readonly TransportBuilder $transportBuilder,
        protected readonly StoreManagerInterface $storeManager,
        protected readonly OrderRepositoryInterface $orderRepository,
        protected readonly ReasonRepositoryInterface $reasonRepository,
        protected readonly ResolutionTypeRepositoryInterface $resolutionTypeRepository,
        protected readonly ModuleConfig $moduleConfig,
        protected readonly LoggerInterface $logger,
        protected readonly ItemCollectionFactory $itemCollectionFactory,
        protected readonly OrderItemRepositoryInterface $orderItemRepository,
        protected readonly ProductRepositoryInterface $productRepository,
        protected readonly ImageHelper $imageHelper,
        protected readonly ItemConditionRepositoryInterface $itemConditionRepository
    ) {
    }

    /**
     * @param RMAInterface $rma
     * @return void
     */
    public function sendCustomerNewRmaEmail(RMAInterface $rma): void
    {
        $storeId = (int)$rma->getStoreId();

        if(!$this->moduleConfig->isEnabled($storeId)){
            return;
        }

        $templateId = $this->moduleConfig->getCustomerNewTemplate($storeId);

        $this->sendRmaEmail($rma, $templateId, $rma->getCustomerEmail(), $rma->getCustomerName(), [
            'rma_reason_label' => $this->getReasonLabel($rma),
            'rma_resolution_label' => $this->getResolutionLabel($rma),
            'rma_items' => $this->getRmaItemsData($rma),
        ]);
    }

    /**
     * @param RMAInterface $rma
     * @param string $statusLabel
     * @return void
     */
    public function sendCustomerStatusChangeEmail(RMAInterface $rma, string $statusLabel): void
    {
        $storeId = (int)$rma->getStoreId();
        $templateId = $this->moduleConfig->getCustomerStatusChangeTemplate($storeId);

        $this->sendRmaEmail($rma, $templateId, $rma->getCustomerEmail(), $rma->getCustomerName(), [
            'rma_status_label' => $statusLabel,
        ]);
    }

    /**
     * @param RMAInterface $rma
     * @return void
     */
    public function sendAdminNewRmaEmail(RMAInterface $rma): void
    {
        $storeId = (int)$rma->getStoreId();

        if(!$this->moduleConfig->isEnabled($storeId)){
            return;
        }

        $templateId = $this->moduleConfig->getAdminNewTemplate($storeId);
        $adminEmail = $this->moduleConfig->getAdminNotifyEmail($storeId);

        if ($adminEmail === '') {
            return;
        }

        $this->sendRmaEmail($rma, $templateId, $adminEmail, 'Admin', [
            'customer_email' => $rma->getCustomerEmail(),
            'rma_reason_label' => $this->getReasonLabel($rma),
            'rma_resolution_label' => $this->getResolutionLabel($rma),
        ]);
    }

    /**
     * @param RMAInterface $rma
     * @param string $templateId
     * @param string $recipientEmail
     * @param string $recipientName
     * @param array $extraVars
     * @return void
     */
    protected function sendRmaEmail(
        RMAInterface $rma,
        string $templateId,
        string $recipientEmail,
        string $recipientName,
        array $extraVars = []
    ): void {
        try {
            $templateVars = array_merge($this->getBaseTemplateVars($rma), $extraVars);

            $transport = $this->transportBuilder
                ->setTemplateIdentifier($templateId)
                ->setTemplateOptions([
                    'area' => Area::AREA_FRONTEND,
                    'store' => $storeId,
                ])
                ->setTemplateVars($templateVars)
                ->setFromByScope($this->moduleConfig->getEmailSenderIdentity($storeId), $storeId)
                ->addTo($recipientEmail, $recipientName)
                ->getTransport();

            $transport->sendMessage();
        } catch (Exception $e) {
            $this->logger->error('RMA: Failed to send email', [
                'template' => $templateId,
                'rma_id' => $rma->getEntityId(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param RMAInterface $rma
     * @return array
     * @throws NoSuchEntityException
     */
    protected function getBaseTemplateVars(RMAInterface $rma): array
    {
        $storeId = (int)$rma->getStoreId();

        return [
            'customer_name' => $rma->getCustomerName(),
            'rma' => $rma,
            'order' => $this->orderRepository->get($rma->getOrderId()),
            'store' => $this->storeManager->getStore($storeId),
        ];
    }

    /**
     * @param RMAInterface $rma
     * @return array
     */
    protected function getRmaItemsData(RMAInterface $rma): array
    {
        $collection = $this->itemCollectionFactory->create();
        $collection->addFieldToFilter('rma_id', $rma->getEntityId());

        $storeId = (int)$rma->getStoreId();
        $itemsData = [];

        foreach ($collection as $rmaItem) {
            $orderItemId = (int)$rmaItem->getData('order_item_id');

            try {
                $orderItem = $this->orderItemRepository->get($orderItemId);
                $name = (string)$orderItem->getName();
                $sku = (string)$orderItem->getSku();
                $thumbnailUrl = $this->getProductThumbnailUrl((int)$orderItem->getProductId(), $storeId);
            } catch (Exception $e) {
                $name = (string)__('Unknown Product');
                $sku = '';
                $thumbnailUrl = '';
            }

            $conditionId = $rmaItem->getData('condition_id');
            $conditionLabel = $this->getConditionLabel($conditionId ? (int)$conditionId : null, $storeId);

            $thumbnailHtml = $thumbnailUrl !== ''
                ? '<img src="' . $thumbnailUrl . '" alt="' . htmlspecialchars($name) . '" width="75" height="75" style="border:1px solid #e3e3e3;" />'
                : '';

            $itemsData[] = [
                'name' => $name,
                'sku' => $sku,
                'qty_requested' => (int)$rmaItem->getData('qty_requested'),
                'condition_label' => $conditionLabel,
                'thumbnail_html' => $thumbnailHtml,
            ];
        }

        return $itemsData;
    }

    /**
     * @param int $productId
     * @param int $storeId
     * @return string
     */
    protected function getProductThumbnailUrl(int $productId, int $storeId): string
    {
        try {
            $product = $this->productRepository->getById($productId, false, $storeId);
            return $this->imageHelper
                ->init($product, 'product_thumbnail_image')
                ->resize(75, 75)
                ->getUrl();
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * @param int|null $conditionId
     * @param int $storeId
     * @return string
     */
    protected function getConditionLabel(?int $conditionId, int $storeId): string
    {
        if ($conditionId === null) {
            return '';
        }

        try {
            $condition = $this->itemConditionRepository->get($conditionId);
            return $condition->getStoreLabel($storeId);
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * @param RMAInterface $rma
     * @return string
     */
    protected function getReasonLabel(RMAInterface $rma): string
    {
        try {
            $reason = $this->reasonRepository->get($rma->getReasonId());
            return $reason->getStoreLabel((int)$rma->getStoreId());
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * @param RMAInterface $rma
     * @return string
     */
    protected function getResolutionLabel(RMAInterface $rma): string
    {
        try {
            $resolutionType = $this->resolutionTypeRepository->get($rma->getResolutionTypeId());
            return $resolutionType->getStoreLabel((int)$rma->getStoreId());
        } catch (Exception $e) {
            return '';
        }
    }
}
