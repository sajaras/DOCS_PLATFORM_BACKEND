@component('mail::message')
# Purchase Order #{{ $purchaseOrder->code }}

Dear {{ $party->name }},

Please find attached the Purchase Order #{{ $purchaseOrder->code }} from {{ $purchaseOrder->organization->name ?? 'Our Company' }}.

**PO Date:** {{ $purchaseOrder->order_date->format('d-M-Y') }}
**Delivery Date:** {{ $purchaseOrder->delivery_date->format('d-M-Y') }}

Further details are available in the attached PDF.

Thanks,<br>
{{ $purchaseOrder->organization->name ?? config('app.name') }}
@endcomponent