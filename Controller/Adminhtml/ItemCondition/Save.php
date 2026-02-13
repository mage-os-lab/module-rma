<?php

declare(strict_types=1);

namespace MageOS\RMA\Controller\Adminhtml\ItemCondition;

use Magento\Framework\App\Action\HttpPostActionInterface;
use MageOS\RMA\Api\ItemConditionRepositoryInterface;
use MageOS\RMA\Controller\Adminhtml\ItemCondition as BaseController;
use MageOS\RMA\Model\ItemConditionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Exception;

class Save extends BaseController implements HttpPostActionInterface
{
    /**
     * @param Context $context
     * @param ItemConditionRepositoryInterface $itemConditionRepository
     * @param ItemConditionFactory $itemConditionFactory
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        protected readonly ItemConditionRepositoryInterface $itemConditionRepository,
        protected readonly ItemConditionFactory $itemConditionFactory,
        protected readonly DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($context);
    }

    /**
     * @return ResultInterface|ResponseInterface|Redirect
     */
    public function execute(): ResultInterface|ResponseInterface|Redirect
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if (!$data) {
            return $resultRedirect->setPath('*/*/');
        }

        $id = (int)($data['entity_id'] ?? 0);

        if ($id) {
            try {
                $model = $this->itemConditionRepository->get($id);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage(__('This item condition no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
            unset($data['code']);
        } else {
            $model = $this->itemConditionFactory->create();
        }

        $model->setData(array_merge($model->getData(), $data));

        try {
            $this->itemConditionRepository->save($model);
            $this->messageManager->addSuccessMessage(__('You saved the item condition.'));
            $this->dataPersistor->clear('rma_item_condition');

            if ($this->getRequest()->getParam('back') === 'continue') {
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $model->getEntityId()]);
            }

            return $resultRedirect->setPath('*/*/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the item condition.'));
        }

        $this->dataPersistor->set('rma_item_condition', $data);

        return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
    }
}
