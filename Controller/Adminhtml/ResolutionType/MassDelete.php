<?php

declare(strict_types=1);

namespace MageOS\RMA\Controller\Adminhtml\ResolutionType;

use MageOS\RMA\Controller\Adminhtml\ResolutionType as BaseController;
use MageOS\RMA\Model\ResourceModel\ResolutionType\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Exception;

class MassDelete extends BaseController implements HttpPostActionInterface
{
    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        protected readonly Filter $filter,
        protected readonly CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
    }

    /**
     * @throws LocalizedException
     */
    public function execute(): ResultInterface|ResponseInterface|Redirect
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $deleted = 0;

        foreach ($collection as $resolutionType) {
            try {
                $resolutionType->delete();
                $deleted++;
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        if ($deleted) {
            $this->messageManager->addSuccessMessage(__('A total of %1 resolution type(s) have been deleted.', $deleted));
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
