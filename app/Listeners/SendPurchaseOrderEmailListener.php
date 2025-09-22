<?php

namespace App\Listeners;

use App\Events\PurchaseOrderReadyForSending;
use App\Jobs\SendPurchaseOrderEmailJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendPurchaseOrderEmailListener implements ShouldQueue // Implement ShouldQueue to make the listener itself queued
{
    use InteractsWithQueue; // Allows you to use queue-related methods if needed

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\PurchaseOrderReadyForSending  $event
     * @return void
     */
    public function handle(PurchaseOrderReadyForSending $event)
    {
        $purchaseOrder = $event->purchaseOrder;
        
        // Ensure the party (supplier) has an email address
        if ($purchaseOrder->party && $purchaseOrder->party->email) {
            Log::info("Dispatching SendPurchaseOrderEmailJob for PO ID: {$purchaseOrder->id} to Party ID: {$purchaseOrder->party->id}");
            SendPurchaseOrderEmailJob::dispatch($purchaseOrder, $purchaseOrder->party);
        } else {
            Log::warning("Cannot send Purchase Order email for PO ID: {$purchaseOrder->id}. Party or party email is missing.");
        }
    }

    /**
     * Handle a job failure.
     *
     * @param  \App\Events\PurchaseOrderReadyForSending  $event
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(PurchaseOrderReadyForSending $event, $exception)
    {
        Log::error("Failed to queue SendPurchaseOrderEmailJob for PO ID: {$event->purchaseOrder->id}. Error: {$exception->getMessage()}");
    }
}