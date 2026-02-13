<?php

declare(strict_types=1);

namespace MageOS\RMA\Controller\Adminhtml\ResolutionType;

use MageOS\RMA\Api\ResolutionTypeRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\ResponseInterface;
use Exception;

class InlineEdit extends Action
{
    const string ADMIN_RESOURCE = 'MageOS_RMA::rma_resolution_type';

    /**
     * @param Context $context
     * @param ResolutionTypeRepositoryInterface $resolutionTypeRepository
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        protected readonly ResolutionTypeRepositoryInterface $resolutionTypeRepository,
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
                foreach (array_keys($postItems) as $resolutionTypeId) {
                    try {
                        $resolutionType = $this->resolutionTypeRepository->get((int)$resolutionTypeId);
                        $itemData = $postItems[$resolutionTypeId];
                        unset($itemData['code']);
                        $resolutionType->setData(array_merge($resolutionType->getData(), $itemData));
                        $this->resolutionTypeRepository->save($resolutionType);
                    } catch (Exception $e) {
                        $messages[] = '[Resolution Type ID: ' . $resolutionTypeId . '] ' . $e->getMessage();
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
