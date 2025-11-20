<!DOCTYPE html>
<html>
<head>
    <title>Invoice - {{ $order->order_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; margin-bottom: 30px; }
        .section { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { font-weight: bold; font-size: 1.2em; }
    </style>
</head>
<body>
<div class="header">
    <h1>INVOICE</h1>
    <p>Order #: {{ $order->order_number }}</p>
    <p>Date: {{ $order->created_at->format('M d, Y') }}</p>
</div>

<div class="section">
    <h3>Customer Information</h3>
    <p>Email: {{ $order->customer_email }}</p>
    <p>Phone: {{ $order->customer_phone ?? 'N/A' }}</p>
</div>

<div class="section">
    <h3>Shipping Address</h3>
    <p>{{ $order->shipping_address }}</p>
</div>

<div class="section">
    <h3>Order Items</h3>
    <table>
        <thead>
        <tr>
            <th>Product</th>
            <th>SKU</th>
            <th>Quantity</th>
            <th>Unit Price</th>
            <th>Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach($order->items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td>{{ $item->sku }}</td>
                <td>{{ $item->quantity }}</td>
                <td>${{ number_format($item->unit_price, 2) }}</td>
                <td>${{ number_format($item->total_price, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<div class="section">
    <p class="total">Total Amount: ${{ number_format($order->total_amount, 2) }}</p>
</div>

<div class="section">
    <p>Status: {{ strtoupper($order->status) }}</p>
    @if($order->notes)
        <p>Notes: {{ $order->notes }}</p>
    @endif
</div>
</body>
</html>
