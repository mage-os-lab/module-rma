<?php

declare(strict_types=1);

namespace MageOS\RMA\Controller\Customer\Comment;

use MageOS\RMA\Api\CommentRepositoryInterface;
use MageOS\RMA\Api\Data\CommentInterface;
use MageOS\RMA\Api\Data\CommentInterfaceFactory;
use MageOS\RMA\Api\RMARepositoryInterface;
use MageOS\RMA\Service\CommentFormatter;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Exception;

class Save implements HttpPostActionInterface
{
    /**
     * @param RequestInterface $request
     * @param JsonFactory $jsonFactory
     * @param CommentInterfaceFactory $commentFactory
     * @param CommentRepositoryInterface $commentRepository
     * @param RMARepositoryInterface $rmaRepository
     * @param CustomerSession $customerSession
     * @param CommentFormatter $commentFormatter
     */
    public function __construct(
        protected readonly RequestInterface $request,
        protected readonly JsonFactory $jsonFactory,
        protected readonly CommentInterfaceFactory $commentFactory,
        protected readonly CommentRepositoryInterface $commentRepository,
        protected readonly RMARepositoryInterface $rmaRepository,
        protected readonly CustomerSession $customerSession,
        protected readonly CommentFormatter $commentFormatter
    ) {
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $result = $this->jsonFactory->create();

        if (!$this->customerSession->isLoggedIn()) {
            return $result->setData(['success' => false, 'message' => (string)__('Not authorized.')]);
        }

        $validationError = $this->validateRequest();
        if ($validationError) {
            return $result->setData($validationError);
        }

        $rmaId = (int)$this->request->getParam('rma_id');
        $commentText = trim((string)$this->request->getParam('comment', ''));

        $authError = $this->validateOwnership($rmaId);
        if ($authError) {
            return $result->setData($authError);
        }

        try {
            $comment = $this->createComment($rmaId, $commentText);
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
        $rmaId = (int)$this->request->getParam('rma_id');
        $commentText = trim((string)$this->request->getParam('comment', ''));

        if (!$rmaId || $commentText === '') {
            return ['success' => false, 'message' => (string)__('Invalid request.')];
        }

        return null;
    }

    /**
     * @param int $rmaId
     * @return array|null
     */
    protected function validateOwnership(int $rmaId): ?array
    {
        try {
            $rma = $this->rmaRepository->get($rmaId);
        } catch (NoSuchEntityException) {
            return ['success' => false, 'message' => (string)__('RMA not found.')];
        }

        $customerId = (int)$this->customerSession->getCustomerId();
        if ((int)$rma->getCustomerId() !== $customerId) {
            return ['success' => false, 'message' => (string)__('Not authorized.')];
        }

        return null;
    }

    /**
     * @param int $rmaId
     * @param string $commentText
     * @return CommentInterface
     * @throws Exception
     */
    protected function createComment(int $rmaId, string $commentText): CommentInterface
    {
        $customer = $this->customerSession->getCustomer();
        $authorName = trim($customer->getFirstname() . ' ' . $customer->getLastname());

        $comment = $this->commentFactory->create();
        $comment->setRmaId($rmaId);
        $comment->setAuthorType(CommentInterface::AUTHOR_TYPE_CUSTOMER);
        $comment->setAuthorName($authorName);
        $comment->setComment($commentText);
        $comment->setIsVisibleToCustomer(true);
        $this->commentRepository->save($comment);

        return $comment;
    }
}
