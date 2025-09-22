<?php

// app/Notifications/PurchaseOrderNotification.php

use App\Models\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PurchaseOrderNotification extends Notification
{
    use Queueable;

    protected $purchaseOrder;

    public function __construct(PurchaseOrder $purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder;
    }

    public function via($notifiable)
    {
        $channels = ['mail'];
        if ($notifiable->whatsapp_number) {
            $channels= 'whatsapp';
        }
        if ($notifiable->phone_number) {
            $channels= 'nexmo'; // Assuming you're using Nexmo for SMS
        }
        return $channels;
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('You have a new purchase order.')
                    ->action('View Purchase Order', url('/purchase-orders/' . $this->purchaseOrder->id))
                    ->line('Thank you for your business!');
    }

    // Implement toWhatsapp and toNexmo methods for WhatsApp and SMS notifications
    // ...
}