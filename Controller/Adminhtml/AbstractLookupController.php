<?php

declare(strict_types=1);

namespace MageOS\RMA\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Page;

abstract class AbstractLookupController extends Action
{
    /**
     * @return string
     */
    abstract protected function getMenuId(): string;

    /**
     * @return string
     */
    abstract protected function getBreadcrumbLabel(): string;

    /**
     * @param Page $resultPage
     * @return Page
     */
    protected function initPage(Page $resultPage): Page
    {
        $resultPage->setActiveMenu($this->getMenuId())
            ->addBreadcrumb(__('RMA'), __('RMA'))
            ->addBreadcrumb(__($this->getBreadcrumbLabel()), __($this->getBreadcrumbLabel()));

        return $resultPage;
    }
}
