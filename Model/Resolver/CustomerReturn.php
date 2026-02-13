<?php

declare(strict_types=1);

namespace MageOS\RMA\Model\Resolver;

use MageOS\RMA\Api\RMARepositoryInterface;
use MageOS\RMA\Model\Resolver\DataProvider\ReturnDataProvider;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomerReturn implements ResolverInterface
{
    use CustomerRmaAccessTrait;

    /**
     * @param RMARepositoryInterface $rmaRepository
     * @param ReturnDataProvider $returnDataProvider
     */
    public function __construct(
        protected readonly RMARepositoryInterface $rmaRepository,
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
        $rma = $this->loadCustomerRma($context, (int)($args['rma_id'] ?? 0));

        return $this->returnDataProvider->formatRma($rma);
    }
}
