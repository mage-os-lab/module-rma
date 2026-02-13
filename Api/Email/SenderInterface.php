<?php

declare(strict_types=1);

namespace MageOS\RMA\Api\Email;

use MageOS\RMA\Api\Data\RMAInterface;

interface SenderInterface
{
    /**
     * @param RMAInterface $rma
     * @return void
     */
    public function sendCustomerNewRmaEmail(RMAInterface $rma): void;

    /**
     * @param RMAInterface $rma
     * @param string $statusLabel
     * @return void
     */
    public function sendCustomerStatusChangeEmail(RMAInterface $rma, string $statusLabel): void;

    /**
     * @param RMAInterface $rma
     * @return void
     */
    public function sendAdminNewRmaEmail(RMAInterface $rma): void;
}
