<?php

declare(strict_types=1);

namespace MageOS\RMA\Block\Customer\Account;

use MageOS\RMA\Helper\ModuleConfig;
use MageOS\RMA\Model\ResourceModel\RMA\CollectionFactory as RmaCollectionFactory;
use Magento\Customer\Block\Account\SortLink;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\View\Element\Template\Context;

class RmaLink extends SortLink
{
    /**
     * @param Context $context
     * @param DefaultPathInterface $defaultPath
     * @param ModuleConfig $moduleConfig
     * @param CustomerSession $customerSession
     * @param RmaCollectionFactory $rmaCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath,
        protected readonly ModuleConfig $moduleConfig,
        protected readonly CustomerSession $customerSession,
        protected readonly RmaCollectionFactory $rmaCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml(): string
    {
        if (!empty($this->moduleConfig->getEnabledStoreIds())) {
            return parent::_toHtml();
        }

        // No store enabled, but show link if customer has existing RMAs
        $customerId = $this->customerSession->getCustomerId();
        if ($customerId) {
            $collection = $this->rmaCollectionFactory->create();
            $collection->addFieldToFilter('customer_id', (int)$customerId);
            $collection->setPageSize(1);

            if ($collection->getSize() > 0) {
                return parent::_toHtml();
            }
        }

        return '';
    }
}
