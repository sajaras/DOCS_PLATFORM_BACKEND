<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Order {{ $purchaseOrder->code }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        .container { width: 100%; margin: 0 auto; }
        .header, .footer { text-align: center; }
        .content { margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        h1 { text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Purchase Order</h1>
            <h2>{{ $purchaseOrder->organization->name ?? 'Your Company' }}</h2>
            {{-- Add Organization Address Here --}}
        </div>

        <div class="content">
            <p><strong>PO Number:</strong> {{ $purchaseOrder->code }}</p>
            <p><strong>PO Date:</strong> {{ $purchaseOrder->order_date->format('d-M-Y') }}</p>
            <p><strong>Supplier:</strong> {{ $purchaseOrder->party->name }}</p>
            {{-- Add Supplier Address Here --}}
            <p><strong>Delivery Date:</strong> {{ $purchaseOrder->delivery_date->format('d-M-Y') }}</p>
            <p><strong>Delivery To:</strong> {{ $purchaseOrder->store->name }} at {{ $purchaseOrder->destinationAddress ? $purchaseOrder->destinationAddress->address_line_1 . ', ' . $purchaseOrder->destinationAddress->city : 'N/A' }}</p>
            @if($purchaseOrder->supplier_quote_number)
                <p><strong>Supplier Quote Ref:</strong> {{ $purchaseOrder->supplier_quote_number }}</p>
            @endif
             @if($purchaseOrder->reference_number)
                <p><strong>Our Ref:</strong> {{ $purchaseOrder->reference_number }}</p>
            @endif


            <h3>Order Details:</h3>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item</th>
                        <th>Make</th>
                        <th>Part No.</th>
                        <th>Qty</th>
                        <th>Basic Rate</th>
                        <th>Gross Amount</th>
                        {{-- Add columns for adjustments if you want to show them breakdown --}}
                        <th>Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseOrder->purchaseOrderDetails as $index => $detail)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $detail->item->name }} ({{ $detail->item->code }})</td>
                        <td>{{ $detail->make->name ?? 'N/A' }}</td>
                        <td>{{ $detail->part_number ?? 'N/A' }}</td>
                        <td>{{ (float)$detail->quantity }} {{ $detail->item->measurementUnit->code ?? '' }}</td>
                        <td class="text-right">{{ number_format($detail->basic_rate, 4) }}</td>
                        <td class="text-right">{{ number_format($detail->quantity * $detail->basic_rate, 2) }}</td>
                        {{-- You would calculate/display final line total here based on adjustments or use the accessor --}}
                        <td class="text-right">{{ number_format($detail->getCalculatedLineTotalAttribute(), 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            {{-- Add Summary of Header Charges/Deductions if any --}}
            {{-- Add Grand Total --}}
            <p style="text-align: right; font-weight: bold; font-size: 1.2em;">
                Grand Total: {{-- Currency Symbol --}} {{ number_format($purchaseOrder->getCalculatedGrandTotalAttribute(), 2) }}
            </p>

            @if($purchaseOrder->terms_and_conditions)
            <h3>Terms & Conditions:</h3>
            <p>{!! nl2br(e($purchaseOrder->terms_and_conditions)) !!}</p>
            @endif

            @if($purchaseOrder->remarks)
            <h3>Remarks:</h3>
            <p>{!! nl2br(e($purchaseOrder->remarks)) !!}</p>
            @endif
        </div>

        <div class="footer">
            <p>This is a computer-generated document.</p>
        </div>
    </div>
</body>
</html>