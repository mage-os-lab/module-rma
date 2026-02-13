<?php

declare(strict_types=1);

namespace MageOS\RMA\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ItemCondition extends AbstractDb
{
    /**
     * @var string
     */
    protected string $_eventPrefix = 'rma_item_condition_resource_model';

    /**
     * @var string
     */
    protected string $labelsTable = '';

    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('rma_item_condition', 'entity_id');
        $this->_useIsObjectNew = true;
        $this->labelsTable = $this->getTable('rma_item_condition_label');
    }

    /**
     * @param AbstractModel $object
     * @return array
     */
    public function getStoreLabels(AbstractModel $object): array
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->labelsTable, ['store_id', 'label'])
            ->where('condition_id = ?', (int)$object->getId());

        return $connection->fetchPairs($select);
    }

    /**
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(AbstractModel $object): static
    {
        parent::_afterLoad($object);

        if ($object->getId()) {
            $object->setData('store_labels', $this->getStoreLabels($object));
        }

        return $this;
    }

    /**
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object): static
    {
        parent::_afterSave($object);

        if ($object->hasData('store_labels')) {
            $connection = $this->getConnection();
            $connection->delete($this->labelsTable, ['condition_id = ?' => (int)$object->getId()]);

            $labels = $object->getData('store_labels');
            $data = [];
            foreach ($labels as $storeId => $label) {
                if (empty($label)) {
                    continue;
                }
                $data[] = [
                    'condition_id' => (int)$object->getId(),
                    'store_id' => (int)$storeId,
                    'label' => $label,
                ];
            }

            if (!empty($data)) {
                $connection->insertMultiple($this->labelsTable, $data);
            }
        }

        return $this;
    }
}
