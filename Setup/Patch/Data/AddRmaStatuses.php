<?php

declare(strict_types=1);

namespace MageOS\RMA\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddRmaStatuses implements DataPatchInterface
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
        $tableName = $this->moduleDataSetup->getTable('rma_status');

        $statuses = [
            [
                'code' => 'new_request',
                'label' => 'New Request',
                'is_active' => 1,
                'sort_order' => 10
            ],
            [
                'code' => 'need_details',
                'label' => 'Need Details',
                'is_active' => 1,
                'sort_order' => 20
            ],
            [
                'code' => 'approved',
                'label' => 'Approved',
                'is_active' => 1,
                'sort_order' => 30
            ],
            [
                'code' => 'rejected',
                'label' => 'Rejected',
                'is_active' => 1,
                'sort_order' => 40
            ],
            [
                'code' => 'shipped_by_customer',
                'label' => 'Shipped by Customer',
                'is_active' => 1
                , 'sort_order' => 50
            ],
            [
                'code' => 'received_by_admin',
                'label' => 'Received by Admin',
                'is_active' => 1,
                'sort_order' => 60
            ],
            [
                'code' => 'canceled_by_customer',
                'label' => 'Canceled by Customer',
                'is_active' => 1,
                'sort_order' => 70
            ],
            [
                'code' => 'resolved',
                'label' => 'Resolved',
                'is_active' => 1,
                'sort_order' => 80
            ]
        ];

        foreach ($statuses as $status) {
            $connection->insertOnDuplicate($tableName, $status, ['label', 'is_active', 'sort_order']);
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
