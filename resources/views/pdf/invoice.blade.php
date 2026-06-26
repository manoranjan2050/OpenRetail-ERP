<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
  .header { display: flex; justify-content: space-between; margin-bottom: 20px; }
  .business-name { font-size: 20px; font-weight: bold; }
  h2 { color: #444; margin: 0 0 4px; }
  table { width: 100%; border-collapse: collapse; margin-top: 16px; }
  th { background: #1e293b; color: #fff; padding: 8px 6px; text-align: left; }
  td { padding: 6px; border-bottom: 1px solid #e2e8f0; }
  .total-row td { font-weight: bold; border-top: 2px solid #1e293b; }
  .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; }
  .paid { background: #dcfce7; color: #166534; }
  .credit { background: #fef9c3; color: #854d0e; }
  .partial { background: #ffedd5; color: #9a3412; }
  .void { background: #fee2e2; color: #991b1b; }
  .qr { text-align: right; margin-top: 16px; }
  .footer { margin-top: 30px; font-size: 10px; color: #888; border-top: 1px solid #e2e8f0; padding-top: 8px; }
</style>
</head>
<body>
<div class="header">
  <div>
    <div class="business-name">{{ $settings->business_name }}</div>
    @if($settings->address) <div>{{ $settings->address }}</div> @endif
    @if($settings->gstin) <div>GSTIN: {{ $settings->gstin }}</div> @endif
  </div>
  <div style="text-align:right">
    <h2>TAX INVOICE</h2>
    <div><strong>{{ $invoice->number }}</strong></div>
    <div>Date: {{ $invoice->invoice_date->format('d M Y') }}</div>
    @if($invoice->due_date)<div>Due: {{ $invoice->due_date->format('d M Y') }}</div>@endif
    <span class="badge {{ $invoice->status }}">{{ strtoupper($invoice->status) }}</span>
  </div>
</div>

@if($invoice->customer)
<div style="background:#f8fafc; padding:10px; border-radius:4px; margin-bottom:12px;">
  <strong>Bill To:</strong> {{ $invoice->customer->name }}<br>
  @if($invoice->customer->mobile) Mobile: {{ $invoice->customer->mobile }}<br> @endif
  @if($invoice->customer->address) {{ $invoice->customer->address }} @endif
</div>
@endif

<table>
  <thead>
    <tr>
      <th>#</th><th>Item</th><th>HSN</th><th>Qty</th><th>Rate (₹)</th><th>GST %</th><th>Tax (₹)</th><th>Total (₹)</th>
    </tr>
  </thead>
  <tbody>
    @foreach($invoice->items as $i => $item)
    <tr>
      <td>{{ $i+1 }}</td>
      <td>{{ $item->product_name }}</td>
      <td>{{ $item->product?->hsn_code ?? '-' }}</td>
      <td>{{ $item->qty }}</td>
      <td>{{ number_format($item->unit_price, 2) }}</td>
      <td>{{ $item->gst_rate }}%</td>
      <td>{{ number_format($item->line_tax, 2) }}</td>
      <td>{{ number_format($item->line_total, 2) }}</td>
    </tr>
    @endforeach
  </tbody>
  <tfoot>
    <tr><td colspan="7" style="text-align:right">Subtotal</td><td>₹{{ number_format($invoice->subtotal, 2) }}</td></tr>
    <tr><td colspan="7" style="text-align:right">GST</td><td>₹{{ number_format($invoice->tax_total, 2) }}</td></tr>
    @if($invoice->discount > 0)
    <tr><td colspan="7" style="text-align:right">Discount</td><td>-₹{{ number_format($invoice->discount, 2) }}</td></tr>
    @endif
    <tr class="total-row"><td colspan="7" style="text-align:right">Grand Total</td><td>₹{{ number_format($invoice->grand_total, 2) }}</td></tr>
    <tr><td colspan="7" style="text-align:right">Paid</td><td>₹{{ number_format($invoice->paid_amount, 2) }}</td></tr>
    @if($invoice->grand_total - $invoice->paid_amount > 0)
    <tr><td colspan="7" style="text-align:right; color:#dc2626"><strong>Balance Due</strong></td><td style="color:#dc2626"><strong>₹{{ number_format($invoice->grand_total - $invoice->paid_amount, 2) }}</strong></td></tr>
    @endif
  </tfoot>
</table>

@if($qrCode)
<div class="qr">
  <div style="font-size:10px; color:#888; margin-bottom:4px;">Scan to pay via UPI</div>
  <img src="data:image/png;base64,{{ $qrCode }}" width="120" height="120">
  @if($settings->upi_id)<div style="font-size:10px">{{ $settings->upi_id }}</div>@endif
</div>
@endif

@if($invoice->notes)
<div style="margin-top:16px; font-size:11px"><strong>Notes:</strong> {{ $invoice->notes }}</div>
@endif

<div class="footer">
  Thank you for your business!
  @if($settings->terms) &nbsp;|&nbsp; {{ $settings->terms }} @endif
</div>
</body>
</html>
