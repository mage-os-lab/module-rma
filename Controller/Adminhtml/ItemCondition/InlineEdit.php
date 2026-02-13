<?php

declare(strict_types=1);

namespace MageOS\RMA\Controller\Adminhtml\ItemCondition;

use MageOS\RMA\Api\ItemConditionRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\ResponseInterface;
use Exception;

class InlineEdit extends Action
{
    const string ADMIN_RESOURCE = 'MageOS_RMA::rma_item_condition';

    /**
     * @param Context $context
     * @param ItemConditionRepositoryInterface $itemConditionRepository
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        protected readonly ItemConditionRepositoryInterface $itemConditionRepository,
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
                foreach (array_keys($postItems) as $conditionId) {
                    try {
                        $itemCondition = $this->itemConditionRepository->get((int)$conditionId);
                        $itemData = $postItems[$conditionId];
                        unset($itemData['code']);
                        $itemCondition->setData(array_merge($itemCondition->getData(), $itemData));
                        $this->itemConditionRepository->save($itemCondition);
                    } catch (Exception $e) {
                        $messages[] = '[Item Condition ID: ' . $conditionId . '] ' . $e->getMessage();
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
