<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Receipt - {{ $payment->receipt_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Courier New', monospace; font-size: 12px; color: #333; background: #f5f5f5; }
        .receipt { width: 80mm; margin: 20px auto; background: #fff; padding: 15px; border: 1px solid #ddd; }
        .receipt-header { text-align: center; border-bottom: 2px dashed #333; padding-bottom: 10px; margin-bottom: 10px; }
        .receipt-header h1 { font-size: 14px; margin-bottom: 5px; }
        .receipt-header p { font-size: 10px; color: #666; }
        .receipt-body { margin-bottom: 10px; }
        .receipt-row { display: flex; justify-content: space-between; margin-bottom: 3px; }
        .receipt-row .label { color: #666; }
        .receipt-row .value { font-weight: bold; }
        .receipt-divider { border-top: 1px dashed #ccc; margin: 8px 0; }
        .receipt-total { font-size: 14px; font-weight: bold; text-align: right; margin-top: 10px; }
        .receipt-footer { text-align: center; border-top: 2px dashed #333; padding-top: 10px; margin-top: 10px; font-size: 10px; color: #666; }
        .receipt-footer p { margin-bottom: 3px; }
        @media print {
            body { background: #fff; }
            .receipt { border: none; margin: 0; padding: 10px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align:center; padding:10px;">
        <button onclick="window.print()" style="padding:8px 20px; background:#28a745; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:14px;">
            Print Receipt
        </button>
        <button onclick="window.close()" style="padding:8px 20px; background:#6c757d; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:14px; margin-left:10px;">
            Close
        </button>
    </div>

    <div class="receipt">
        <div class="receipt-header">
            <h1>OFFICIAL RECEIPT</h1>
            <p>Multi-Village Management System</p>
            <p>{{ $payment->village?->village_name ?? 'Village Administration' }}</p>
            @if($payment->village?->address)<p>{{ $payment->village->address }}</p>@endif
        </div>

        <div class="receipt-body">
            <div class="receipt-row">
                <span class="label">Receipt No:</span>
                <span class="value">{{ $payment->receipt_number }}</span>
            </div>
            <div class="receipt-row">
                <span class="label">Transaction No:</span>
                <span class="value">TXN-{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="receipt-row">
                <span class="label">Billing No:</span>
                <span class="value">{{ $payment->billing_number_snapshot ?: ($payment->billing?->billing_number ?? '—') }}</span>
            </div>
            <div class="receipt-row">
                <span class="label">Date Paid:</span>
                <span class="value">{{ $payment->payment_date->format('M d, Y') }}</span>
            </div>

            <div class="receipt-divider"></div>

            <div class="receipt-row">
                <span class="label">Household:</span>
                <span class="value">{{ $payment->household?->household_number ?? '—' }}</span>
            </div>
            <div class="receipt-row">
                <span class="label">Head:</span>
                <span class="value">{{ $payment->household?->household_head_name ?? '—' }}</span>
            </div>
            @if($payment->household?->address)
            <div class="receipt-row">
                <span class="label">Address:</span>
                <span class="value">{{ $payment->household->address }}</span>
            </div>
            @endif
            <div class="receipt-row">
                <span class="label">Village:</span>
                <span class="value">{{ $payment->village?->village_name ?? '—' }}</span>
            </div>

            <div class="receipt-divider"></div>

            <div class="receipt-row">
                <span class="label">Payment Type:</span>
                <span class="value">{{ $payment->paymentType?->payment_name ?? '—' }}</span>
            </div>
            <div class="receipt-row">
                <span class="label">Period:</span>
                <span class="value">{{ $payment->billing_period_snapshot ?: ($payment->billing?->billing_period ?? '—') }}</span>
            </div>

            <div class="receipt-divider"></div>

            <div class="receipt-row">
                <span class="label">Amount:</span>
                <span class="value">₱{{ number_format($payment->amount, 2) }}</span>
            </div>
            <div class="receipt-total">
                TOTAL PAID: ₱{{ number_format($payment->total_paid, 2) }}
            </div>

            <div class="receipt-divider"></div>

            <div class="receipt-row">
                <span class="label">Billing Total:</span>
                <span class="value">₱{{ number_format($payment->billing_amount_snapshot ?? $payment->billing?->total_amount ?? 0, 2) }}</span>
            </div>
            <div class="receipt-row">
                <span class="label">Remaining Balance:</span>
                <span class="value" style="{{ ($payment->billing_balance_snapshot ?? $remainingBalance ?? 0) <= 0 ? 'color: green;' : 'color: red;' }}">
                    ₱{{ number_format($payment->billing_balance_snapshot ?? $remainingBalance ?? 0, 2) }}
                </span>
            </div>

            <div class="receipt-divider"></div>

            <div class="receipt-row">
                <span class="label">Method:</span>
                <span class="value">{{ strtoupper(str_replace('_', ' ', $payment->payment_method)) }}</span>
            </div>
            @if($payment->reference_number)
            <div class="receipt-row">
                <span class="label">Reference:</span>
                <span class="value">{{ $payment->reference_number }}</span>
            </div>
            @endif
            <div class="receipt-row">
                <span class="label">Received By:</span>
                <span class="value">{{ $payment->receivedByUser?->full_name ?? '—' }}</span>
            </div>
        </div>

        <div class="receipt-footer">
            <p><strong>Thank you for your payment!</strong></p>
            <p>This is an official receipt of the Multi-Village Management System.</p>
            <p>For inquiries, contact the City Planning and Development Office.</p>
            <p style="margin-top: 10px; font-size: 8px;">Generated: {{ now()->format('M d, Y h:i A') }}</p>
        </div>
    </div>
</body>
</html>
