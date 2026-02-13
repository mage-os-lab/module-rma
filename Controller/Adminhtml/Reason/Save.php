<?php

declare(strict_types=1);

namespace MageOS\RMA\Controller\Adminhtml\Reason;

use Magento\Framework\App\Action\HttpPostActionInterface;
use MageOS\RMA\Api\ReasonRepositoryInterface;
use MageOS\RMA\Controller\Adminhtml\Reason as BaseController;
use MageOS\RMA\Model\ReasonFactory;
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
     * @param ReasonRepositoryInterface $reasonRepository
     * @param ReasonFactory $reasonFactory
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        protected readonly ReasonRepositoryInterface $reasonRepository,
        protected readonly ReasonFactory $reasonFactory,
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
                $model = $this->reasonRepository->get($id);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage(__('This reason no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
            unset($data['code']);
        } else {
            $model = $this->reasonFactory->create();
        }

        $model->setData(array_merge($model->getData(), $data));

        try {
            $this->reasonRepository->save($model);
            $this->messageManager->addSuccessMessage(__('You saved the reason.'));
            $this->dataPersistor->clear('rma_reason');

            if ($this->getRequest()->getParam('back') === 'continue') {
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $model->getEntityId()]);
            }

            return $resultRedirect->setPath('*/*/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the reason.'));
        }

        $this->dataPersistor->set('rma_reason', $data);

        return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
    }
}
