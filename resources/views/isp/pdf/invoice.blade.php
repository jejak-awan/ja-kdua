<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; line-height: 1.5; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #3b82f6; padding-bottom: 20px; margin-bottom: 20px; }
        .company-info { text-align: left; }
        .invoice-info { text-align: right; }
        .billing-info { margin-bottom: 40px; }
        .billing-info div { display: inline-block; width: 48%; vertical-align: top; }
        table { width: 100%; text-align: left; border-collapse: collapse; }
        th { background: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 12px; font-size: 12px; text-transform: uppercase; color: #64748b; }
        td { padding: 12px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
        .total-box { margin-top: 30px; text-align: right; }
        .total-box table { width: 40%; margin-left: auto; }
        .total-box td { border: none; padding: 5px 12px; }
        .grand-total { font-size: 18px; font-weight: bold; color: #3b82f6; border-top: 2px solid #3b82f6 !important; padding-top: 10px !important; }
        .footer { margin-top: 50px; text-align: center; font-size: 10px; color: #94a3b8; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 9999px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .badge-paid { background: #dcfce7; color: #166534; }
        .badge-unpaid { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <div class="company-info">
                <h1 style="margin: 0; color: #3b82f6;">K2NET ISP</h1>
                <p style="margin: 0; font-size: 12px;">Jl. Raya No. 123, Malang, Indonesia</p>
                <p style="margin: 0; font-size: 12px;">Support: +62 812 3456 7890</p>
            </div>
            <div class="invoice-info">
                <h2 style="margin: 0; text-transform: uppercase; color: #64748b;">Invoice</h2>
                <p style="margin: 0; font-weight: bold;">#{{ $invoice->invoice_number }}</p>
                <p style="margin: 0; font-size: 12px;">Due: {{ $invoice->due_date }}</p>
                <div class="badge {{ $invoice->status === 'paid' ? 'badge-paid' : 'badge-unpaid' }}" style="margin-top: 10px;">
                    {{ $invoice->status }}
                </div>
            </div>
        </div>

        <div class="billing-info">
            <div>
                <p style="font-size: 12px; font-weight: bold; color: #64748b; text-transform: uppercase; margin-bottom: 5px;">Bill To:</p>
                <p style="margin: 0; font-weight: bold;">{{ $user->name }}</p>
                <p style="margin: 0; font-size: 12px;">{{ $user->email }}</p>
                <p style="margin: 0; font-size: 12px;">Customer ID: #{{ $user->id }}</p>
            </div>
            <div style="text-align: right;">
                <p style="font-size: 12px; font-weight: bold; color: #64748b; text-transform: uppercase; margin-bottom: 5px;">Payment Details:</p>
                <p style="margin: 0; font-size: 12px;">Bank Transfer / Virtual Account</p>
                <p style="margin: 0; font-size: 12px;">BCA: 123-456-7890</p>
                <p style="margin: 0; font-size: 12px;">a/n K2NET Media</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align: right;">Period</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>Internet Service Subscription</strong><br>
                        <small style="color: #64748b;">Unlimited Fiber connection</small>
                    </td>
                    <td style="text-align: right;">{{ \Carbon\Carbon::parse($invoice->due_date)->format('M Y') }}</td>
                    <td style="text-align: right;">Rp{{ number_format($invoice->amount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="total-box">
            <table>
                <tr>
                    <td>Subtotal:</td>
                    <td style="text-align: right;">Rp{{ number_format($invoice->amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Tax (PPN 11%):</td>
                    <td style="text-align: right;">Rp0</td>
                </tr>
                <tr class="grand-total">
                    <td>Total Due:</td>
                    <td style="text-align: right;">Rp{{ number_format($invoice->amount, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p>Thank you for choosing K2NET as your internet service provider.</p>
            <p>This is a computer-generated document, no signature required.</p>
        </div>
    </div>
</body>
</html>
