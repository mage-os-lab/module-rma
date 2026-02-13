<?php

declare(strict_types=1);

namespace MageOS\RMA\Service;

use MageOS\RMA\Api\Data\CommentInterface;

class CommentFormatter
{
    /**
     * @param CommentInterface $comment
     * @param bool $includeVisibility
     * @return array
     */
    public function toArray(CommentInterface $comment, bool $includeVisibility = false): array
    {
        $data = [
            'entity_id' => $comment->getEntityId(),
            'author_type' => $comment->getAuthorType(),
            'author_name' => $comment->getAuthorName(),
            'comment' => $comment->getComment(),
            'created_at' => $comment->getCreatedAt(),
        ];

        if ($includeVisibility) {
            $data['is_visible_to_customer'] = (bool)$comment->getIsVisibleToCustomer();
        }

        return $data;
    }
}
