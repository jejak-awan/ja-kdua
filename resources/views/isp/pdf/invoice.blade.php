<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.5; }
        .invoice-box { max-width: 800px; margin: auto; padding: 10px; }
        .header { border-bottom: 2px solid #3b82f6; padding-bottom: 20px; margin-bottom: 20px; position: relative; }
        .company-info { float: left; width: 50%; }
        .invoice-info { float: right; width: 40%; text-align: right; }
        .billing-info { margin-bottom: 40px; clear: both; padding-top: 20px; }
        .bill-to { float: left; width: 48%; }
        .pay-to { float: right; width: 48%; text-align: right; }
        table { width: 100%; text-align: left; border-collapse: collapse; margin-top: 20px; }
        th { background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 12px; font-size: 12px; text-transform: uppercase; color: #64748b; }
        td { padding: 12px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        .total-box { margin-top: 30px; text-align: right; }
        .total-box table { width: 40%; margin-left: auto; }
        .total-box td { border: none; padding: 5px 12px; }
        .grand-total { font-size: 18px; font-weight: bold; color: #3b82f6; border-top: 2px solid #3b82f6 !important; padding-top: 10px !important; }
        .footer { margin-top: 50px; text-align: center; font-size: 10px; color: #94a3b8; border-top: 1px solid #eee; padding-top: 10px; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .badge-paid { background: #dcfce7; color: #166534; }
        .badge-unpaid { background: #fee2e2; color: #991b1b; }
        
        .watermark {
            position: fixed;
            top: 40%;
            left: 15%;
            transform: rotate(-45deg);
            font-size: 120px;
            font-weight: bold;
            z-index: -1;
            text-transform: uppercase;
            opacity: 0.1;
        }
        .watermark-paid { color: #166534; }
        .watermark-unpaid { color: #991b1b; }
        
        .clear { clear: both; }
    </style>
</head>
<body>
    <div class="watermark {{ $invoice->status === 'paid' ? 'watermark-paid' : 'watermark-unpaid' }}">
        {{ $invoice->status }}
    </div>

    <div class="invoice-box">
        <div class="header">
            <div class="company-info">
                <h1 style="margin: 0; color: #3b82f6;">K2NET ISP</h1>
                <p style="margin: 0; font-size: 12px;">PT. KADUA KREASI MEDIA</p>
                <p style="margin: 0; font-size: 12px;">Jl. Raya No. 123, Malang, Indonesia</p>
                <p style="margin: 0; font-size: 12px;">Support: +62 812 3456 7890</p>
            </div>
            <div class="invoice-info">
                <h2 style="margin: 0; text-transform: uppercase; color: #64748b;">Invoice</h2>
                <p style="margin: 0; font-weight: bold; font-size: 16px;">#{{ $invoice->invoice_number }}</p>
                <p style="margin: 0; font-size: 12px;">Date: {{ \Carbon\Carbon::parse($invoice->created_at)->format('d M Y') }}</p>
                <p style="margin: 0; font-size: 12px; color: #991b1b;">Due: {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</p>
                <div class="badge {{ $invoice->status === 'paid' ? 'badge-paid' : 'badge-unpaid' }}" style="margin-top: 10px;">
                    {{ $invoice->status }}
                </div>
            </div>
            <div class="clear"></div>
        </div>

        <div class="billing-info">
            <div class="bill-to">
                <p style="font-size: 11px; font-weight: bold; color: #64748b; text-transform: uppercase; margin-bottom: 5px;">Bill To:</p>
                <p style="margin: 0; font-weight: bold; font-size: 14px;">{{ $user->name }}</p>
                <p style="margin: 0; font-size: 12px;">{{ $user->email }}</p>
                <p style="margin: 0; font-size: 12px;">Phone: {{ $user->phone ?? '-' }}</p>
                <p style="margin: 0; font-size: 12px;">CID: #{{ $user->id }}</p>
            </div>
            <div class="pay-to">
                <p style="font-size: 11px; font-weight: bold; color: #64748b; text-transform: uppercase; margin-bottom: 5px;">Payment Details:</p>
                <p style="margin: 0; font-size: 12px; font-weight: bold;">{{ Setting::get('bank_name', 'BANK CENTRAL ASIA') }}</p>
                <p style="margin: 0; font-size: 12px;">Account: {{ Setting::get('bank_account_number', '123-456-7890') }}</p>
                <p style="margin: 0; font-size: 12px;">a/n: {{ Setting::get('bank_account_name', 'PT KADUA KREASI MEDIA') }}</p>
                @if($invoice->status !== 'paid')
                    <p style="margin-top: 5px; font-size: 10px; color: #3b82f6;">Please include INV#{{ $invoice->invoice_number }} in payment note.</p>
                @endif
            </div>
            <div class="clear"></div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th style="text-align: center; width: 100px;">Period</th>
                    <th style="text-align: right; width: 150px;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong style="font-size: 13px;">Internet Service Subscription</strong><br>
                        <small style="color: #64748b;">{{ $invoice->plan_name ?? 'Monthly Internet Package' }}</small>
                    </td>
                    <td style="text-align: center;">{{ \Carbon\Carbon::parse($invoice->due_date)->format('M Y') }}</td>
                    <td style="text-align: right;">{{ number_format($invoice->amount, 0, ',', '.') }} IDR</td>
                </tr>
                @if($invoice->tax_amount > 0)
                <tr>
                    <td>Tax (PPN 11%)</td>
                    <td style="text-align: center;">-</td>
                    <td style="text-align: right;">{{ number_format($invoice->tax_amount, 0, ',', '.') }} IDR</td>
                </tr>
                @endif
            </tbody>
        </table>

        <div class="total-box">
            <table>
                <tr>
                    <td style="color: #64748b;">Subtotal:</td>
                    <td style="text-align: right;">{{ number_format($invoice->amount - ($invoice->tax_amount ?? 0), 0, ',', '.') }} IDR</td>
                </tr>
                <tr class="grand-total">
                    <td>Total Payment:</td>
                    <td style="text-align: right;">{{ number_format($invoice->amount, 0, ',', '.') }} IDR</td>
                </tr>
            </table>
        </div>

        <div style="margin-top: 40px; text-align: center;">
            <img src="https://chart.googleapis.com/chart?chs=100x100&cht=qr&chl={{ urlencode('https://billing.k6net.id/v/'. $invoice->invoice_number) }}&choe=UTF-8" alt="QR Code">
            <p style="font-size: 9px; margin: 0; color: #94a3b8;">Original E-Invoice #{{ $invoice->invoice_number }}</p>
        </div>

        <div class="footer">
            <p style="margin-bottom: 5px;">Thank you for your business. For support, please call +62 812 3456 7890 or visit our customer portal.</p>
            <p>PT. KADUA KREASI MEDIA - Trusted Connectivity Partner</p>
        </div>
    </div>
</body>
</html>
