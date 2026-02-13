<?php

declare(strict_types=1);

namespace MageOS\RMA\Controller\Adminhtml\Comment;

use MageOS\RMA\Controller\Adminhtml\Rma as BaseController;
use MageOS\RMA\Model\ResourceModel\Comment\CollectionFactory;
use MageOS\RMA\Service\CommentFormatter;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

class LoadList extends BaseController implements HttpGetActionInterface
{
    /**
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param JsonFactory $jsonFactory
     * @param CommentFormatter $commentFormatter
     */
    public function __construct(
        Context $context,
        protected readonly CollectionFactory $collectionFactory,
        protected readonly JsonFactory $jsonFactory,
        protected readonly CommentFormatter $commentFormatter
    ) {
        parent::__construct($context);
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $result = $this->jsonFactory->create();
        $rmaId = (int)$this->getRequest()->getParam('rma_id');
        $afterId = (int)$this->getRequest()->getParam('after_id', 0);

        if (!$rmaId) {
            return $result->setData(['success' => false, 'comments' => []]);
        }

        $comments = $this->loadComments($rmaId, $afterId);

        return $result->setData(['success' => true, 'comments' => $comments]);
    }

    /**
     * @param int $rmaId
     * @param int $afterId
     * @return array
     */
    protected function loadComments(int $rmaId, int $afterId): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('rma_id', $rmaId);

        if ($afterId > 0) {
            $collection->addFieldToFilter('entity_id', ['gt' => $afterId]);
        }

        $collection->setOrder('created_at', 'ASC');

        $comments = [];
        foreach ($collection as $comment) {
            $comments[] = $this->commentFormatter->toArray($comment, true);
        }

        return $comments;
    }
}
