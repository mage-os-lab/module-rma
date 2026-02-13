<?php

declare(strict_types=1);

namespace MageOS\RMA\Model\Resolver;

use MageOS\RMA\Model\Resolver\DataProvider\ReturnDataProvider;
use MageOS\RMA\Service\OrderEligibility;
use MageOS\RMA\Service\RmaSubmitService;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;

class CreateCustomerReturn implements ResolverInterface
{
    use ReturnInputTrait;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderEligibility $orderEligibility
     * @param RmaSubmitService $rmaSubmitService
     * @param ReturnDataProvider $returnDataProvider
     */
    public function __construct(
        protected readonly OrderRepositoryInterface $orderRepository,
        protected readonly OrderEligibility $orderEligibility,
        protected readonly RmaSubmitService $rmaSubmitService,
        protected readonly ReturnDataProvider $returnDataProvider
    ) {
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws GraphQlAuthorizationException
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException|NoSuchEntityException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, ?array $value = null, ?array $args = null): array
    {
        if ($context->getExtensionAttributes()->getIsCustomer() === false) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        $input = $args['input'] ?? [];
        $this->validateRequiredFields($input, [
            'order_id' => 'Order ID',
            'reason_id' => 'Reason ID',
            'resolution_type_id' => 'Resolution type ID',
            'items' => 'Items',
        ]);

        $customerId = (int)$context->getUserId();
        $orderId = (int)$input['order_id'];

        try {
            $order = $this->orderRepository->get($orderId);
        } catch (NoSuchEntityException) {
            throw new GraphQlNoSuchEntityException(__('Order with ID "%1" does not exist.', $orderId));
        }

        if ((int)$order->getCustomerId() !== $customerId) {
            throw new GraphQlAuthorizationException(__('You are not authorized to create a return for this order.'));
        }

        if (!$this->orderEligibility->isOrderEligible($order)) {
            throw new GraphQlInputException(__('This order is not eligible for a return.'));
        }

        $selectedItems = $this->buildSelectedItems($input['items']);

        try {
            $rma = $this->rmaSubmitService->createRma(
                $order,
                $customerId,
                (string)$order->getCustomerEmail(),
                (string)($order->getCustomerName() ?: 'Customer'),
                (int)$input['reason_id'],
                (int)$input['resolution_type_id'],
                $selectedItems
            );
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        return ['return' => $this->returnDataProvider->formatRma($rma)];
    }
}
