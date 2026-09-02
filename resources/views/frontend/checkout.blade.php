@extends('frontend.layouts.app')

@section('content')
    <style>
        :root {
            --premium-gold: {{ get_setting('base_color', '#ff6700') }};
            --brand-vivid: {{ get_setting('base_color', '#ff6700') }};
            --premium-dark: #1a1a1a;
            --premium-soft: #fcfcfc;
            --premium-gray: #f8f9fa;
            --premium-accent: {{ hex2rgba(get_setting('base_color', '#ff6700'), 0.1) }};
        }

        .premium-checkout-container {
            font-family: var(--mayush-font-body);
            background-color: var(--premium-soft);
            min-height: 100vh;
            padding: 40px 0;
        }

        .checkout-wrapper {
            border-radius: 20px;
            background: white;
            margin-left: 1px;
            margin-right: 1px;
        }

        .checkout-main-content {
            background: white;
            padding: 40px;
            border-right: 1px solid #f0f0f0;
            border-top-left-radius: 20px;
            border-bottom-left-radius: 20px;
        }

        .checkout-sidebar {
            background: var(--premium-gray);
            padding: 40px;
            border-top-right-radius: 20px;
            border-bottom-right-radius: 20px;
        }

        .checkout-sidebar .sticky-top {
            z-index: 5 !important;
        }
        
        @media (max-width: 991px) {
            .checkout-main-content {
                border-right: none;
                border-bottom-left-radius: 0;
                border-top-right-radius: 20px;
            }
            .checkout-sidebar {
                border-top-right-radius: 0;
                border-bottom-left-radius: 20px;
            }
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--brand-vivid);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
        }

        .checkout-main-content h3, 
        .checkout-main-content h5,
        .checkout-main-content .nav-tabs .nav-link.active {
            color: var(--brand-vivid) !important;
            font-weight: 700 !important;
        }

        .section-title .step-number {
            width: 28px;
            height: 28px;
            background: var(--brand-vivid);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            margin-right: 12px;
            box-shadow: 0 4px 10px {{ hex2rgba(get_setting('base_color', '#ff6700'), 0.3) }};
        }

        .premium-checkout-card {
            border: 1px solid #eee !important;
            border-radius: 16px !important;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            background: white;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .premium-checkout-card:hover {
            border-color: var(--premium-gold) !important;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        }

        .card-header-premium {
            padding: 24px;
            background: transparent;
            border-bottom: 1px solid #f9f9f9;
        }

        .card-body-premium {
            padding: 24px;
        }

        .action-button-main {
            background: var(--premium-dark);
            color: white;
            border: none;
            padding: 18px 36px;
            border-radius: 12px;
            font-weight: 700;
            width: 100%;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 0.95rem;
        }

        .action-button-main:hover {
            background: #333;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .action-button-main:disabled {
            background: #ccc;
            transform: none;
            box-shadow: none;
        }

        /* Float Labels & Modern Inputs */
        .form-control-premium {
            border: 1px solid #e0e0e0;
            padding: 15px 20px;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control-premium:focus {
            border-color: var(--premium-gold);
            box-shadow: 0 0 0 4px rgba(226, 176, 74, 0.05);
            outline: none;
        }

        /* Order Summary Item */
        .summary-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .summary-item-img {
            width: 64px;
            height: 64px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid #eee;
            margin-right: 15px;
        }

        /* Floating AI Badge */
        .ai-insight-badge {
            background: linear-gradient(135deg, #6e8efb, #a777e3);
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .insight-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            border: 1px solid #edf2f7;
            margin-top: 20px;
        }

        /* Desktop specific layout */
        @media (min-width: 992px) {
            .premium-checkout-container {
                display: flex;
            }
            .checkout-main-content {
                flex: 1;
                max-width: 65%;
            }
            .checkout-sidebar {
                width: 35%;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fade-in {
            animation: fadeInUp 0.6s ease forwards;
        }

        /* Checkout visual system: warm editorial surfaces, navy structure, gold actions */
        .premium-checkout-container {
            --checkout-navy: #12192a;
            --checkout-ink: #263143;
            --checkout-muted: #667085;
            --checkout-canvas: #f7f5f0;
            --checkout-surface: #ffffff;
            --checkout-soft: #fbfaf7;
            --checkout-line: #e7e1d8;
            --checkout-gold: #d6a24e;
            --checkout-sage: #0e7a50;
            --checkout-danger: #b94a48;
            background:
                radial-gradient(circle at 12% 0%, rgba(214, 162, 78, 0.08), transparent 30%),
                var(--checkout-canvas);
            padding: 24px 0 56px;
        }

        .checkout-wrapper {
            overflow: hidden;
            border: 1px solid rgba(18, 25, 42, 0.08);
            border-radius: 22px;
            box-shadow: 0 18px 48px rgba(18, 25, 42, 0.09) !important;
        }

        .checkout-main-content {
            max-width: none;
            padding: 42px 48px 48px !important;
            border-right: 1px solid var(--checkout-line);
            background: var(--checkout-surface);
        }

        .checkout-sidebar {
            width: auto;
            padding: 32px 30px 40px !important;
            background: #f1f2f1;
        }

        .checkout-sidebar-sticky {
            top: 24px !important;
        }

        .checkout-page-header {
            padding-bottom: 22px;
            border-bottom: 1px solid var(--checkout-line);
        }

        .checkout-page-header h1 {
            margin-bottom: 7px !important;
            color: var(--checkout-navy) !important;
            font-family: var(--mayush-font-heading, Georgia, serif);
            font-size: 30px !important;
            font-weight: 700 !important;
            letter-spacing: -0.02em;
        }

        .checkout-page-header p {
            margin-bottom: 0;
            color: var(--checkout-muted) !important;
            font-size: 14px;
        }

        .checkout-section {
            margin-bottom: 34px !important;
        }

        .section-title {
            gap: 11px;
            margin-bottom: 15px;
            color: var(--checkout-navy) !important;
            font-size: 18px;
            font-weight: 750;
            letter-spacing: -0.01em;
        }

        .section-title .step-number {
            display: inline-flex;
            flex: 0 0 32px;
            width: 32px;
            height: 32px;
            margin-right: 0;
            color: var(--checkout-navy);
            background: var(--checkout-gold);
            box-shadow: 0 6px 14px rgba(214, 162, 78, 0.28);
            font-size: 13px;
        }

        .checkout-main-content h3,
        .checkout-main-content h5,
        .checkout-main-content h6 {
            color: var(--checkout-navy) !important;
        }

        .checkout-main-content .text-muted,
        .checkout-sidebar .text-muted {
            color: var(--checkout-muted) !important;
        }

        .checkout-vault-banner {
            margin-bottom: 34px !important;
            border: 1px solid rgba(214, 162, 78, 0.3) !important;
            border-radius: 16px !important;
            background: linear-gradient(135deg, #12192a 0%, #25314a 100%) !important;
            box-shadow: 0 14px 28px rgba(18, 25, 42, 0.15) !important;
        }

        .checkout-vault-banner .card-body {
            padding: 20px 22px !important;
        }

        .checkout-vault-banner h3 {
            color: #fff !important;
        }

        .checkout-vault-banner p {
            color: rgba(255, 255, 255, 0.72) !important;
        }

        .checkout-vault-banner .btn-primary {
            min-height: 44px;
            border-radius: 10px !important;
            color: var(--checkout-navy) !important;
            background: var(--checkout-gold) !important;
            box-shadow: 0 6px 16px rgba(214, 162, 78, 0.25) !important;
        }

        .checkout-vault-banner .btn-primary:hover,
        .checkout-vault-banner .btn-primary:focus-visible {
            color: var(--checkout-navy) !important;
            background: #e3b767 !important;
        }

        /* Address selection */
        #shipping_info .choose-address {
            margin-bottom: 10px;
        }

        #shipping_info .choose-address button {
            color: var(--checkout-navy) !important;
            font-size: 12px !important;
            text-decoration: underline;
            text-decoration-color: rgba(214, 162, 78, 0.7);
            text-underline-offset: 3px;
        }

        #shipping_info .border:not(.border-0) {
            margin-bottom: 16px !important;
            border: 1px solid var(--checkout-line) !important;
            border-radius: 14px;
            background: var(--checkout-soft);
        }

        #shipping_info .aiz-megabox-elem {
            min-height: 82px;
            padding: 17px 20px !important;
            background: transparent !important;
        }

        #shipping_info .aiz-megabox-elem .address-text,
        #shipping_info .aiz-megabox-elem #choose-default,
        #shipping_info .aiz-megabox-elem #choose-default-billing {
            color: var(--checkout-ink);
            font-size: 14px;
            line-height: 1.65;
        }

        #shipping_info #default-address-change-btn,
        #shipping_info #billing-address-change-btn {
            margin-right: 0 !important;
            border: 0;
            border-radius: 9px !important;
            color: #fff !important;
            background: var(--checkout-sage) !important;
            font-size: 12px;
            font-weight: 700;
        }

        #shipping_info .bg-soft-blue {
            min-height: 42px;
            border: 1px solid #eadcc8 !important;
            border-radius: 10px !important;
            background: #fffaf1 !important;
            color: var(--checkout-navy) !important;
        }

        #shipping_info .bg-soft-blue .text-blue,
        #shipping_info .bg-soft-blue .text-blue i {
            color: var(--checkout-navy) !important;
        }

        #shipping_info .form-group.form-check {
            padding-top: 10px !important;
        }

        #shipping_info .aiz-checkbox span.fs-14 {
            color: var(--checkout-muted) !important;
            font-size: 13px !important;
        }

        /* Delivery method cards */
        #delivery_info .card {
            margin-bottom: 16px !important;
            border: 1px solid var(--checkout-line) !important;
            border-radius: 14px !important;
            background: var(--checkout-surface);
            box-shadow: 0 8px 20px rgba(18, 25, 42, 0.04);
        }

        #delivery_info .card-header {
            padding: 17px 20px !important;
            border-bottom: 1px solid var(--checkout-line) !important;
            background: var(--checkout-soft);
        }

        #delivery_info .card-header h5 {
            margin-bottom: 0 !important;
            color: var(--checkout-navy) !important;
            font-size: 14px !important;
            font-weight: 750 !important;
        }

        #delivery_info .card-body {
            padding: 18px 20px !important;
        }

        #delivery_info .list-group-item {
            padding: 4px 0 14px !important;
        }

        #delivery_info .list-group-item img {
            width: 68px !important;
            height: 68px !important;
            border: 1px solid var(--checkout-line);
            border-radius: 10px;
            background: var(--checkout-soft);
        }

        #delivery_info .list-group-item .text-dark {
            color: var(--checkout-ink) !important;
            line-height: 1.55;
        }

        #delivery_info h6 {
            margin: 4px 0 12px !important;
            color: var(--checkout-navy) !important;
            font-family: var(--mayush-font-heading, Georgia, serif);
            font-size: 15px !important;
        }

        #delivery_info .aiz-megabox-elem {
            min-height: 58px;
            padding: 13px 14px !important;
            border: 1px solid var(--checkout-line) !important;
            border-radius: 10px !important;
            background: #fff;
            transition: border-color 180ms ease, background-color 180ms ease, box-shadow 180ms ease;
        }

        #delivery_info .aiz-megabox-elem:hover,
        #delivery_info .aiz-megabox input:checked + .aiz-megabox-elem {
            border-color: var(--checkout-gold) !important;
            background: #fffaf1 !important;
            box-shadow: 0 4px 12px rgba(214, 162, 78, 0.12);
        }

        #delivery_info .aiz-megabox-elem .fw-600,
        #delivery_info .aiz-megabox-elem .fw-700 {
            color: var(--checkout-ink);
            font-size: 13px;
            line-height: 1.45;
        }

        #delivery_info .pickup_point_id select,
        #delivery_info .carrier_id select {
            min-height: 44px;
            border: 1px solid var(--checkout-line);
            border-radius: 9px !important;
        }

        /* Payment choices */
        #payment_info > div:first-child,
        #payment_info > div:first-child + div {
            color: var(--checkout-ink);
        }

        #payment_info h3 {
            margin-bottom: 12px !important;
            color: var(--checkout-navy) !important;
            font-family: var(--mayush-font-heading, Georgia, serif);
            font-size: 15px !important;
            font-weight: 700 !important;
        }

        #payment_info textarea,
        #payment_info .form-control {
            min-height: 48px;
            padding: 12px 14px;
            border: 1px solid var(--checkout-line);
            border-radius: 10px !important;
            color: var(--checkout-ink);
            background: #fff;
        }

        #payment_info textarea {
            min-height: 118px;
            resize: vertical;
        }

        #payment_info textarea:focus,
        #payment_info .form-control:focus {
            border-color: var(--checkout-gold);
            box-shadow: 0 0 0 3px rgba(214, 162, 78, 0.14);
        }

        #payment_info .aiz-megabox {
            margin-bottom: 10px !important;
        }

        #payment_info .aiz-megabox-elem {
            min-height: 72px;
            padding: 13px 16px !important;
            border: 1px solid var(--checkout-line) !important;
            border-radius: 12px !important;
            background: #fff;
            transition: border-color 180ms ease, background-color 180ms ease, box-shadow 180ms ease;
        }

        #payment_info .aiz-megabox-elem:hover,
        #payment_info .aiz-megabox input:checked + .aiz-megabox-elem {
            border-color: var(--checkout-gold) !important;
            background: #fffaf1 !important;
            box-shadow: 0 5px 14px rgba(214, 162, 78, 0.12);
        }

        #payment_info .aiz-megabox-elem > span:first-child {
            color: var(--checkout-ink);
            line-height: 1.4;
        }

        #payment_info .aiz-megabox-elem img {
            max-width: 58px;
            max-height: 36px;
        }

        /* Completion and primary action */
        .checkout-completion-card {
            margin-top: 6px;
            padding: 22px 24px !important;
            border: 1px solid #eadfcd !important;
            border-radius: 16px !important;
            background: #fbf7ef !important;
        }

        .checkout-completion-card .aiz-checkbox {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            color: var(--checkout-muted);
            line-height: 1.65;
        }

        .checkout-completion-card a {
            color: var(--checkout-navy) !important;
            text-decoration: underline;
            text-decoration-color: rgba(214, 162, 78, 0.75);
            text-underline-offset: 3px;
        }

        .checkout-completion-actions {
            gap: 20px;
            margin-top: 20px;
        }

        .checkout-completion-actions > a {
            white-space: nowrap;
            color: var(--checkout-navy) !important;
        }

        .action-button-main {
            min-height: 48px;
            padding: 13px 24px;
            border-radius: 10px;
            color: #fff;
            background: var(--checkout-navy);
            box-shadow: 0 8px 18px rgba(18, 25, 42, 0.14);
            font-size: 13px;
            letter-spacing: 0.04em;
        }

        .action-button-main:hover,
        .action-button-main:focus-visible {
            color: #fff;
            background: #25314a;
            box-shadow: 0 10px 22px rgba(18, 25, 42, 0.2);
        }

        .action-button-main:disabled {
            color: #7b7d82;
            background: #d7d5d0;
            box-shadow: none;
            cursor: not-allowed;
            opacity: 1;
        }

        /* Summary and sidebar */
        .checkout-sidebar #cart_summary > .z-3 > .card {
            overflow: hidden;
            border: 1px solid var(--checkout-line) !important;
            border-radius: 16px !important;
            background: #fff;
            box-shadow: 0 8px 18px rgba(18, 25, 42, 0.05);
        }

        .checkout-sidebar #cart_summary .card-header {
            padding: 20px 20px 8px !important;
        }

        .checkout-sidebar #cart_summary .card-header h3 {
            color: var(--checkout-navy) !important;
            font-family: var(--mayush-font-heading, Georgia, serif);
            font-size: 17px !important;
        }

        .checkout-sidebar #cart_summary .card-body {
            padding: 10px 20px 20px !important;
        }

        .checkout-sidebar #cart_summary .bg-primary,
        .checkout-sidebar #cart_summary .bg-secondary-base {
            min-height: 48px;
            padding: 11px 12px !important;
            border-radius: 9px;
        }

        .checkout-sidebar #cart_summary .bg-primary {
            background: var(--checkout-navy) !important;
        }

        .checkout-sidebar #cart_summary .bg-secondary-base {
            background: var(--checkout-sage) !important;
        }

        .checkout-sidebar #cart_summary table {
            margin: 20px 0 !important;
        }

        .checkout-sidebar #cart_summary table th,
        .checkout-sidebar #cart_summary table td {
            padding-bottom: 10px !important;
            color: var(--checkout-ink) !important;
            line-height: 1.45;
        }

        .checkout-sidebar #cart_summary .cart-total th,
        .checkout-sidebar #cart_summary .cart-total td {
            padding-top: 16px !important;
            border-top: 1px solid var(--checkout-line) !important;
        }

        .checkout-sidebar #cart_summary .cart-total td,
        .checkout-sidebar #cart_summary .text-primary {
            color: var(--premium-gold) !important;
        }

        .checkout-sidebar #cart_summary .suggestion-item {
            padding: 10px !important;
            border: 1px solid var(--checkout-line) !important;
            border-radius: 12px !important;
            background: #fff;
        }

        .checkout-sidebar #cart_summary .suggestion-item:hover {
            border-color: rgba(214, 162, 78, 0.7) !important;
            background: #fffaf1 !important;
        }

        .checkout-sidebar #cart_summary .suggestion-item .btn-soft-primary {
            color: var(--checkout-navy) !important;
            background: #fff1d8 !important;
        }

        .checkout-sidebar #cart_summary small {
            color: var(--checkout-muted);
        }

        .ai-insight-badge {
            margin-bottom: 13px;
            padding: 6px 10px;
            border-radius: 8px;
            color: #fff;
            background: var(--checkout-navy);
            font-size: 10px;
            letter-spacing: 0.04em;
        }

        .ai-insight-badge i {
            color: var(--checkout-gold);
        }

        .insight-card {
            margin-top: 18px !important;
            padding: 21px !important;
            border: 1px solid rgba(18, 25, 42, 0.08);
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 8px 18px rgba(18, 25, 42, 0.04);
        }

        .insight-card h4 {
            color: var(--checkout-navy) !important;
            font-family: var(--mayush-font-heading, Georgia, serif);
            font-size: 17px !important;
        }

        .insight-card ul {
            margin-top: 14px;
        }

        .insight-card li {
            gap: 9px;
            margin-bottom: 15px !important;
            color: var(--checkout-muted) !important;
            line-height: 1.55;
        }

        .insight-card li:last-child {
            margin-bottom: 0 !important;
        }

        .insight-card li i {
            flex: 0 0 15px;
            margin-right: 0 !important;
        }

        .insight-card li strong {
            color: var(--checkout-navy);
        }

        .checkout-security-card {
            padding: 13px 16px !important;
            border: 1px dashed #d7d0c5 !important;
            border-radius: 12px !important;
            background: rgba(255, 255, 255, 0.5);
        }

        .checkout-security-card p {
            color: var(--checkout-muted) !important;
            line-height: 1.45;
        }

        @media (min-width: 992px) {
            .checkout-main-content {
                flex: 0 0 67%;
                max-width: 67%;
            }

            .checkout-sidebar {
                flex: 0 0 33%;
                max-width: 33%;
            }
        }

        @media (max-width: 991px) {
            .premium-checkout-container {
                padding-top: 16px;
            }

            .checkout-sidebar {
                order: -1;
                border-bottom: 1px solid var(--checkout-line);
            }

            .checkout-main-content {
                order: 2;
                border-right: 0;
            }

            .checkout-sidebar-sticky {
                position: static !important;
            }
        }

        @media (max-width: 767px) {
            .premium-checkout-container {
                padding: 10px 0 28px;
            }

            .checkout-wrapper {
                border-radius: 16px;
            }

            .checkout-main-content,
            .checkout-sidebar {
                padding: 24px 18px !important;
            }

            .checkout-page-header {
                padding-bottom: 18px;
            }

            .checkout-page-header h1 {
                font-size: 26px !important;
            }

            .checkout-section {
                margin-bottom: 28px !important;
            }

            .section-title {
                font-size: 17px;
            }

            .checkout-completion-actions {
                align-items: stretch !important;
                flex-direction: column;
                gap: 16px;
            }

            .checkout-completion-actions > a,
            .checkout-completion-actions #submitOrderBtn {
                width: 100% !important;
                min-width: 0 !important;
                text-align: center;
            }

            #shipping_info .d-flex.justify-content-between,
            #shipping_info .d-flex.flex-wrap.align-items-center.justify-content-between {
                align-items: stretch !important;
                flex-direction: column;
                gap: 12px;
            }

            #shipping_info .bg-soft-blue {
                width: 100%;
            }

            #delivery_info .card-body,
            #delivery_info .card-header {
                padding-right: 14px !important;
                padding-left: 14px !important;
            }

            #delivery_info .row.gutters-16 > [class*="col-"] {
                width: 100%;
                flex: 0 0 100%;
                max-width: 100%;
            }

            #payment_info .aiz-megabox-elem {
                min-height: 64px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .premium-checkout-container *,
            .premium-checkout-container *::before,
            .premium-checkout-container *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                scroll-behavior: auto !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* Validation GSAP */
        .premium-input-error {
            border-color: #e74c3c !important;
            animation: shake 0.4s cubic-bezier(.36,.07,.19,.97) both;
        }

        @keyframes shake {
            10%, 90% { transform: translate3d(-1px, 0, 0); }
            20%, 80% { transform: translate3d(2px, 0, 0); }
            30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
            40%, 60% { transform: translate3d(4px, 0, 0); }
        }

        .error-message {
            color: #e74c3c;
            font-size: 0.85rem;
            margin-top: 6px;
            display: none;
        }

        /* Loading Spinner Overlay */
        #loading-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(255, 255, 255, 0.9);
            z-index: 9999;
            display: none;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            backdrop-filter: blur(5px);
        }
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid var(--premium-accent);
            border-top: 4px solid var(--premium-gold);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Checkout account modal: compact split-panel auth experience */
        .checkout-auth-modal .modal-dialog {
            width: calc(100% - 32px);
            max-width: 920px;
            margin: 16px auto;
        }
        .checkout-auth-card {
            display: grid;
            grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.1fr);
            min-height: 560px;
            overflow: hidden;
            border: 1px solid rgba(214, 162, 78, 0.24) !important;
            border-radius: 24px !important;
            background: #fff;
            box-shadow: 0 24px 70px rgba(18, 25, 42, 0.24);
        }
        .checkout-auth-aside {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 560px;
            padding: 32px;
            overflow: hidden;
            color: #fff;
            background:
                radial-gradient(circle at 85% 12%, rgba(214, 162, 78, 0.28), transparent 32%),
                linear-gradient(145deg, #12192a 0%, #1c263d 58%, #3a2d23 100%);
        }
        .checkout-auth-aside::after {
            position: absolute;
            right: -64px;
            bottom: -90px;
            width: 250px;
            height: 250px;
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 50%;
            box-shadow: 0 0 0 24px rgba(255, 255, 255, 0.03), 0 0 0 48px rgba(255, 255, 255, 0.02);
            content: '';
        }
        .checkout-auth-brand {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            min-height: 32px;
            width: fit-content;
            padding: 6px 10px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.92);
        }
        .checkout-auth-brand img {
            width: auto;
            max-width: 132px;
            height: 34px;
            object-fit: contain;
        }
        .checkout-auth-product-card {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-self: center;
            width: min(100%, 270px);
            margin: 10px 0 18px;
            padding: 16px;
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 18px 36px rgba(0, 0, 0, 0.16);
            backdrop-filter: blur(10px);
        }
        .checkout-auth-kicker,
        .checkout-auth-eyebrow {
            display: block;
            color: var(--premium-gold);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }
        .checkout-auth-product-image {
            width: 100%;
            height: 174px;
            margin: 10px 0 14px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.92);
            object-fit: cover;
        }
        .checkout-auth-product-card strong {
            color: #fff;
            font-size: 15px;
            line-height: 1.4;
        }
        .checkout-auth-product-note {
            margin-top: 6px;
            color: rgba(255, 255, 255, 0.68);
            font-size: 12px;
            line-height: 1.4;
        }
        .checkout-auth-aside-copy {
            position: relative;
            z-index: 1;
            max-width: 300px;
        }
        .checkout-auth-aside-copy h3 {
            margin: 0 0 8px;
            color: #fff;
            font-family: var(--mayush-font-heading, Georgia, serif);
            font-size: 24px;
            line-height: 1.2;
        }
        .checkout-auth-aside-copy p {
            margin: 0;
            color: rgba(255, 255, 255, 0.72);
            font-size: 13px;
            line-height: 1.6;
        }
        .checkout-auth-benefits {
            position: relative;
            z-index: 1;
            display: flex;
            flex-wrap: wrap;
            gap: 8px 14px;
            margin-top: 20px;
        }
        .checkout-auth-benefits span {
            color: rgba(255, 255, 255, 0.84);
            font-size: 11px;
        }
        .checkout-auth-benefits i {
            margin-right: 5px;
            color: var(--premium-gold);
        }
        .checkout-auth-main {
            position: relative;
            max-height: min(640px, calc(100vh - 32px));
            padding: 44px 46px 32px;
            overflow-y: auto;
            background: #fff;
        }
        .checkout-auth-close {
            position: absolute;
            top: 16px;
            right: 16px;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            padding: 0;
            border: 1px solid #e8e5df;
            border-radius: 50%;
            color: #475569;
            background: #fff;
            cursor: pointer;
            transition: color 180ms ease, border-color 180ms ease, background-color 180ms ease;
        }
        .checkout-auth-close:hover,
        .checkout-auth-close:focus-visible {
            border-color: var(--premium-gold);
            color: var(--premium-dark);
            background: #fffaf2;
            outline: none;
        }
        .checkout-auth-close svg {
            width: 20px;
            height: 20px;
        }
        .checkout-auth-main-header {
            max-width: 410px;
            margin-bottom: 20px;
        }
        .checkout-auth-main-header h2 {
            margin: 6px 0 7px;
            color: var(--premium-dark);
            font-family: var(--mayush-font-heading, Georgia, serif);
            font-size: 26px;
            line-height: 1.2;
        }
        .checkout-auth-main-header p {
            margin: 0;
            color: #475569;
            font-size: 13px;
            line-height: 1.55;
        }
        .checkout-auth-tabs {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 4px;
            margin-bottom: 22px;
            padding: 4px;
            border-radius: 12px;
            background: #f5f3ef;
        }
        .checkout-auth-tab {
            display: block;
            padding: 10px 12px;
            border-radius: 9px;
            color: #64748b;
            font-size: 13px;
            font-weight: 700;
            text-align: center;
            transition: color 180ms ease, background-color 180ms ease, box-shadow 180ms ease;
        }
        .checkout-auth-tab:hover,
        .checkout-auth-tab:focus-visible {
            color: var(--premium-dark);
            text-decoration: none;
        }
        .checkout-auth-tab.active {
            color: var(--premium-dark);
            background: #fff;
            box-shadow: 0 2px 8px rgba(18, 25, 42, 0.08);
        }
        .checkout-auth-progress {
            display: flex;
            gap: 18px;
            margin: 0 0 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid #eeeae3;
        }
        .checkout-auth-progress span {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: #94a3b8;
            font-size: 12px;
            font-weight: 700;
        }
        .checkout-auth-progress span.active {
            color: var(--premium-dark);
        }
        .checkout-auth-progress b {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            color: #64748b;
            background: #edf0f2;
            font-size: 11px;
        }
        .checkout-auth-progress span.active b {
            color: #fff;
            background: var(--premium-gold);
        }
        .checkout-auth-step-panel.d-none {
            display: none !important;
        }
        .checkout-auth-modal .form-group {
            margin-bottom: 14px;
        }
        .checkout-auth-modal .form-control,
        .checkout-auth-modal .bootstrap-select > .dropdown-toggle {
            min-height: 44px;
            padding: 10px 13px;
            border: 1px solid #dedbd5;
            border-radius: 9px !important;
            color: #1f2937;
            background: #fff;
            box-shadow: none !important;
            transition: border-color 180ms ease, box-shadow 180ms ease, background-color 180ms ease;
        }
        .checkout-auth-modal .form-control:focus,
        .checkout-auth-modal .bootstrap-select > .dropdown-toggle:focus {
            border-color: var(--premium-gold);
            background: #fff;
            box-shadow: 0 0 0 3px var(--premium-accent) !important;
        }
        .checkout-auth-modal label {
            margin-bottom: 6px;
            color: #334155;
            font-size: 12px;
            font-weight: 700;
        }
        .checkout-auth-methods {
            display: flex;
            flex-wrap: wrap;
            gap: 10px 18px;
            min-height: 28px;
        }
        .checkout-auth-methods .aiz-radio {
            margin: 0;
        }
        .checkout-auth-step-panel hr {
            margin: 18px 0 16px;
            border-color: #eeeae3;
        }
        .checkout-auth-step-panel h6 {
            margin-bottom: 14px !important;
            color: var(--premium-dark) !important;
            font-family: var(--mayush-font-heading, Georgia, serif);
            font-size: 16px;
        }
        .checkout-auth-modal .btn-primary {
            min-height: 44px;
            padding: 10px 18px;
            border: none;
            border-radius: 9px;
            color: #fff;
            background: var(--premium-gold);
            font-weight: 700;
            transition: background-color 180ms ease, box-shadow 180ms ease;
        }
        .checkout-auth-modal .btn-primary:hover,
        .checkout-auth-modal .btn-primary:focus-visible {
            color: #fff;
            background: #bc6d2d;
            box-shadow: 0 4px 12px var(--premium-accent);
            outline: none;
        }
        .checkout-auth-step-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-top: 18px;
        }
        .checkout-auth-step-actions .btn-primary {
            flex: 1;
        }
        .checkout-auth-back {
            min-height: 44px;
            padding: 10px 0;
            color: #64748b;
            font-size: 13px;
            font-weight: 700;
        }
        .checkout-auth-back:hover,
        .checkout-auth-back:focus-visible {
            color: var(--premium-dark);
            text-decoration: none;
        }
        .checkout-auth-secondary-link {
            display: block;
            margin-top: 14px;
            color: #64748b;
            font-size: 12px;
            text-align: center;
        }
        .checkout-auth-secondary-link:hover,
        .checkout-auth-secondary-link:focus-visible {
            color: var(--premium-dark);
        }
        .checkout-auth-modal .checkout-account-errors {
            margin-bottom: 16px;
            padding: 10px 12px;
            font-size: 12px;
        }
        @media (max-width: 767px) {
            .checkout-auth-modal .modal-dialog {
                width: calc(100% - 20px);
                margin: 10px auto;
            }
            .checkout-auth-card {
                display: block;
                min-height: auto;
                border-radius: 18px !important;
            }
            .checkout-auth-aside {
                display: none;
            }
            .checkout-auth-main {
                max-height: calc(100vh - 20px);
                padding: 34px 20px 22px;
            }
            .checkout-auth-main-header h2 {
                padding-right: 38px;
                font-size: 23px;
            }
            .checkout-auth-modal .form-control,
            .checkout-auth-modal .bootstrap-select > .dropdown-toggle {
                min-height: 46px;
                font-size: 16px;
            }
            .checkout-auth-modal .row.gutters-10 > [class*="col-"] {
                margin-bottom: 0;
            }
            .checkout-auth-step-actions {
                align-items: stretch;
                flex-direction: column-reverse;
            }
            .checkout-auth-back {
                width: 100%;
            }
        }
        @media (prefers-reduced-motion: reduce) {
            .checkout-auth-modal *,
            .checkout-auth-modal *::before,
            .checkout-auth-modal *::after {
                transition-duration: 0.01ms !important;
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                scroll-behavior: auto !important;
            }
        }
    </style>
    <section class="premium-checkout-container">
        <!-- Loading Overlay -->
        <div id="loading-overlay">
            <div class="spinner"></div>
            <h4 class="fs-18 fw-700 text-dark">{{ translate('Processing your elegant order...') }}</h4>
            <p class="fs-14 text-muted">{{ translate('Please wait while we secure your transaction') }}</p>
        </div>
        
        <div class="container">
            <div class="row shadow-lg checkout-wrapper">
                <!-- Main Checkout Flow -->
                <div class="col-lg-8 checkout-main-content animate-fade-in p-lg-5">
                    <div class="checkout-page-header mb-5">
                <h1 class="fs-24 fw-800 text-dark mb-1">{{ translate('Checkout') }}</h1>
                <p class="text-muted">{{ translate('Review your order and choose your preferences.') }}</p>
            </div>

            @php
                $isEligible = \App\Services\PaymentVaultService::isEligible();
                $preferredMethod = \App\Services\PaymentVaultService::getPreferredPaymentMethod();
            @endphp

            @if ($isEligible)
                <!-- 1-Click Purchase Banner -->
                <div class="card checkout-vault-banner border-0 mb-5 overflow-hidden" style="background: linear-gradient(135deg, #1a1a1a 0%, #3d3d3d 100%); border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                    <div class="card-body p-4 d-flex flex-column flex-md-row align-items-center justify-content-between">
                        <div class="text-white mb-3 mb-md-0">
                            <h3 class="fs-18 fw-700 mb-1 d-flex align-items-center">
                                <i class="las la-unlock-alt mr-2 text-warning"></i>
                                {{ translate('Elegant Vault Active') }}
                            </h3>
                            <p class="fs-14 opacity-70 mb-0">{{ translate('Purchase with your saved preferences in one click.') }}</p>
                        </div>
                        <div class="d-flex align-items-center">
                            <form action="{{ route('checkout.fast_purchase') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary px-4 py-3 fw-700 rounded-pill d-flex align-items-center" style="background: var(--premium-gold); border: none; color: #000; box-shadow: 0 4px 15px rgba(226, 176, 74, 0.3);">
                                    <i class="las la-bolt fs-20 mr-2"></i>
                                    {{ translate('1-CLICK PURCHASE') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <form class="form-default" data-toggle="validator" action="{{ route('payment.checkout') }}" role="form" method="POST" id="checkout-form">
                @csrf
                
                <!-- 1. Shipping Information -->
                <div class="checkout-section mb-5">
                    <div class="section-title">
                        <span class="step-number">1</span>
                        {{ translate('Shipping Information') }}
                    </div>
                    <div id="shipping_info">
                        @include('frontend.partials.cart.shipping_info', ['address_id' => $address_id])
                    </div>
                </div>

                <!-- 2. Delivery Method -->
                <div class="checkout-section mb-5">
                    <div class="section-title">
                        <span class="step-number">2</span>
                        {{ translate('Delivery Method') }}
                    </div>
                    <div id="delivery_info">
                        @include('frontend.partials.cart.delivery_info', ['carts' => $carts, 'carrier_list' => $carrier_list, 'shipping_info' => $shipping_info])
                    </div>
                </div>

                <!-- 3. Payment Details -->
                <div class="checkout-section mb-5">
                    <div class="section-title">
                        <span class="step-number">3</span>
                        {{ translate('Payment Details') }}
                    </div>
                    <div id="payment_info">
                        @include('frontend.partials.cart.payment_info', ['carts' => $carts, 'total' => $total])
                    </div>
                </div>

                <!-- Agreement & Completion -->
                <div class="premium-checkout-card checkout-completion-card p-4 bg-light border-0">
                    <div class="fs-14 mb-4">
                        <label class="aiz-checkbox">
                            <input type="checkbox" required id="agree_checkbox" onchange="stepCompletionPaymentInfo()">
                            <span class="aiz-square-check"></span>
                            <span>{{ translate('I agree to the') }}</span>
                        </label>
                        <a href="{{ route('terms') }}" class="fw-700 text-dark">{{ translate('terms and conditions') }}</a>,
                        <a href="{{ route('returnpolicy') }}" class="fw-700 text-dark">{{ translate('return policy') }}</a> &
                        <a href="{{ route('privacypolicy') }}" class="fw-700 text-dark">{{ translate('privacy policy') }}</a>
                    </div>

                    <div class="checkout-completion-actions d-flex align-items-center justify-content-between">
                        <a href="{{ route('home') }}" class="btn btn-link text-dark fw-700 p-0">
                            <i class="las la-arrow-left"></i> {{ translate('Back to Store') }}
                        </a>
                        <button type="button" onclick="submitOrder(this)" id="submitOrderBtn" class="action-button-main" style="width: auto; min-width: 250px;">
                            {{ translate('Complete Order') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Sticky Sidebar -->
        <div class="col-lg-4 checkout-sidebar p-lg-5">
            <div class="sticky-top checkout-sidebar-sticky" style="top: 40px;">
                <!-- Order Summary Partial -->
                <div id="cart_summary">
                    @include('frontend.partials.cart.cart_summary', ['proceed' => 0, 'carts' => $carts])
                </div>

                @php
                    $cart_total = 0;
                    $cart_count = count($carts);
                    foreach ($carts as $key => $cartItem) {
                        $cart_total += ($cartItem->price + $cartItem->tax) * $cartItem->quantity;
                    }
                    $white_glove_threshold = 5000;
                @endphp
                <!-- AI Design Insights Section -->
                <div class="insight-card mt-4 box-shadow-sm">
                    <div class="ai-insight-badge">
                        <i class="las la-magic mr-1"></i> {{ translate('AI DESIGN COMPANION') }}
                    </div>
                    <h4 class="fs-16 fw-700 text-dark mb-2">{{ translate('Premium Insights') }}</h4>
                    <ul class="list-unstyled fs-13 text-muted mb-0">
                        <li class="mb-3 d-flex">
                            <i class="las la-check-circle text-success mr-2 mt-1"></i>
                            @if($cart_count > 1)
                            <span>{{ translate('Your ') }} {{ $cart_count }} {{ translate(' selections share a cohesive aesthetic. Excellent pairing.') }}</span>
                            @else
                            <span>{{ translate('A perfect choice. Consider pairing this with a matching accessory to complete the look.') }}</span>
                            @endif
                        </li>
                        <li class="mb-3 d-flex">
                            <i class="las la-truck text-primary mr-2 mt-1"></i>
                            @if($cart_total >= $white_glove_threshold)
                            <span>{{ translate('Your order qualifies for our exclusive "White Glove" installation service!') }}</span>
                            @else
                            <span>{{ translate('You are only ') }} <strong>{{ single_price($white_glove_threshold - $cart_total) }}</strong> {{ translate(' away from unlocking our "White Glove" installation service.') }}</span>
                            @endif
                        </li>
                        <li class="d-flex">
                            <i class="las la-shield-alt text-info mr-2 mt-1"></i>
                            <span>{{ translate('Each piece is inspected by our quality artisans before shipping.') }}</span>
                        </li>
                    </ul>
                </div>

                <div class="checkout-security-card mt-4 p-3 rounded-lg border border-dashed text-center">
                    <p class="fs-12 text-muted mb-0">
                        <i class="las la-lock mr-1"></i> {{ translate('Secure 256-bit SSL Encrypted Connection') }}
                    </p>
                </div>
            </div>
        </div>
        
            </div>
        </div>
    </section>
@endsection

@section('modal')
    @include('frontend.partials.checkout_account_modal')
    <!-- Address Modal -->
    @if(Auth::check())
        @include('frontend.partials.address.address_modal')
        @include('frontend.partials.address.billing_address_modal')
    @endif
@endsection

@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js" integrity="sha384-d+vyQ0dYcymoP8ndq2hW7FGC50nqGdXUEgoOUGxbbkAJwZqL7h+jKN0GGgn9hFDS" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js" integrity="sha384-poC0r6usQOX2Ayt/VGA+t81H6V3iN9L+Irz9iO8o+s0X20tLpzc9DOOtnKxhaQSE" crossorigin="anonymous"></script>
    <script type="text/javascript">
        gsap.registerPlugin(ScrollTrigger);
        var carrierCount = 0;
        var checkoutIsAuthenticated = @json(Auth::check());
        var checkoutNeedsAddress = @json(Auth::check() && !Auth::user()->addresses()->exists());

        $(document).ready(function() {
            // Initial reveal of main content and sections
            gsap.from(".checkout-main-content > div", {
                opacity: 0,
                y: 30,
                duration: 0.8,
                stagger: 0.1,
                ease: "expo.out"
            });

            // Handle payment options toggle
            $(".online_payment, .offline_payment_option").click(function() {
                $('#manual_payment_description').parent().addClass('d-none');
                stepCompletionPaymentInfo();
            });
            
            toggleManualPaymentData($('input[name=payment_option]:checked').data('id'));

            // Initial state checks
            carrierCount = parseInt(document.getElementById('carrierCount')?.value || 0);
            checkCarrerShippingInfo();
            stepCompletionShippingInfo();
            stepCompletionDeliveryInfo();
            stepCompletionPaymentInfo();

            bindCheckoutAccountModal();
            if (!checkoutIsAuthenticated || checkoutNeedsAddress) {
                openCheckoutAccountModal();
            }
        });

        // Minimum order settings
        var minimum_order_amount_check = {{ get_setting('minimum_order_amount_check') == 1 ? 1 : 0 }};
        var minimum_order_amount = {{ get_setting('minimum_order_amount_check') == 1 ? get_setting('minimum_order_amount') : 0 }};

        function submitOrder(el) {
            let $btn = $(el);
            $btn.prop('disabled', true).addClass('btn-loading');
            
            if ($('#agree_checkbox').is(":checked")) {
                var subtotal = parseFloat($('#sub_total').val()) || 0;
                
                if (minimum_order_amount_check && subtotal < minimum_order_amount) {
                    AIZ.plugins.notify('danger', '{{ translate('Your order amount is less than the minimum order amount') }}');
                    $btn.prop('disabled', false).removeClass('btn-loading');
                } else {
                    var offline_payment_active = '{{ addon_is_activated('offline_payment') }}';
                    var isOfflineChecked = $('.offline_payment_option').is(":checked");
                    
                    if (offline_payment_active == '1' && isOfflineChecked && $('#trx_id').val() == '') {
                        AIZ.plugins.notify('danger', '{{ translate('You need to provide a Transaction ID') }}');
                        $btn.prop('disabled', false).removeClass('btn-loading');
                    } else {
                        // Validate sections
                        var isOkShipping = stepCompletionShippingInfo();
                        var isOkDelivery = stepCompletionDeliveryInfo();
                        var isOkPayment = stepCompletionPaymentInfo();

                        if(isOkShipping && isOkDelivery && isOkPayment) {
                            // Premium GSAP feedback
                            gsap.to(el, { scale: 0.95, duration: 0.1, yoyo: true, repeat: 1 });
                            $('#loading-overlay').css('display', 'flex');
                            gsap.fromTo('#loading-overlay', {opacity: 0}, {opacity: 1, duration: 0.3});

                            // AJAX Submission
                            let formData = new FormData($('#checkout-form')[0]);
                            $.ajax({
                                url: $('#checkout-form').attr('action'),
                                type: 'POST',
                                data: formData,
                                processData: false,
                                contentType: false,
                                success: function(data) {
                                    if (data.status === 'success') {
                                        // Final "Order Secured" Animation
                                        gsap.to('#loading-overlay h4', { 
                                            text: '{{ translate("Elegance Secured.") }}', 
                                            duration: 0.5, 
                                            color: 'var(--premium-gold)' 
                                        });
                                        
                                        setTimeout(() => {
                                            if (data.type === 'redirect') {
                                                window.location.href = data.url;
                                            } else if (data.type === 'html') {
                                                // Inject hidden form (like CMI) and submit
                                                $('body').append('<div id="temp-payment-form" style="display:none">' + data.html + '</div>');
                                                // CMI form has name="cmi_form" and submits on body onload, 
                                                // but since we inject it, we trigger it manually
                                                if ($('form[name="cmi_form"]').length) {
                                                    document.cmi_form.submit();
                                                } else {
                                                    $('#temp-payment-form form').submit();
                                                }
                                            }
                                        }, 1000);
                                    } else {
                                        $('#loading-overlay').fadeOut();
                                        AIZ.plugins.notify('danger', data.message || '{{ translate("An error occurred.") }}');
                                        $btn.prop('disabled', false).removeClass('btn-loading');
                                    }
                                },
                                error: function(xhr) {
                                    $('#loading-overlay').fadeOut();
                                    let msg = '{{ translate("Something went wrong. Please try again.") }}';
                                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                                    AIZ.plugins.notify('danger', msg);
                                    $btn.prop('disabled', false).removeClass('btn-loading');
                                }
                            });
                        } else {
                            AIZ.plugins.notify('danger', '{{ translate("Please complete all required fields.") }}');
                            $btn.prop('disabled', false).removeClass('btn-loading');
                            
                            // Highlight first missing field
                            $('#checkout-form [required]').each(function (i, el) {
                                let $this = $(this);
                                if (!$this.val() || ($this.is(':checkbox') && !$this.is(':checked')) || ($this.is(':radio') && $('input[name="'+$this.attr('name')+'"]:checked').length === 0)) {
                                    let targetParent = $this.closest('.form-control, .aiz-checkbox, .aiz-radio');
                                    if(targetParent.length === 0) targetParent = $this;
                                    
                                    targetParent.addClass('premium-input-error');
                                    setTimeout(() => { targetParent.removeClass('premium-input-error'); }, 400);
                                    
                                    $this.focus();
                                    $('html, body').animate({ scrollTop: $this.offset().top - 100 }, 500);
                                    return false;
                                }
                            });
                        }
                    }
                }
            } else {
                AIZ.plugins.notify('danger', '{{ translate('Please agree to our terms and conditions.') }}');
                $btn.prop('disabled', false).removeClass('btn-loading');
            }
        }

        function toggleManualPaymentData(id) {
            if (id && id !== 'undefined') {
                $('#manual_payment_description').parent().removeClass('d-none');
                $('#manual_payment_description').html($('#manual_payment_info_' + id).html());
            }
        }

        function updateDeliveryAddress(id, city_id = 0, area_id = 0) {
            $('.checkout-main-content').css('opacity', '0.6');
            $.post('{{ route('checkout.updateDeliveryAddress') }}', {
                _token: AIZ.data.csrf,
                address_id: id,
                city_id: city_id,
                area_id: area_id
            }, function(data) {
                $('#delivery_info').html(data.delivery_info);
                $('#cart_summary').html(data.cart_summary);
                $('.checkout-main-content').css('opacity', '1');
                carrierCount = data.carrier_count;
                checkCarrerShippingInfo();
                gsap.from("#delivery_info", { opacity: 0, y: 10, duration: 0.4 });
            });
            AIZ.plugins.bootstrapSelect("refresh");
        }

        function updateBillingAddress(id) {
            $.post('{{ route('checkout.updateBillingAddress') }}', {
                _token: AIZ.data.csrf,
                address_id: id
            });
        }

        function stepCompletionShippingInfo() {
            return $('input[name="address_id"]:checked').length > 0 || $('input[name="single_address_id"]:checked').length > 0;
        }

        $(document).on('change', '#shipping_info [required]', function() {
            if ($(this).attr('name') == 'address_id') {
                updateDeliveryAddress($(this).val());
                setDefaultshippingAddress();
                setBillingAddress();
            }
            stepCompletionShippingInfo();
        });

        $(document).on('click focusin', '#shipping_info', function() {
            if (!checkoutIsAuthenticated) {
                openCheckoutAccountModal();
            }
        });

        function openCheckoutAccountModal() {
            $('#checkout-account-modal').modal({
                backdrop: true,
                keyboard: true,
                focus: true
            });
        }

        function bindCheckoutAccountModal() {
            $(document).on('click', '[data-checkout-auth-next]', function() {
                var $form = $(this).closest('form');
                var $step = $form.find('[data-checkout-auth-step="1"]');
                var valid = true;

                $step.find(':input').each(function() {
                    if (!this.checkValidity()) {
                        this.reportValidity();
                        valid = false;
                        return false;
                    }
                });

                if (valid) {
                    setCheckoutAuthStep($form, 2);
                    $form.find('[data-checkout-auth-step="2"] :input:visible').first().trigger('focus');
                }
            });

            $(document).on('click', '[data-checkout-auth-back]', function() {
                setCheckoutAuthStep($(this).closest('form'), 1);
            });

            $(document).on('shown.bs.tab', '.checkout-auth-tab', function(event) {
                $('.checkout-auth-tab').attr('aria-selected', 'false');
                $(event.target).attr('aria-selected', 'true');

                if ($(event.target).attr('href') === '#checkout-register-tab') {
                    setCheckoutAuthStep($('#checkout-register-form'), 1);
                }
            });

            $('#checkout-account-modal').on('hidden.bs.modal', function() {
                setCheckoutAuthStep($('#checkout-register-form'), 1);
                $('.checkout-account-errors').addClass('d-none').empty();
            });

            $('[name="verification_method"]').on('change', function() {
                var $form = $(this).closest('form');
                var phoneMode = $form.find('[name="verification_method"]:checked').val() === 'phone';
                $form.find('.checkout-phone-fields').toggleClass('d-none', !phoneMode);
                $form.find('.checkout-email-fields').toggleClass('d-none', phoneMode);
                $form.find('[name="email"]').prop('required', !phoneMode);
                $form.find('[name="account_phone"], [name="account_country_code"]').prop('required', phoneMode);
            });

            $('[name="login_method"]').on('change', function() {
                var $form = $(this).closest('form');
                var phoneMode = $form.find('[name="login_method"]:checked').val() === 'phone';
                $form.find('.checkout-login-phone-fields').toggleClass('d-none', !phoneMode);
                $form.find('.checkout-login-email-fields').toggleClass('d-none', phoneMode);
                $form.find('[name="login_email"]').prop('required', !phoneMode);
                $form.find('[name="login_phone"], [name="login_country_code"]').prop('required', phoneMode);
            });

            $(document).on('change', '.checkout-country-select', function() {
                var $form = $(this).closest('form');
                var phoneCode = $(this).find(':selected').data('phone-code');
                if (phoneCode) {
                    $form.find('.checkout-delivery-country-code').val(phoneCode);
                }
                loadCheckoutStatesOrCities($form);
            });

            $(document).on('change', '.checkout-state-select', function() {
                loadCheckoutCities($(this).closest('form'), $(this).val());
            });

            $(document).on('change', '.checkout-city-select', function() {
                loadCheckoutAreas($(this).closest('form'), $(this).val());
            });

            $('[data-checkout-account-form]').each(function() {
                var $country = $(this).find('.checkout-country-select');
                if ($country.val()) {
                    $country.trigger('change');
                }
            });

            $(document).on('submit', '[data-checkout-account-form]', function(e) {
                e.preventDefault();
                submitCheckoutAccountForm($(this));
            });
        }

        function setCheckoutAuthStep($form, step) {
            if (!$form || !$form.length) {
                return;
            }

            $form.find('[data-checkout-auth-step]').addClass('d-none');
            $form.find('[data-checkout-auth-step="' + step + '"]').removeClass('d-none');

            var $modal = $('#checkout-account-modal');
            $modal.find('[data-checkout-step-label]').removeClass('active');
            $modal.find('[data-checkout-step-label="' + step + '"]').addClass('active');
            $modal.find('.checkout-auth-main').scrollTop(0);
        }

        function submitCheckoutAccountForm($form) {
            var $button = $form.find('.checkout-account-submit');
            $('.checkout-account-errors').addClass('d-none').empty();
            $button.prop('disabled', true).addClass('btn-loading');

            $.ajax({
                url: '{{ route('checkout.account_address') }}',
                type: 'POST',
                data: $form.serialize(),
                success: function(data) {
                    checkoutIsAuthenticated = true;
                    checkoutNeedsAddress = false;
                    $('#shipping_info').html(data.shipping_info);
                    $('#delivery_info').html(data.delivery_info);
                    $('#cart_summary').html(data.cart_summary);
                    carrierCount = data.carrier_count || 0;
                    $('#checkout-account-modal').modal('hide');
                    checkCarrerShippingInfo();
                    stepCompletionShippingInfo();
                    stepCompletionDeliveryInfo();
                    stepCompletionPaymentInfo();
                    AIZ.plugins.notify('success', data.message || '{{ translate('Checkout details saved.') }}');
                    AIZ.plugins.bootstrapSelect("refresh");
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.needs_address) {
                        checkoutIsAuthenticated = true;
                        checkoutNeedsAddress = true;
                        $('#checkout-account-modal .tab-pane').removeClass('show active');
                        $('#checkout-address-tab').addClass('show active');
                        $('#checkout-account-modal .checkout-auth-main').scrollTop(0);
                    }

                    var message = xhr.responseJSON && xhr.responseJSON.message
                        ? xhr.responseJSON.message
                        : '{{ translate('Please check the highlighted fields.') }}';

                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        var errors = [];
                        $.each(xhr.responseJSON.errors, function(field, fieldErrors) {
                            errors = errors.concat(fieldErrors);
                        });
                        message = errors.join('<br>');
                    }

                    $('.checkout-account-errors').removeClass('d-none').html(message);
                    AIZ.plugins.notify('danger', $('<div>').html(message).text());
                },
                complete: function() {
                    $button.prop('disabled', false).removeClass('btn-loading');
                }
            });
        }

        function loadCheckoutStatesOrCities($form) {
            @if(get_setting('has_state') == 1)
                $.post('{{ route('get-state') }}', {
                    _token: AIZ.data.csrf,
                    country_id: $form.find('.checkout-country-select').val()
                }, function(data) {
                    $form.find('.checkout-state-select').html(data);
                    $form.find('.checkout-city-select').html('<option value="">{{ translate('Select your city') }}</option>');
                    $form.find('.checkout-area-select').html('<option value="">{{ translate('Select your area') }}</option>');
                });
            @else
                $.post('{{ route('get-city-by-country') }}', {
                    _token: AIZ.data.csrf,
                    country_id: $form.find('.checkout-country-select').val()
                }, function(data) {
                    $form.find('.checkout-city-select').html(data);
                    $form.find('.checkout-area-select').html('<option value="">{{ translate('Select your area') }}</option>');
                });
            @endif
        }

        function loadCheckoutCities($form, stateId) {
            $.post('{{ route('get-city') }}', {
                _token: AIZ.data.csrf,
                state_id: stateId
            }, function(data) {
                $form.find('.checkout-city-select').html(data);
                $form.find('.checkout-area-select').html('<option value="">{{ translate('Select your area') }}</option>');
            });
        }

        function loadCheckoutAreas($form, cityId) {
            $.post('{{ route('get-area') }}', {
                _token: AIZ.data.csrf,
                city_id: cityId
            }, function(data) {
                $form.find('.checkout-area-select').html(data);
            });
        }

        function stepCompletionDeliveryInfo() {
            let isOk = $('.delivery_shipping_cost:checked').length > 0;
            
            // If they chose pickup_point, ensure a specific point is selected
            if($('.shipping-type-radio:checked[value="pickup_point"]').length > 0) {
                isOk = false;
                $('.pickup_point_id').each(function() {
                    if($(this).val()) isOk = true;
                });
            }
            
            return isOk;
        }

        function updateDeliveryInfo(shipping_type, type_id, user_id, country_id = 0, city_id = 0) {
            @if (get_setting('shipping_type') == 'area_wise_shipping' || get_setting('shipping_type') == 'carrier_wise_shipping')
                country_id = $('select[name="country_id"]').val() != null ? $('select[name="country_id"]').val() : 0;
                city_id = $('select[name="city_id"]').val() != null ? $('select[name="city_id"]').val() : 0;
            @endif
            $('.aiz-refresh').addClass('active');
            $.post('{{ route('checkout.updateDeliveryInfo') }}', {
                _token: AIZ.data.csrf,
                shipping_type: shipping_type,
                type_id: type_id,
                user_id: user_id,
                country_id: country_id,
                city_id: city_id
            }, function(data) {
                $('#cart_summary').html(data);
                checkCarrerShippingInfo();
                stepCompletionDeliveryInfo();
                $('.aiz-refresh').removeClass('active');
            });
            AIZ.plugins.bootstrapSelect("refresh");
        }

        function show_pickup_point(el, user_id) {
        	var type = $(el).val();
        	var target = $(el).data('target');
            var type_id = null;

        	if(type == 'home_delivery' || type == 'carrier'){
                if(!$(target).hasClass('d-none')){
                    $(target).addClass('d-none');
                }
                $('.carrier_id_'+user_id).removeClass('d-none');
        	}else{
        		$(target).removeClass('d-none');
        		$('.carrier_id_'+user_id).addClass('d-none');
        	}

            if(type == 'carrier'){
                type_id = $('input[name=carrier_id_'+user_id+']:checked').val();
            }else if(type == 'pickup_point'){
                type_id = $('select[name=pickup_point_id_'+user_id+']').val();
            }
            updateDeliveryInfo(type, type_id, user_id);
        }

        function stepCompletionPaymentInfo() {
            var isChecked = $('input[name="payment_option"]:checked').length > 0;
            var agree = $('#agree_checkbox').is(":checked");
            $("#submitOrderBtn").prop('disabled', !(isChecked && agree));
            return isChecked;
        }

        $('input[name="payment_option"]').change(function(){
            stepCompletionPaymentInfo();
        });

        function checkCarrerShippingInfo(){
            const shippingType = @json(get_setting('shipping_type'));
            const isCarrier = $('.shipping-type-radio:checked[value="carrier"]').length > 0;
            if(shippingType == 'carrier_wise_shipping' && isCarrier && carrierCount === 0){
                $('#submitOrderBtn').prop('disabled', true);
                $('#agree_checkbox').prop('disabled',true);
            } else {
                $('#agree_checkbox').prop('disabled', false);
            }
        }

        function changeShippingAddress(){
            $('#choose-address-modal').modal('hide');
        }

        function setDefaultshippingAddress() {
            let checkedAddress = $('input[name="address_id"]:checked');

            if (checkedAddress.length) {

                let selectedText = checkedAddress.closest('label').find('.address-text').html();
                $('#choose-default').html(selectedText);
                $('#default-address-change-btn').attr('onclick', "edit_address('" + checkedAddress.val() + "')");
                $('input[name="billing_address_id"]').first().val(checkedAddress.val());
                let $box = $('#default-address-box');
                if ($box.length) {
                    $box.removeClass('border-danger');
                    checkedAddress.prop('checked', true);
                    checkedAddress.prop('disabled', false);
                    $box.find('#hide-no-longer-div').remove();
                    
                }
            }
        }

        function setBillingAddress() {
            let checkedAddress = $('input[name="billing_address_id"]:checked');
            if (checkedAddress.length) {
                let selectedText = checkedAddress.closest('label').find('.address-text').html();
                $('#choose-default-billing').html(selectedText);
                $('#default-billing-address-change-btn').attr('onclick', "edit_billing_address('" + checkedAddress.val() + "')");
                let $box = $('#default-billing-address-box');
                if ($box.length) {
                    $box.removeClass('border-danger');
                    checkedAddress.prop('checked', true);
                    checkedAddress.prop('disabled', false);
                    $box.find('#hide-no-valid-div').remove();
                }
            } else {
                // If no billing address is explicitly checked, use the shipping address as default
                let shippingAddress = $('input[name="address_id"]:checked');
                if (shippingAddress.length) {
                    let selectedText = shippingAddress.closest('label').find('.address-text').html();
                    $('#choose-default-billing').html(selectedText);
                    $('input[name="billing_address_id"]').first().val(shippingAddress.val());
                }
            }
            updateBillingAddress(checkedAddress.val() || $('input[name="address_id"]:checked').val());
        }

        $(document).on("click", "#coupon-apply", function() {
            var data = new FormData($('#apply-coupon-form')[0]);
            $.ajax({
                headers: { 'X-CSRF-TOKEN': AIZ.data.csrf },
                method: "POST",
                url: "{{ route('checkout.apply_coupon_code') }}",
                data: data,
                cache: false, contentType: false, processData: false,
                success: function(data) {
                    AIZ.plugins.notify(data.response_message.response, data.response_message.message);
                    $("#cart_summary").html(data.html);
                }
            });
        });

        $(document).on("click", "#coupon-remove", function() {
            var data = new FormData($('#remove-coupon-form')[0]);
            $.ajax({
                headers: { 'X-CSRF-TOKEN': AIZ.data.csrf },
                method: "POST",
                url: "{{ route('checkout.remove_coupon_code') }}",
                data: data,
                cache: false, contentType: false, processData: false,
                success: function(data) {
                    $("#cart_summary").html(data);
                }
            });
        });
    </script>

    @include('frontend.partials.address.address_js')

    @if(get_active_countries()->count() == 1)
    <script>
        $(document).ready(function() {
            @if(get_setting('has_state') == 1)
                get_states(@json(get_active_countries()[0]->id));
            @else
                get_city_by_country(@json(get_active_countries()[0]->id));
            @endif
        });
    </script>
    @endif

    @if (get_setting('google_map') == 1)
        @include('frontend.partials.google_map')
    @endif
@endsection
