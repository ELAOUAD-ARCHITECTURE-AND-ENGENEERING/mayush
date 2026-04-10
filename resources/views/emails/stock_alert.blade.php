<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap');
        
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: none;
            width: 100% !important;
        }

        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f8fafc;
            padding: 40px 0;
        }

        .main {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 600px;
            border-spacing: 0;
            color: #1e293b;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
        }

        .header {
            background: #ffffff;
            padding: 30px 20px 20px;
            text-align: center;
            border-bottom: 1px solid #f1f5f9;
        }

        .header img {
            max-width: 160px;
            height: auto;
        }

        .hero {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            padding: 40px 20px;
            text-align: center;
            color: white;
        }

        .hero-title {
            font-size: 32px;
            font-weight: 700;
            margin: 0 0 10px 0;
        }
        
        .hero-subtitle {
            font-size: 18px;
            margin: 0;
            opacity: 0.9;
        }

        .content {
            padding: 40px 40px;
        }

        .greeting {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .product-card {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin: 30px 0;
            background-color: #f8fafc;
        }

        .product-img {
            width: 120px;
            height: 120px;
            border-radius: 8px;
            object-fit: cover;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .product-info {
            padding-left: 24px;
            vertical-align: middle;
        }

        .product-name {
            font-size: 20px;
            font-weight: 600;
            color: #0f172a;
            margin: 0 0 8px 0;
        }

        .product-price {
            font-size: 24px;
            font-weight: 700;
            color: #059669;
            margin: 0 0 16px 0;
        }

        .btn {
            background: #0f172a;
            color: #ffffff !important;
            padding: 14px 32px;
            text-decoration: none;
            font-size: 16px;
            font-weight: 600;
            border-radius: 50px;
            display: inline-block;
            transition: all 0.2s ease;
        }

        .btn:hover {
            background: #1e293b;
        }

        .footer {
            background-color: #f1f5f9;
            padding: 30px;
            text-align: center;
            font-size: 14px;
            color: #64748b;
        }

        @media screen and (max-width: 600px) {
            .content { padding: 30px 20px; }
            .product-img { width: 80px; height: 80px; }
            .product-card { padding: 15px; }
            .product-info { padding-left: 15px; }
            .product-name { font-size: 16px; }
            .product-price { font-size: 20px; margin-bottom: 12px; }
            .btn { padding: 10px 24px; font-size: 14px; }
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
                    @if($logo)
                        <img src="{{ uploaded_asset($logo) }}" alt="{{ get_setting('site_name') }}">
                    @else
                        <h2 style="margin:0; color:#0f172a;">{{ get_setting('site_name') }}</h2>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="hero">
                    <h1 class="hero-title">Back In Stock! 🎉</h1>
                    <p class="hero-subtitle">You wished for it, we got it.</p>
                </td>
            </tr>
            <tr>
                <td class="content">
                    <p class="greeting">Hi {{ $user->name }},</p>
                    <p style="font-size: 16px; color: #475569; line-height: 1.6; margin-bottom: 10px;">Good news! An item on your wishlist is finally back in our shelves. Grab it before it sells out again.</p>
                    
                    <div class="product-card">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td width="120">
                                    <img src="{{ uploaded_asset($product->thumbnail_img) }}" class="product-img" alt="{{ $product->getTranslation('name') }}">
                                </td>
                                <td class="product-info">
                                    <h3 class="product-name">{{ $product->getTranslation('name') }}</h3>
                                    <p class="product-price">{{ single_price($product->unit_price) }}</p>
                                    <a href="{{ route('product', $product->slug) }}" class="btn">Shop Now</a>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
            <tr>
                <td class="footer">
                    <p style="margin: 0 0 10px 0;">&copy; {{ date('Y') }} {{ get_setting('site_name') }}. All rights reserved.</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
