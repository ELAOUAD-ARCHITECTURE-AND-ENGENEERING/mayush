<!DOCTYPE html>
<html>
<head>
    <style>
        @page { size: auto;  margin: 0mm; }
        body {
            font-family: 'Courier', monospace;
            width: 80mm;
            margin: 0 auto;
            padding: 5mm;
            font-size: 12px;
            line-height: 1.2;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .divider { border-top: 1px dashed #000; margin: 5px 0; }
        .title { font-weight: bold; font-size: 16px; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; }
        .totals { margin-top: 10px; }
        .footer { margin-top: 20px; font-size: 10px; }
    </style>
</head>
<body>
    <div class="text-center">
        <div class="title">{{ get_setting('site_name') }}</div>
        <div>{{ $order->seller->shop->name }}</div>
        <div>{{ $order->seller->shop->address }}</div>
        <div>{{ $order->seller->phone }}</div>
    </div>

    <div class="divider"></div>

    <table style="font-size: 11px;">
        <tr>
            <td>Date: {{ date('d-m-Y H:i', $order->date) }}</td>
        </tr>
        <tr>
            <td>Order ID: {{ $order->code }}</td>
        </tr>
        <tr>
            <td>Customer: {{ $order->user ? $order->user->name : translate('Walk-in Customer') }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Price</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->orderDetails as $orderDetail)
                <tr>
                    <td>
                        {{ $orderDetail->product->getTranslation('name') }}
                        @if ($orderDetail->variation)
                            <br><small>{{ $orderDetail->variation }}</small>
                        @endif
                    </td>
                    <td class="text-right">{{ $orderDetail->quantity }}</td>
                    <td class="text-right">{{ single_price($orderDetail->price * $orderDetail->quantity) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <div class="totals">
        <table>
            <tr>
                <td>Subtotal</td>
                <td class="text-right">{{ single_price($order->orderDetails->sum(function($t){ return $t->price * $t->quantity; })) }}</td>
            </tr>
            <tr>
                <td>Tax</td>
                <td class="text-right">{{ single_price($order->orderDetails->sum('tax')) }}</td>
            </tr>
            @if($order->coupon_discount > 0)
            <tr>
                <td>Discount</td>
                <td class="text-right">-{{ single_price($order->coupon_discount) }}</td>
            </tr>
            @endif
            <tr>
                <td>Shipping</td>
                <td class="text-right">{{ single_price($order->shipping_cost) }}</td>
            </tr>
            <tr style="font-weight: bold; font-size: 14px;">
                <td>TOTAL</td>
                <td class="text-right">{{ single_price($order->grand_total) }}</td>
            </tr>
        </table>
    </div>

    <div class="divider"></div>

    <div class="text-center footer">
        <p>{{ translate('Thank you for shopping with us!') }}</p>
        <p>{{ translate('Visit again.') }}</p>
    </div>

    <script>
        window.print();
    </script>
</body>
</html>
