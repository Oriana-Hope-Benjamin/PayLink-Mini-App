<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Subscription Payment Receipt</title>
<style>
    body {
        font-family: sans-serif;
        color: #333;
        line-height: 1.5;
        margin: 0;
        padding: 40px;
    }
    .header {
        border-bottom: 5px solid #f25c54; /* Red bar */
        padding-bottom: 20px;
        margin-bottom: 40px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .logo {
        width: 60px;
        height: 60px;
        background-color: #f25c54; /* Red square logo placeholder */
        color: white;
        font-weight: bold;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 24px;
    }
    .contact-info {
        text-align: right;
        font-size: 12px;
    }
    h1 {
        text-align: center;
        font-size: 28px;
        margin-bottom: 30px;
    }
    p {
        font-size: 14px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 40px;
    }
    th, td {
        padding: 12px;
        text-align: left;
    }
    thead th {
        background-color: #f3f4f6; /* Light grey header */
        color: #888;
        font-weight: normal;
    }
    td {
        border-bottom: 1px solid #e5e7eb;
    }
    .amount-column {
        text-align: right;
    }
    .total-row td {
        font-weight: bold;
    }
    .bill-to, .payment-method {
        font-size: 14px;
        margin-bottom: 30px;
    }
    .bill-to h3, .payment-method h3 {
        font-size: 16px;
        margin-bottom: 10px;
    }
    .authorized-signature {
        margin-top: 60px;
    }
    .authorized-signature h3 {
        font-size: 16px;
        margin-bottom: 30px;
    }
    .signature-line {
        border-bottom: 1px solid #333;
        width: 300px;
        display: inline-block;
    }
</style>
</head>
<body>

<div class="header">
    <div class="logo">L</div>
    <div class="contact-info">
        <p>inquire@sureplus.mail | Template.net | 222 555 7777</p>
    </div>
</div>

<h1>Subscription Payment Receipt</h1>

<p>Thank you for your payment. Below are the details of your transaction:</p>

<table>
    <thead>
        <tr>
            <th>Item Description</th>
            <th class="amount-column">Amount</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $payment->description ?? 'Payment' }}</td>
            <td class="amount-column">{{ $payment->currency }} {{ number_format($payment->amount / 100, 2) }}</td>
        </tr>
        <tr>
            <td>Tax</td>
            <td class="amount-column">{{ $payment->currency }} 0.00</td>
        </tr>
        <tr class="total-row">
            <td>Total Amount:</td>
            <td class="amount-column">{{ $payment->currency }} {{ number_format($payment->amount / 100, 2) }}</td>
        </tr>
    </tbody>
</table>

<div class="bill-to">
    <h3>Bill To:</h3>
    <p>{{ $payment->payer_name }}<br>
    {{ $payment->payer_phone }}<br>
    {{ $payment->payer_email ?? 'N/A' }}</p>
</div>

<div class="payment-method">
    <h3>Payment Method:</h3>
    <p>Mobile Money (MTN MoMo)<br>
    <strong>Transaction Date:</strong> {{ $payment->updated_at->format('F j, Y') }}<br>
    <strong>Transaction ID:</strong> {{ $payment->external_txn_id ?? $payment->internal_reference }}</p>
</div>

</body>
</html>