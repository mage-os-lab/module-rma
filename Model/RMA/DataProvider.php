<?php

declare(strict_types=1);

namespace MageOS\RMA\Model\RMA;

use MageOS\RMA\Model\ResourceModel\RMA\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var array|null
     */
    protected ?array $loadedData = null;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param RequestInterface $request
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        protected readonly DataPersistorInterface $dataPersistor,
        protected readonly RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @return array|null
     */
    public function getData(): ?array
    {
        if ($this->loadedData !== null) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();

        foreach ($items as $rma) {
            $this->loadedData[$rma->getId()] = $rma->getData();
        }

        $persistedData = $this->dataPersistor->get('rma_entity');
        if (!empty($persistedData)) {
            $rma = $this->collection->getNewEmptyItem();
            $rma->setData($persistedData);
            $this->loadedData[$rma->getId()] = $rma->getData();
            $this->dataPersistor->clear('rma_entity');
        }

        return $this->loadedData;
    }

    /**
     * @return array
     */
    public function getMeta(): array
    {
        $meta = parent::getMeta();
        $isEdit = (bool)$this->request->getParam('entity_id');

        if ($isEdit) {
            // Edit mode: hide the creation fieldset, disable reason + resolution selects
            $meta['general']['arguments']['data']['config']['visible'] = false;
            $meta['processing']['children']['reason_id']['arguments']['data']['config']['disabled'] = true;
            $meta['processing']['children']['resolution_type_id']['arguments']['data']['config']['disabled'] = true;
        }

        return $meta;
    }
}
