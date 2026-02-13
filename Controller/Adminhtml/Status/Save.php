<?php

declare(strict_types=1);

namespace MageOS\RMA\Controller\Adminhtml\Status;

use Magento\Framework\App\Action\HttpPostActionInterface;
use MageOS\RMA\Api\StatusRepositoryInterface;
use MageOS\RMA\Controller\Adminhtml\Status as BaseController;
use MageOS\RMA\Model\StatusFactory;
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
     * @param StatusRepositoryInterface $statusRepository
     * @param StatusFactory $statusFactory
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        protected readonly StatusRepositoryInterface $statusRepository,
        protected readonly StatusFactory $statusFactory,
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
                $model = $this->statusRepository->get($id);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage(__('This status no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
            unset($data['code']);
        } else {
            $model = $this->statusFactory->create();
        }

        $model->setData(array_merge($model->getData(), $data));

        try {
            $this->statusRepository->save($model);
            $this->messageManager->addSuccessMessage(__('You saved the status.'));
            $this->dataPersistor->clear('rma_status');

            if ($this->getRequest()->getParam('back') === 'continue') {
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $model->getEntityId()]);
            }

            return $resultRedirect->setPath('*/*/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the status.'));
        }

        $this->dataPersistor->set('rma_status', $data);

        return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
    }
}
