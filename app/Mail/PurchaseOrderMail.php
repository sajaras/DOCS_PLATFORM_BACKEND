<?php

namespace App\Mail;

use App\Models\PurchaseOrder;
use App\Models\Party;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PurchaseOrderMail extends Mailable implements ShouldQueue // Make mailable itself queueable if not using job
{
    use Queueable, SerializesModels;

    public PurchaseOrder $purchaseOrder;
    public Party $party;
    public string $pdfData;
    public string $pdfFileName;

    /**
     * Create a new message instance.
     *
     * @param \App\Models\PurchaseOrder $purchaseOrder
     * @param \App\Models\Party $party
     * @param string $pdfData Raw PDF data
     * @param string $pdfFileName Desired name for the PDF attachment
     * @return void
     */
    public function __construct(PurchaseOrder $purchaseOrder, Party $party, string $pdfData, string $pdfFileName)
    {
        $this->purchaseOrder = $purchaseOrder;
        $this->party = $party;
        $this->pdfData = $pdfData;
        $this->pdfFileName = $pdfFileName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.orders.purchase_order_email')
                    ->subject("Purchase Order #{$this->purchaseOrder->code} from {$this->purchaseOrder->organization()->first()->name}")
                    ->attachData($this->pdfData, $this->pdfFileName, [
                        'mime' => 'application/pdf',
                    ]);
    }
}