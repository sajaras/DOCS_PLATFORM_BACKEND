<?php

namespace App\Listeners;

use App\Events\PurchaseOrderCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use PurchaseOrderCreated as GlobalPurchaseOrderCreated;
use PurchaseOrderNotification;

class SendPurchaseOrderNotification implements ShouldQueue
{
    public function handle(GlobalPurchaseOrderCreated $event)
    {
        $event->purchaseOrder->party->notify(new PurchaseOrderNotification($event->purchaseOrder));
    }
}
