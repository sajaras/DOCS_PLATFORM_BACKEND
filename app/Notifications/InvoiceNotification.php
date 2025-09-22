<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvoiceNotification extends Notification
{
    use Queueable;

    protected $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function via($notifiable)
    {
        $channels = ['mail']; // Default to email
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
                    ->line('You have a new invoice.')
                    ->action('View Invoice', url('/invoices/' . $this->invoice->id))
                    ->line('Thank you for your business!');
    }

    public function toWhatsapp($notifiable)
    {
        // Format the message for WhatsApp
        // ...
    }

    public function toNexmo($notifiable)
    {
        // Format the message for SMS
        // ...
    }
}