<?php

declare(strict_types=1);

namespace MageOS\RMA\Controller\Adminhtml\Comment;

use MageOS\RMA\Api\CommentRepositoryInterface;
use MageOS\RMA\Api\Data\CommentInterface;
use MageOS\RMA\Api\Data\CommentInterfaceFactory;
use MageOS\RMA\Api\RMARepositoryInterface;
use MageOS\RMA\Controller\Adminhtml\Rma as BaseController;
use MageOS\RMA\Service\CommentFormatter;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session as AuthSession;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Exception;

class Save extends BaseController implements HttpPostActionInterface
{
    /**
     * @param Context $context
     * @param CommentInterfaceFactory $commentFactory
     * @param CommentRepositoryInterface $commentRepository
     * @param RMARepositoryInterface $rmaRepository
     * @param AuthSession $authSession
     * @param JsonFactory $jsonFactory
     * @param CommentFormatter $commentFormatter
     */
    public function __construct(
        Context $context,
        protected readonly CommentInterfaceFactory $commentFactory,
        protected readonly CommentRepositoryInterface $commentRepository,
        protected readonly RMARepositoryInterface $rmaRepository,
        protected readonly AuthSession $authSession,
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

        $validationError = $this->validateRequest();
        if ($validationError) {
            return $result->setData($validationError);
        }

        $rmaId = (int)$this->getRequest()->getParam('rma_id');
        $commentText = trim((string)$this->getRequest()->getParam('comment', ''));
        $isVisible = (bool)$this->getRequest()->getParam('is_visible_to_customer', true);

        try {
            $comment = $this->createComment($rmaId, $commentText, $isVisible);
        } catch (Exception) {
            return $result->setData(['success' => false, 'message' => (string)__('Could not save comment.')]);
        }

        return $result->setData([
            'success' => true,
            'comment' => $this->commentFormatter->toArray($comment, true),
        ]);
    }

    /**
     * @return array|null
     */
    protected function validateRequest(): ?array
    {
        $rmaId = (int)$this->getRequest()->getParam('rma_id');
        $commentText = trim((string)$this->getRequest()->getParam('comment', ''));

        if (!$rmaId || $commentText === '') {
            return ['success' => false, 'message' => (string)__('Invalid request.')];
        }

        try {
            $this->rmaRepository->get($rmaId);
        } catch (NoSuchEntityException) {
            return ['success' => false, 'message' => (string)__('RMA not found.')];
        }

        return null;
    }

    /**
     * @param int $rmaId
     * @param string $commentText
     * @param bool $isVisible
     * @return CommentInterface
     * @throws Exception
     */
    protected function createComment(int $rmaId, string $commentText, bool $isVisible): CommentInterface
    {
        $adminUser = $this->authSession->getUser();
        $authorName = $adminUser ? $adminUser->getName() : (string)__('Admin');

        $comment = $this->commentFactory->create();
        $comment->setRmaId($rmaId);
        $comment->setAuthorType(CommentInterface::AUTHOR_TYPE_ADMIN);
        $comment->setAuthorName($authorName);
        $comment->setComment($commentText);
        $comment->setIsVisibleToCustomer($isVisible);
        $this->commentRepository->save($comment);

        return $comment;
    }
}
