<?php

namespace App\Jobs;

use App\Models\PurchaseOrder;
use App\Models\Party;
use App\Mail\PurchaseOrderMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf; // Or your preferred PDF facade

class SendPurchaseOrderEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public PurchaseOrder $purchaseOrder;
    public Party $party;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\PurchaseOrder $purchaseOrder
     * @param \App\Models\Party $party
     * @return void
     */
    public function __construct(PurchaseOrder $purchaseOrder, Party $party)
    {
        $this->purchaseOrder = $purchaseOrder;
        $this->party = $party;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            Log::info("Generating PDF for Purchase Order ID: {$this->purchaseOrder->id}");
            // Ensure all necessary relationships are loaded for the PDF view
            $this->purchaseOrder->loadMissing([
                'organization', 
                'party', 
                'store.unit', 
                'destinationAddress',
                'modeOfTransportation',
                'purchaseOrderDetails.item.measurementUnit', // Load unit for items
                'purchaseOrderDetails.make',
                'purchaseOrderDetails.adjustments.chargeDeductionHead',
                'headerChargeDeductions.chargeDeductionHead'
            ]);

            // Generate PDF
            $pdf = Pdf::loadView('pdfs.purchase_order', ['purchaseOrder' => $this->purchaseOrder]);
            $pdfData = $pdf->output(); // Get raw PDF data
            $pdfFileName = 'PO_' . $this->purchaseOrder->code . '_' . $this->purchaseOrder->order_date->format('Ymd') . '.pdf';

            Log::info("Sending Purchase Order email for PO ID: {$this->purchaseOrder->id} to {$this->party->email}");
            Mail::to($this->party->email)
                ->send(new PurchaseOrderMail($this->purchaseOrder, $this->party, $pdfData, $pdfFileName));
            
            Log::info("Purchase Order email sent successfully for PO ID: {$this->purchaseOrder->id}");

        } catch (\Exception $e) {
            Log::error("Failed to send Purchase Order email for PO ID: {$this->purchaseOrder->id}. Error: {$e->getMessage()} at {$e->getFile()}:{$e->getLine()}");
            // Optionally, rethrow the exception to mark the job as failed for retry
            // $this->fail($e); 
            // Or notify someone, etc.
        }
    }
}