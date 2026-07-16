<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 20px; }
        .invoice-header { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .logo { font-size: 24px; font-weight: bold; color: #6366f1; }
        .company-info { color: #666; margin-top: 5px; font-size: 11px; }
        .invoice-title { font-size: 28px; font-weight: bold; text-align: right; color: #6366f1; }
        .invoice-number { font-size: 14px; text-align: right; color: #666; }
        .section { margin-bottom: 20px; }
        .section-title { font-size: 14px; font-weight: bold; color: #666; text-transform: uppercase; margin-bottom: 10px; border-bottom: 2px solid #6366f1; padding-bottom: 5px; }
        .info-row { display: flex; margin-bottom: 5px; }
        .info-label { font-weight: bold; width: 100px; color: #666; }
        .info-value { flex: 1; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #6366f1; color: white; padding: 10px; text-align: left; font-size: 12px; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .totals { margin-top: 20px; text-align: right; }
        .totals table { width: 320px; margin-left: auto; }
        .totals td { padding: 5px 10px; }
        .totals .total-row { font-weight: bold; font-size: 14px; border-top: 2px solid #6366f1; }
        .totals .amount-due { background-color: #eef2ff; padding: 8px 10px; border-radius: 4px; }
        .footer { margin-top: 40px; text-align: center; color: #999; font-size: 10px; border-top: 1px solid #eee; padding-top: 10px; }
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: bold; }
        .status-paid { background-color: #d1fae5; color: #065f46; }
        .status-pending { background-color: #fef3c7; color: #92400e; }
        .status-overdue { background-color: #fee2e2; color: #991b1b; }
        .status-cancelled { background-color: #e5e7eb; color: #374151; }
        .notes { background-color: #f9fafb; padding: 15px; border-radius: 8px; margin-top: 20px; }
        .notes-title { font-weight: bold; margin-bottom: 5px; color: #666; }
    </style>
</head>
<body>
    <div class="invoice-header">
        <div>
            <div class="logo">{{ $companyName ?? 'DevlioPay' }}</div>
            <div class="company-info">
                {{ $companyEmail ?? 'billing@devliopay.com' }}<br>
                {{ config('app.url', 'http://localhost') }}
            </div>
        </div>
        <div>
            <div class="invoice-title">INVOICE</div>
            <div class="invoice-number">{{ $invoice->number }}</div>
        </div>
    </div>

    <div class="section">
        <table style="width: 100%; border: none;">
            <tr>
                <td style="width: 50%; border: none; vertical-align: top;">
                    <div class="section-title">Bill To</div>
                    <div><strong>{{ $invoice->user?->name ?? 'Deleted User' }}</strong></div>
                    <div>{{ $invoice->user?->email ?? '' }}</div>
                    @if($invoice->user?->company)
                        <div>{{ $invoice->user->company }}</div>
                    @endif
                    @if($invoice->user?->address)
                        <div>{{ $invoice->user->address }}</div>
                    @endif
                    @if($invoice->user?->city)
                        <div>{{ $invoice->user->city }}, {{ $invoice->user?->state ?? '' }} {{ $invoice->user?->zip_code ?? '' }}</div>
                    @endif
                    @if($invoice->user?->country)
                        <div>{{ $invoice->user->country }}</div>
                    @endif
                </td>
                <td style="width: 50%; border: none; vertical-align: top;">
                    <div class="section-title">Invoice Details</div>
                    <div class="info-row">
                        <span class="info-label">Date:</span>
                        <span class="info-value">{{ $invoice->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Due Date:</span>
                        <span class="info-value">{{ $invoice->due_at?->format('M d, Y') ?? 'N/A' }}</span>
                    </div>
                    @if($invoice->paid_at)
                        <div class="info-row">
                            <span class="info-label">Paid:</span>
                            <span class="info-value">{{ $invoice->paid_at->format('M d, Y') }}</span>
                        </div>
                    @endif
                    <div class="info-row">
                        <span class="info-label">Status:</span>
                        <span class="info-value">
                            <span class="status-badge status-{{ $invoice->status }}">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </span>
                    </div>
                    @if($invoice->currency)
                        <div class="info-row">
                            <span class="info-label">Currency:</span>
                            <span class="info-value">{{ $invoice->currency->code }} ({{ $invoice->currency->symbol }})</span>
                        </div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">${{ number_format($item->amount, 2) }}</td>
                    <td class="text-right">${{ number_format($item->amount * $item->quantity, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td>Subtotal:</td>
                <td class="text-right">${{ number_format($subtotal ?? $invoice->subtotal, 2) }}</td>
            </tr>
            @if(($tax ?? $invoice->tax ?? 0) > 0)
            <tr>
                <td>Tax:</td>
                <td class="text-right">${{ number_format($tax ?? $invoice->tax, 2) }}</td>
            </tr>
            @endif
            <tr>
                <td>Total:</td>
                <td class="text-right">${{ number_format($total ?? $invoice->total, 2) }}</td>
            </tr>
            @if(($credit ?? $invoice->credit ?? 0) > 0)
            <tr>
                <td>Credit Applied:</td>
                <td class="text-right" style="color: #059669;">-${{ number_format($credit ?? $invoice->credit, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row amount-due">
                <td><strong>Amount Due:</strong></td>
                <td class="text-right"><strong>${{ number_format($amountDue ?? max(0, $invoice->total - ($invoice->credit ?? 0)), 2) }}</strong></td>
            </tr>
        </table>
    </div>

    @if($invoice->notes)
        <div class="notes">
            <div class="notes-title">Notes</div>
            <div>{{ $invoice->notes }}</div>
        </div>
    @endif

    <div class="footer">
        <p>Thank you for your business!</p>
        <p>{{ $companyName ?? 'DevlioPay' }} - Open Source Billing Platform</p>
    </div>
</body>
</html>
