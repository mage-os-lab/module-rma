<?php

declare(strict_types=1);

namespace MageOS\RMA\Block\Adminhtml\Rma\Edit;

use MageOS\RMA\Api\RMARepositoryInterface;
use MageOS\RMA\Model\ResourceModel\Comment\CollectionFactory;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\NoSuchEntityException;

class Comments extends Template
{
    /**
     * @var string
     */
    protected $_template = 'MageOS_RMA::rma/edit/comments.phtml';

    /**
     * @param Context $context
     * @param RMARepositoryInterface $rmaRepository
     * @param CollectionFactory $commentCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        protected readonly RMARepositoryInterface $rmaRepository,
        protected readonly CollectionFactory $commentCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return int
     */
    public function getRmaId(): int
    {
        return (int)$this->getRequest()->getParam('entity_id');
    }

    /**
     * @return bool
     */
    public function isEditMode(): bool
    {
        return $this->getRmaId() > 0;
    }

    /**
     * @return array
     */
    public function getComments(): array
    {
        $rmaId = $this->getRmaId();
        if (!$rmaId) {
            return [];
        }

        $collection = $this->commentCollectionFactory->create();
        $collection->addFieldToFilter('rma_id', $rmaId);
        $collection->setOrder('created_at', 'ASC');

        $comments = [];
        foreach ($collection as $comment) {
            $comments[] = [
                'entity_id' => $comment->getEntityId(),
                'author_type' => $comment->getAuthorType(),
                'author_name' => $comment->getAuthorName(),
                'comment' => $comment->getComment(),
                'is_visible_to_customer' => (bool)$comment->getIsVisibleToCustomer(),
                'created_at' => $comment->getCreatedAt(),
            ];
        }

        return $comments;
    }

    /**
     * @return string
     */
    public function getSaveUrl(): string
    {
        return $this->getUrl('rma/comment/save');
    }

    /**
     * @return string
     */
    public function getLoadListUrl(): string
    {
        return $this->getUrl('rma/comment/loadList');
    }
}
