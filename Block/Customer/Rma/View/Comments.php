<?php

declare(strict_types=1);

namespace MageOS\RMA\Block\Customer\Rma\View;

use MageOS\RMA\Api\Data\RMAInterface;
use MageOS\RMA\Model\ResourceModel\Comment\CollectionFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Comments extends Template
{
    /**
     * @var string
     */
    protected $_template = 'MageOS_RMA::customer/rma/view/comments.phtml';

    /**
     * @param Context $context
     * @param CollectionFactory $commentCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        protected readonly CollectionFactory $commentCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @return RMAInterface|null
     */
    protected function getRma(): ?RMAInterface
    {
        return $this->getRequest()->getParam('rma_entity');
    }

    /**
     * @return int
     */
    public function getRmaId(): int
    {
        $rma = $this->getRma();
        return (int)$rma?->getEntityId();
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
        $collection->addFieldToFilter('is_visible_to_customer', 1);
        $collection->setOrder('created_at', 'ASC');

        $comments = [];
        foreach ($collection as $comment) {
            $comments[] = [
                'entity_id' => $comment->getEntityId(),
                'author_type' => $comment->getAuthorType(),
                'author_name' => $comment->getAuthorName(),
                'comment' => $comment->getComment(),
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
        return $this->getUrl('rma/customer_comment/save');
    }

    /**
     * @return string
     */
    public function getLoadListUrl(): string
    {
        return $this->getUrl('rma/customer_comment/loadList');
    }
}
