<?php

declare(strict_types=1);

namespace MageOS\RMA\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class ModuleConfig extends AbstractHelper
{
    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        protected readonly StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
    }

    const string SECTION = 'rma/';

    const string GROUP_GENERAL = self::SECTION . 'general/';
    const string GROUP_POLICY = self::SECTION . 'policy/';
    const string GROUP_EMAIL = self::SECTION . 'email/';

    const string XML_PATH_ENABLED = self::GROUP_GENERAL . 'enabled';
    const string XML_PATH_INCREMENT_ID_PREFIX = self::GROUP_GENERAL . 'increment_id_prefix';
    const string XML_PATH_RETURN_PERIOD = self::GROUP_GENERAL . 'return_period';

    const string XML_PATH_AUTO_APPROVE = self::GROUP_POLICY . 'auto_approve';
    const string XML_PATH_ALLOWED_ORDER_STATUSES = self::GROUP_POLICY . 'allowed_order_statuses';

    const string XML_PATH_SENDER_IDENTITY = self::GROUP_EMAIL . 'sender_identity';
    const string XML_PATH_CUSTOMER_NEW_TEMPLATE = self::GROUP_EMAIL . 'customer_new_template';
    const string XML_PATH_CUSTOMER_STATUS_CHANGE_TEMPLATE = self::GROUP_EMAIL . 'customer_status_change_template';
    const string XML_PATH_ADMIN_NEW_TEMPLATE = self::GROUP_EMAIL . 'admin_new_template';
    const string XML_PATH_ADMIN_NOTIFY_EMAIL = self::GROUP_EMAIL . 'admin_notify_email';

    /**
     * @param int $storeId
     * @return bool
     */
    public function isEnabled(int $storeId = 0): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return array
     */
    public function getEnabledStoreIds(): array
    {
        $storeIds = [];

        foreach ($this->storeManager->getStores() as $store) {
            if ($this->isEnabled((int)$store->getId())) {
                $storeIds[] = (int)$store->getId();
            }
        }

        return $storeIds;
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getIncrementIdPrefix(int $storeId = 0): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_INCREMENT_ID_PREFIX,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int $storeId
     * @return int
     */
    public function getReturnPeriod(int $storeId = 0): int
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_RETURN_PERIOD,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int $storeId
     * @return bool
     */
    public function isAutoApproveEnabled(int $storeId = 0): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_AUTO_APPROVE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int $storeId
     * @return array
     */
    public function getAllowedOrderStatuses(int $storeId = 0): array
    {
        $value = (string)$this->scopeConfig->getValue(
            self::XML_PATH_ALLOWED_ORDER_STATUSES,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $value !== '' ? explode(',', $value) : [];
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getEmailSenderIdentity(int $storeId = 0): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_SENDER_IDENTITY,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getCustomerNewTemplate(int $storeId = 0): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_CUSTOMER_NEW_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getCustomerStatusChangeTemplate(int $storeId = 0): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_CUSTOMER_STATUS_CHANGE_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getAdminNewTemplate(int $storeId = 0): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_ADMIN_NEW_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getAdminNotifyEmail(int $storeId = 0): string
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_ADMIN_NOTIFY_EMAIL,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
