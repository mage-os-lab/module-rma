<?php

declare(strict_types=1);

namespace MageOS\RMA\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Component\ComponentRegistrar;

class RegisterModuleForHyvaConfig implements ObserverInterface
{
    /**
     * @param ComponentRegistrar $componentRegistrar
     */
    public function __construct(
        protected ComponentRegistrar $componentRegistrar
    ) {
    }

    /**
     * @param Observer $event
     * @return void
     */
    public function execute(Observer $event): void
    {
        $config = $event->getData('config');
        $extensions = $config->hasData('extensions') ? $config->getData('extensions') : [];
        $path = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, 'MageOS_RMA');

        $extensions[] = ['src' => substr($path, strlen(BP) + 1)];

        $config->setData('extensions', $extensions);
    }
}
