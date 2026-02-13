<?php

declare(strict_types=1);

namespace MageOS\RMA\Observer;

use MageOS\RMA\Api\Data\RMAInterface;
use MageOS\RMA\Api\RMARepositoryInterface;
use MageOS\RMA\Helper\ModuleConfig;
use MageOS\RMA\Model\RMA\StatusCodes;
use MageOS\RMA\Model\RMA\StatusResolver;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Exception;

class AutoApproveRma implements ObserverInterface
{
    /**
     * @param RMARepositoryInterface $rmaRepository
     * @param StatusResolver $statusResolver
     * @param ModuleConfig $moduleConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        protected readonly RMARepositoryInterface $rmaRepository,
        protected readonly StatusResolver $statusResolver,
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

        if (!$this->moduleConfig->isAutoApproveEnabled((int)$rma->getStoreId())) {
            return;
        }

        $approvedStatusId = $this->statusResolver->getIdByCode(StatusCodes::APPROVED);

        if ($approvedStatusId === null) {
            $this->logger->error('RMA: Cannot auto-approve, "approved" status not found', [
                'rma_id' => $rma->getEntityId(),
            ]);

            return;
        }

        try {
            $rma->setStatusId($approvedStatusId);
            $this->rmaRepository->save($rma);
        } catch (Exception $e) {
            $this->logger->error('RMA: Failed to auto-approve', [
                'rma_id' => $rma->getEntityId(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
