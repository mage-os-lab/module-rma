<?php

declare(strict_types=1);

namespace MageOS\RMA\Model\ResourceModel\RMA\Grid;

use MageOS\RMA\Model\ResourceModel\RMA\Collection as RmaCollection;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Psr\Log\LoggerInterface;

class Collection extends RmaCollection implements SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    protected AggregationInterface $aggregations;

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param string $mainTable
     * @param string $resourceModel
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        string $mainTable = 'rma_entity',
        string $resourceModel = \MageOS\RMA\Model\ResourceModel\RMA::class,
        ?AdapterInterface $connection = null,
        ?AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->_init(Document::class, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * @return $this
     */
    protected function _initSelect(): static
    {
        parent::_initSelect();

        $this->getSelect()->joinLeft(
            ['rma_status' => $this->getTable('rma_status')],
            'main_table.status_id = rma_status.entity_id',
            ['status_label' => 'rma_status.label']
        )->joinLeft(
            ['rma_reason' => $this->getTable('rma_reason')],
            'main_table.reason_id = rma_reason.entity_id',
            ['reason_label' => 'rma_reason.label']
        )->joinLeft(
            ['rma_resolution' => $this->getTable('rma_resolution_type')],
            'main_table.resolution_type_id = rma_resolution.entity_id',
            ['resolution_label' => 'rma_resolution.label']
        );

        return $this;
    }

    /**
     * @param string $field
     * @param string|null $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null): static
    {
        $mappedFields = [
            'entity_id' => 'main_table.entity_id',
            'status_id' => 'main_table.status_id',
            'reason_id' => 'main_table.reason_id',
            'resolution_type_id' => 'main_table.resolution_type_id',
            'created_at' => 'main_table.created_at',
            'updated_at' => 'main_table.updated_at',
            'store_id' => 'main_table.store_id',
        ];

        if (isset($mappedFields[$field])) {
            $field = $mappedFields[$field];
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * @return AggregationInterface
     */
    public function getAggregations(): AggregationInterface
    {
        return $this->aggregations;
    }

    /**
     * @param $aggregations
     * @return $this
     */
    public function setAggregations($aggregations): static
    {
        $this->aggregations = $aggregations;

        return $this;
    }

    /**
     * @return SearchCriteriaInterface|null
     */
    public function getSearchCriteria(): ?SearchCriteriaInterface
    {
        return null;
    }

    /**
     * @param SearchCriteriaInterface|null $searchCriteria
     * @return $this
     */
    public function setSearchCriteria(?SearchCriteriaInterface $searchCriteria = null): static
    {
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->getSize();
    }

    /**
     * @param $totalCount
     * @return $this
     */
    public function setTotalCount($totalCount): static
    {
        return $this;
    }

    /**
     * @param array|null $items
     * @return $this
     */
    public function setItems(?array $items = null): static
    {
        return $this;
    }
}
