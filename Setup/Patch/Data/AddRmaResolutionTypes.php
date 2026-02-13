<?php

declare(strict_types=1);

namespace MageOS\RMA\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddRmaResolutionTypes implements DataPatchInterface
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
        $tableName = $this->moduleDataSetup->getTable('rma_resolution_type');

        $resolutionTypes = [
            [
                'code' => 'repair',
                'label' => 'Repair',
                'is_active' => 1,
                'sort_order' => 10
            ],
            [
                'code' => 'return',
                'label' => 'Return',
                'is_active' => 1,
                'sort_order' => 20
            ],
            [
                'code' => 'exchange',
                'label' => 'Exchange',
                'is_active' => 1,
                'sort_order' => 30
            ]
        ];

        foreach ($resolutionTypes as $resolutionType) {
            $connection->insertOnDuplicate($tableName, $resolutionType, ['label', 'is_active', 'sort_order']);
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
