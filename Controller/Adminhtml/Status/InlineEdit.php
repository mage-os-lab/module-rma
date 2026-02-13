<?php

declare(strict_types=1);

namespace MageOS\RMA\Controller\Adminhtml\Status;

use MageOS\RMA\Api\StatusRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\ResponseInterface;
use Exception;

class InlineEdit extends Action
{
    const string ADMIN_RESOURCE = 'MageOS_RMA::rma_status';

    /**
     * @param Context $context
     * @param StatusRepositoryInterface $statusRepository
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        protected readonly StatusRepositoryInterface $statusRepository,
        protected readonly JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
    }

    /**
     * @return Json|ResultInterface|ResponseInterface
     */
    public function execute(): Json|ResultInterface|ResponseInterface
    {
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];

        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);

            if (empty($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                foreach (array_keys($postItems) as $statusId) {
                    try {
                        $status = $this->statusRepository->get((int)$statusId);
                        $itemData = $postItems[$statusId];
                        unset($itemData['code']);
                        $status->setData(array_merge($status->getData(), $itemData));
                        $this->statusRepository->save($status);
                    } catch (Exception $e) {
                        $messages[] = '[Status ID: ' . $statusId . '] ' . $e->getMessage();
                        $error = true;
                    }
                }
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error,
        ]);
    }
}
