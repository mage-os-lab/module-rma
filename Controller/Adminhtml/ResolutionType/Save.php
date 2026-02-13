<?php

declare(strict_types=1);

namespace MageOS\RMA\Controller\Adminhtml\ResolutionType;

use Magento\Framework\App\Action\HttpPostActionInterface;
use MageOS\RMA\Api\ResolutionTypeRepositoryInterface;
use MageOS\RMA\Controller\Adminhtml\ResolutionType as BaseController;
use MageOS\RMA\Model\ResolutionTypeFactory;
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
     * @param ResolutionTypeRepositoryInterface $resolutionTypeRepository
     * @param ResolutionTypeFactory $resolutionTypeFactory
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        protected readonly ResolutionTypeRepositoryInterface $resolutionTypeRepository,
        protected readonly ResolutionTypeFactory $resolutionTypeFactory,
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
                $model = $this->resolutionTypeRepository->get($id);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage(__('This resolution type no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
            unset($data['code']);
        } else {
            $model = $this->resolutionTypeFactory->create();
        }

        $model->setData(array_merge($model->getData(), $data));

        try {
            $this->resolutionTypeRepository->save($model);
            $this->messageManager->addSuccessMessage(__('You saved the resolution type.'));
            $this->dataPersistor->clear('rma_resolution_type');

            if ($this->getRequest()->getParam('back') === 'continue') {
                return $resultRedirect->setPath('*/*/edit', ['entity_id' => $model->getEntityId()]);
            }

            return $resultRedirect->setPath('*/*/');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the resolution type.'));
        }

        $this->dataPersistor->set('rma_resolution_type', $data);

        return $resultRedirect->setPath('*/*/edit', ['entity_id' => $id]);
    }
}
