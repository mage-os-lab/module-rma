<?php

declare(strict_types=1);

namespace MageOS\RMA\Controller\Adminhtml\Reason;

use MageOS\RMA\Api\ReasonRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\ResponseInterface;
use Exception;

class InlineEdit extends Action
{
    const string ADMIN_RESOURCE = 'MageOS_RMA::rma_reason';

    /**
     * @param Context $context
     * @param ReasonRepositoryInterface $reasonRepository
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        protected readonly ReasonRepositoryInterface $reasonRepository,
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
                foreach (array_keys($postItems) as $reasonId) {
                    try {
                        $reason = $this->reasonRepository->get((int)$reasonId);
                        $itemData = $postItems[$reasonId];
                        unset($itemData['code']);
                        $reason->setData(array_merge($reason->getData(), $itemData));
                        $this->reasonRepository->save($reason);
                    } catch (Exception $e) {
                        $messages[] = '[Reason ID: ' . $reasonId . '] ' . $e->getMessage();
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
