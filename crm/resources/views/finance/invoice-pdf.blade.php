<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10pt; color: #1a1a1a; }
    .page { width: 210mm; min-height: 297mm; padding: 16mm 20mm; }
    .header { display: table; width: 100%; margin-bottom: 10mm; }
    .header-left { display: table-cell; vertical-align: top; }
    .header-right { display: table-cell; text-align: right; vertical-align: top; }
    .brand { font-size: 18pt; font-weight: bold; color: #4A3050; }
    .tagline { font-size: 8pt; color: #7a5f87; }
    .invoice-title { font-size: 22pt; font-weight: bold; color: #4A3050; }
    .inv-meta { font-size: 9pt; color: #666; margin-top: 4pt; }
    .section-label { font-size: 7.5pt; font-weight: bold; text-transform: uppercase; letter-spacing: .08em; color: #4A3050; border-bottom: 1.5pt solid #4A3050; padding-bottom: 2pt; margin: 8pt 0 5pt; }
    .two-col { display: table; width: 100%; }
    .col-l { display: table-cell; width: 50%; vertical-align: top; padding-right: 6mm; }
    .col-r { display: table-cell; width: 50%; vertical-align: top; }
    .dt { font-size: 8.5pt; color: #888; }
    .dd { font-size: 10pt; color: #1a1a1a; margin-bottom: 3pt; }
    table.items { width: 100%; border-collapse: collapse; margin-top: 6pt; }
    table.items th { background: #4A3050; color: #C9A8D4; font-size: 8pt; padding: 5pt 8pt; text-align: left; }
    table.items td { padding: 5pt 8pt; font-size: 9.5pt; border-bottom: .5pt solid #ebe1ef; }
    .totals-block { width: 60%; margin-left: auto; margin-top: 6pt; }
    .totals-row { display: table; width: 100%; padding: 3pt 0; border-bottom: .5pt solid #ebe1ef; }
    .totals-row.total { border-top: 1.5pt solid #4A3050; border-bottom: none; font-weight: bold; font-size: 12pt; color: #4A3050; margin-top: 4pt; }
    .tl { display: table-cell; color: #666; font-size: 9pt; }
    .tr { display: table-cell; text-align: right; font-size: 9.5pt; }
    .status-badge { display: inline-block; background: #f5f0f7; color: #4A3050; font-size: 8pt; font-weight: bold; padding: 2pt 8pt; border-radius: 4pt; }
    .footer { position: absolute; bottom: 12mm; left: 20mm; right: 20mm; font-size: 7.5pt; color: #aaa; border-top: .5pt solid #ebe1ef; padding-top: 4pt; display: table; width: calc(100% - 40mm); }
    .fl { display: table-cell; }
    .fr { display: table-cell; text-align: right; }
</style>
</head>
<body>
<div class="page">
    <div class="header">
        <div class="header-left">
            <div class="brand">Prescribe &amp; Co</div>
            <div class="tagline">GPhC-Registered Online Pharmacy</div>
            <div class="tagline" style="margin-top:2pt">{{ $pharmacy['address'] }}</div>
            <div class="tagline">{{ $pharmacy['email'] }} · GPhC: {{ $pharmacy['gphc_number'] }}</div>
        </div>
        <div class="header-right">
            <div class="invoice-title">INVOICE</div>
            <div class="inv-meta">{{ $invoice->invoice_number }}</div>
            <div class="inv-meta">Issued: {{ $invoice->issued_at?->format('d F Y') ?? now()->format('d F Y') }}</div>
            @if($invoice->paid_at)<div class="inv-meta">Paid: {{ $invoice->paid_at->format('d F Y') }}</div>@endif
            <div style="margin-top:6pt"><span class="status-badge">{{ strtoupper($invoice->status) }}</span></div>
        </div>
    </div>

    <div class="two-col">
        <div class="col-l">
            <div class="section-label">Bill To</div>
            <div class="dd">{{ $invoice->patient?->full_name }}</div>
            <div class="dt">{{ $invoice->patient?->email }}</div>
            @if($invoice->order?->delivery_address_line_1)
                <div class="dt" style="margin-top:3pt">{{ $invoice->order->deliveryAddress() }}</div>
            @endif
        </div>
        <div class="col-r">
            <div class="section-label">Order Details</div>
            @if($invoice->order)
                <div class="dt">Order number</div><div class="dd">{{ $invoice->order->order_number }}</div>
            @endif
            @if($invoice->stripe_invoice_id)
                <div class="dt">Stripe invoice</div><div class="dd" style="font-size:8pt">{{ $invoice->stripe_invoice_id }}</div>
            @endif
        </div>
    </div>

    <div class="section-label" style="margin-top:10pt">Items</div>
    <table class="items">
        <thead><tr>
            <th>Description</th>
            <th>Qty</th>
            <th style="text-align:right">Unit Price</th>
            <th style="text-align:right">VAT %</th>
            <th style="text-align:right">Line Total</th>
        </tr></thead>
        <tbody>
            @if($invoice->order?->items)
                @foreach($invoice->order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}{{ $item->product_strength ? ' ' . $item->product_strength : '' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td style="text-align:right">£{{ number_format($item->unit_price, 2) }}</td>
                    <td style="text-align:right">{{ number_format($item->vat_rate, 0) }}%</td>
                    <td style="text-align:right">£{{ number_format($item->line_total, 2) }}</td>
                </tr>
                @endforeach
            @else
                <tr><td colspan="5" style="color:#888">No line items</td></tr>
            @endif
        </tbody>
    </table>

    <div class="totals-block">
        <div class="totals-row"><span class="tl">Subtotal</span><span class="tr">£{{ number_format($invoice->subtotal, 2) }}</span></div>
        @if($invoice->vat_amount > 0)
        <div class="totals-row"><span class="tl">VAT</span><span class="tr">£{{ number_format($invoice->vat_amount, 2) }}</span></div>
        @else
        <div class="totals-row"><span class="tl">VAT (zero-rated)</span><span class="tr">£0.00</span></div>
        @endif
        <div class="totals-row total"><span class="tl">Total</span><span class="tr">£{{ number_format($invoice->total, 2) }}</span></div>
    </div>

    <div class="footer">
        <div class="fl">Prescribe &amp; Co · VAT Registration pending · Company No. pending · GPhC: {{ $pharmacy['gphc_number'] }}</div>
        <div class="fr">Generated {{ now()->format('d M Y') }} · CONFIDENTIAL</div>
    </div>
</div>
</body>
</html>
