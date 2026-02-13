<?php

declare(strict_types=1);

namespace MageOS\RMA\Model\Resolver;

use MageOS\RMA\Api\RMARepositoryInterface;
use MageOS\RMA\Model\Resolver\DataProvider\ReturnDataProvider;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\OrderRepositoryInterface;

class GuestReturn implements ResolverInterface
{
    use GuestOrderLookupTrait;

    /**
     * @param RMARepositoryInterface $rmaRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ReturnDataProvider $returnDataProvider
     */
    public function __construct(
        protected readonly RMARepositoryInterface $rmaRepository,
        protected readonly OrderRepositoryInterface $orderRepository,
        protected readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        protected readonly ReturnDataProvider $returnDataProvider
    ) {
    }

    /**
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws GraphQlInputException
     * @throws GraphQlAuthorizationException
     * @throws GraphQlNoSuchEntityException|NoSuchEntityException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null): array
    {
        $orderNumber = $args['order_number'] ?? '';
        $email = $args['email'] ?? '';
        $rmaId = (int)($args['rma_id'] ?? 0);

        if (!$orderNumber || !$email || !$rmaId) {
            throw new GraphQlInputException(__('Order number, email and RMA ID are required.'));
        }

        $order = $this->findGuestOrder($orderNumber, $email);

        try {
            $rma = $this->rmaRepository->get($rmaId);
        } catch (NoSuchEntityException) {
            throw new GraphQlNoSuchEntityException(__('RMA with ID "%1" does not exist.', $rmaId));
        }

        if ((int)$rma->getOrderId() !== (int)$order->getEntityId()) {
            throw new GraphQlAuthorizationException(__('You are not authorized to view this return.'));
        }

        return $this->returnDataProvider->formatRma($rma);
    }
}
