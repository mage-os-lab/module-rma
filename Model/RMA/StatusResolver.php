<?php

declare(strict_types=1);

namespace MageOS\RMA\Model\RMA;

use MageOS\RMA\Api\StatusRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class StatusResolver
{
    /**
     * @var array
     */
    protected array $cache = [];

    /**
     * @param StatusRepositoryInterface $statusRepository
     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     */
    public function __construct(
        protected readonly StatusRepositoryInterface $statusRepository,
        protected readonly SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
    ) {
    }

    /**
     * @param int $statusId
     * @return string|null
     */
    public function getCodeById(int $statusId): ?string
    {
        if (!isset($this->cache[$statusId])) {
            try {
                $status = $this->statusRepository->get($statusId);
                $this->cache[$statusId] = $status->getCode();
            } catch (NoSuchEntityException) {
                return null;
            }
        }

        return $this->cache[$statusId];
    }

    /**
     * @param string $code
     * @return int|null
     */
    public function getIdByCode(string $code): ?int
    {
        $flipped = array_flip($this->cache);

        if (isset($flipped[$code])) {
            return $flipped[$code];
        }

        $searchCriteria = $this->searchCriteriaBuilderFactory->create()
            ->addFilter('code', $code)
            ->create();

        $results = $this->statusRepository->getList($searchCriteria);

        foreach ($results->getItems() as $status) {
            $id = (int)$status->getEntityId();
            $this->cache[$id] = $status->getCode();

            return $id;
        }

        return null;
    }
}
