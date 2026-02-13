<?php

declare(strict_types=1);

namespace MageOS\RMA\Observer;

use MageOS\RMA\Api\Data\RMAInterface;
use MageOS\RMA\Api\Email\SenderInterface;
use MageOS\RMA\Api\StatusRepositoryInterface;
use MageOS\RMA\Helper\ModuleConfig;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Exception;

class SendStatusChangeEmail implements ObserverInterface
{
    /**
     * @param SenderInterface $sender
     * @param StatusRepositoryInterface $statusRepository
     * @param ModuleConfig $moduleConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        protected readonly SenderInterface $sender,
        protected readonly StatusRepositoryInterface $statusRepository,
        protected readonly ModuleConfig $moduleConfig,
        protected readonly LoggerInterface $logger
    ) {
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /** @var RMAInterface $rma */
        $rma = $observer->getData('rma');

        if (!$rma instanceof RMAInterface) {
            return;
        }

        $newStatusId = (int)$observer->getData('new_status_id');
        $statusLabel = $this->getStatusLabel($newStatusId, (int)$rma->getStoreId());

        try {
            $this->sender->sendCustomerStatusChangeEmail($rma, $statusLabel);
        } catch (Exception $e) {
            $this->logger->error('RMA: Failed to send status change email', [
                'rma_id' => $rma->getEntityId(),
                'new_status_id' => $newStatusId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param int $statusId
     * @param int $storeId
     * @return string
     */
    protected function getStatusLabel(int $statusId, int $storeId): string
    {
        try {
            $status = $this->statusRepository->get($statusId);
            return $status->getStoreLabel($storeId);
        } catch (Exception $e) {
            return '';
        }
    }
}
