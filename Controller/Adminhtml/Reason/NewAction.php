<?php

declare(strict_types=1);

namespace MageOS\RMA\Controller\Adminhtml\Reason;

use Magento\Framework\App\Action\HttpGetActionInterface;
use MageOS\RMA\Controller\Adminhtml\Reason as BaseController;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

class NewAction extends BaseController implements HttpGetActionInterface
{
    /**
     * @param Context $context
     * @param ForwardFactory $resultForwardFactory
     */
    public function __construct(
        Context $context,
        protected readonly ForwardFactory $resultForwardFactory
    ) {
        parent::__construct($context);
    }

    /**
     * @return ResultInterface|ResponseInterface
     */
    public function execute(): ResultInterface|ResponseInterface
    {
        $resultForward = $this->resultForwardFactory->create();

        return $resultForward->forward('edit');
    }
}
