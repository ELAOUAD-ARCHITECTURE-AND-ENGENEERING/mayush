<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap');
        
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f4f7f9;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: none;
            width: 100% !important;
        }

        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f4f7f9;
            padding-bottom: 40px;
        }

        .main {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 600px;
            border-spacing: 0;
            color: #1e293b;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #0f172a 0%, #334155 100%);
            padding: 40px 20px;
            text-align: center;
        }

        .header img {
            max-width: 180px;
        }

        .content {
            padding: 40px 30px;
        }

        .title {
            font-size: 28px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 16px;
            text-align: center;
        }

        .subtitle {
            font-size: 16px;
            line-height: 24px;
            color: #64748b;
            text-align: center;
            margin-bottom: 32px;
        }

        .product-card {
            border: 1px solid #f1f5f9;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 16px;
            background-color: #fcfdfe;
        }

        .product-table {
            width: 100%;
            border-collapse: collapse;
        }

        .product-img {
            width: 80px;
            height: 80px;
            border-radius: 6px;
            object-fit: cover;
        }

        .product-info {
            padding-left: 20px;
            vertical-align: top;
        }

        .product-name {
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
            margin: 0 0 4px 0;
        }

        .product-variant {
            font-size: 14px;
            color: #94a3b8;
            margin: 0 0 8px 0;
        }

        .product-price {
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
        }

        .footer-action {
            text-align: center;
            padding: 20px 0 40px 0;
        }

        .btn {
            background: linear-gradient(to right, #2563eb, #1d4ed8);
            color: #ffffff !important;
            padding: 16px 32px;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            border-radius: 50px;
            display: inline-block;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.4);
            transition: transform 0.2s ease;
        }

        .footer {
            background-color: #f8fafc;
            padding: 30px;
            text-align: center;
            font-size: 14px;
            color: #94a3b8;
            border-top: 1px solid #f1f5f9;
        }

        .expiry-alert {
            background-color: #fff7ed;
            border-left: 4px solid #f97316;
            padding: 12px 16px;
            margin-bottom: 24px;
            border-radius: 4px;
        }

        .expiry-text {
            color: #9a3412;
            font-size: 14px;
            margin: 0;
            font-weight: 500;
        }

        @media screen and (max-width: 600px) {
            .content { padding: 30px 20px; }
            .product-img { width: 60px; height: 60px; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main">
            <tr>
                <td class="header">
                    @php
                        $logo = get_setting('header_logo');
                    @endphp
                    <img src="{{ uploaded_asset($logo) }}" alt="{{ get_setting('site_name') }}">
                </td>
            </tr>
            <tr>
                <td class="content">
                    <h1 class="title">Don't miss out on your favorites!</h1>
                    <p class="subtitle">We noticed you left some items in your cart. We've reserved them for you, but they won't stay there forever.</p>
                    
                    <div class="expiry-alert">
                        <p class="expiry-text">⚠️ <strong>Note:</strong> Your items will be released back to our inventory in just a few hours. Complete your purchase now to secure them.</p>
                    </div>

                    @foreach ($order->orderDetails as $orderDetail)
                    <div class="product-card">
                        <table class="product-table">
                            <tr>
                                <td width="80">
                                    <img src="{{ uploaded_asset($orderDetail->product->thumbnail_img) }}" class="product-img" alt="{{ $orderDetail->product->getTranslation('name') }}">
                                </td>
                                <td class="product-info">
                                    <h3 class="product-name">{{ $orderDetail->product->getTranslation('name') }}</h3>
                                    @if($orderDetail->variation)
                                        <p class="product-variant">Variation: {{ $orderDetail->variation }}</p>
                                    @endif
                                    <p class="product-price">{{ single_price($orderDetail->price) }} × {{ $orderDetail->quantity }}</p>
                                </td>
                            </tr>
                        </table>
                    </div>
                    @endforeach

                    <div class="footer-action">
                        <a href="{{ route('purchase_history.details', encrypt($order->id)) }}" class="btn">Complete My Order</a>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="footer">
                    <p>&copy; {{ date('Y') }} {{ get_setting('site_name') }}. All rights reserved.</p>
                    <p>If you have any questions, reply to this email or visit our <a href="{{ route('support_ticket.index') }}" style="color: #2563eb; text-decoration: none;">Support Center</a>.</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
