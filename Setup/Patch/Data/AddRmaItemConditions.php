<?php

declare(strict_types=1);

namespace MageOS\RMA\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddRmaItemConditions implements DataPatchInterface
{
    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        protected readonly ModuleDataSetupInterface $moduleDataSetup
    ) {
    }

    /**
     * @return $this
     */
    public function apply(): self
    {
        $this->moduleDataSetup->startSetup();

        $connection = $this->moduleDataSetup->getConnection();
        $tableName = $this->moduleDataSetup->getTable('rma_item_condition');

        $conditions = [
            [
                'code' => 'damaged',
                'label' => 'Damaged',
                'is_active' => 1,
                'sort_order' => 10
            ],
            [
                'code' => 'opened',
                'label' => 'Opened',
                'is_active' => 1,
                'sort_order' => 20
            ],
            [
                'code' => 'unopened',
                'label' => 'Unopened',
                'is_active' => 1,
                'sort_order' => 30
            ],
        ];

        foreach ($conditions as $condition) {
            $connection->insertOnDuplicate($tableName, $condition, ['label', 'is_active', 'sort_order']);
        }

        $this->moduleDataSetup->endSetup();

        return $this;
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases(): array
    {
        return [];
    }
}
