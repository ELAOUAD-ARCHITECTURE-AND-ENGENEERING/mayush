{{-- ═══════════════════════════════════════════════════════════════════════════
     BUYER DASHBOARD — PRO SIDEBAR NAVIGATION
     Design system: Data-Dense Dashboard (ui-ux-pro-max)
     Typography: Poppins 600/700 headings, Open Sans 400/600 body
     Colors: #111827 nav bg, brand primary active state, #F9FAFB surface
     ═══════════════════════════════════════════════════════════════════════════ --}}
<aside class="bdash-sidebar" id="buyerSidebar" aria-label="{{ translate('Buyer account navigation') }}">

    {{-- ─── User Profile Block ─────────────────────────────────────────────── --}}
    @php
        $sideUser   = auth()->user();
        $sideAvatar = $sideUser && $sideUser->avatar_original
                        ? uploaded_asset($sideUser->avatar_original)
                        : static_asset('assets/img/avatar-place.png');

        $delivery_viewed        = get_count_by_delivery_viewed();
        $payment_status_viewed  = get_count_by_payment_status_viewed();
        $order_badge            = ($delivery_viewed + $payment_status_viewed) > 0
                                    ? ($delivery_viewed + $payment_status_viewed)
                                    : 0;

        $support_ticket_count = DB::table('tickets')
            ->where('client_viewed', 0)
            ->where('user_id', Auth::user()->id)
            ->count();
    @endphp

    <div class="bdash-profile">
        <span class="bdash-profile__avatar">
            <img
                src="{{ $sideAvatar }}"
                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';"
                alt="{{ translate('Profile picture') }}"
            >
        </span>
        <div class="bdash-profile__info">
            <span class="bdash-profile__name">{{ $sideUser->name }}</span>
            <span class="bdash-profile__role">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" width="10" height="10" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5 3.25a.75.75 0 0 1 1.5 0V4h3V3.25a.75.75 0 0 1 1.5 0V4h.25A2.75 2.75 0 0 1 14 6.75v6.5A2.75 2.75 0 0 1 11.25 16H4.75A2.75 2.75 0 0 1 2 13.25v-6.5A2.75 2.75 0 0 1 4.75 4H5V3.25Zm-1 4.5v-.25c0-.138.112-.25.25-.25h7.5c.138 0 .25.112.25.25v.25H4Z" clip-rule="evenodd"/>
                </svg>
                {{ translate('Buyer Account') }}
            </span>
        </div>
        {{-- Mobile close --}}
        <button
            class="bdash-sidebar__close d-xl-none"
            data-toggle="class-toggle"
            data-target=".aiz-mobile-side-nav"
            data-same=".mobile-side-nav-thumb"
            aria-label="{{ translate('Close navigation') }}"
        >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/>
            </svg>
        </button>
    </div>

    {{-- ─── Navigation ─────────────────────────────────────────────────────── --}}
    <nav class="bdash-nav" data-toggle="aiz-side-menu">

        {{-- ── Group: Shopping ─────────────────────────────────────────────── --}}
        <div class="bdash-nav__group">
            <span class="bdash-nav__label">{{ translate('Shopping') }}</span>

            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}"
               class="bdash-nav__item {{ areActiveRoutes(['dashboard'], 'is-active') }}"
               aria-current="{{ request()->routeIs('dashboard') ? 'page' : 'false' }}"
            >
                <span class="bdash-nav__icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                        <path fill-rule="evenodd" d="M9.293 2.293a1 1 0 0 1 1.414 0l7 7A1 1 0 0 1 17 11h-1v6a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1v-3a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-6H3a1 1 0 0 1-.707-1.707l7-7Z" clip-rule="evenodd"/>
                    </svg>
                </span>
                <span class="bdash-nav__text">{{ translate('Overview') }}</span>
            </a>

            {{-- Purchase History --}}
            <a href="{{ route('purchase_history.index') }}"
               class="bdash-nav__item {{ areActiveRoutes(['purchase_history.index', 'purchase_history.details'], 'is-active') }}"
               aria-current="{{ request()->routeIs('purchase_history.index', 'purchase_history.details') ? 'page' : 'false' }}"
            >
                <span class="bdash-nav__icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                        <path fill-rule="evenodd" d="M6 5v1H4.667a1.75 1.75 0 0 0-1.743 1.598l-.826 9.5A1.75 1.75 0 0 0 3.84 19H16.16a1.75 1.75 0 0 0 1.743-1.902l-.826-9.5A1.75 1.75 0 0 0 15.333 6H14V5a4 4 0 0 0-8 0Zm4-2.5A2.5 2.5 0 0 0 7.5 5v1h5V5A2.5 2.5 0 0 0 10 2.5ZM7.5 10a2.5 2.5 0 0 0 5 0V8.75a.75.75 0 0 1 1.5 0V10a4 4 0 0 1-8 0V8.75a.75.75 0 0 1 1.5 0V10Z" clip-rule="evenodd"/>
                    </svg>
                </span>
                <span class="bdash-nav__text">{{ translate('Purchase History') }}</span>
                @if ($order_badge > 0)
                    <span class="bdash-nav__badge" aria-label="{{ $order_badge }} {{ translate('new updates') }}">{{ $order_badge }}</span>
                @endif
            </a>

            {{-- Downloads --}}
            <a href="{{ route('digital_purchase_history.index') }}"
               class="bdash-nav__item {{ areActiveRoutes(['digital_purchase_history.index'], 'is-active') }}"
            >
                <span class="bdash-nav__icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                        <path d="M10.75 2.75a.75.75 0 0 0-1.5 0v8.614L6.295 8.235a.75.75 0 1 0-1.09 1.03l4.25 4.5a.75.75 0 0 0 1.09 0l4.25-4.5a.75.75 0 0 0-1.09-1.03l-2.955 3.129V2.75Z"/>
                        <path d="M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z"/>
                    </svg>
                </span>
                <span class="bdash-nav__text">{{ translate('Downloads') }}</span>
            </a>

            {{-- Wishlist --}}
            <a href="{{ route('wishlists.index') }}"
               class="bdash-nav__item {{ areActiveRoutes(['wishlists.index'], 'is-active') }}"
            >
                <span class="bdash-nav__icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                        <path d="m9.653 16.915-.005-.003-.019-.01a20.759 20.759 0 0 1-1.162-.682 22.045 22.045 0 0 1-2.582-2.09c-1.06-1.07-2.135-2.52-2.635-4.148-.504-1.643-.314-3.556 1.138-4.887C5.696 4.33 7.03 4 8.208 4c.93 0 1.82.276 2.588.79a5.27 5.27 0 0 1 2.592-.79c1.177 0 2.512.33 3.82 1.095 1.452 1.33 1.642 3.244 1.138 4.887-.5 1.627-1.575 3.079-2.635 4.148a22.048 22.048 0 0 1-2.582 2.09 20.764 20.764 0 0 1-1.181.692l-.005.003-.002.001a.752.752 0 0 1-.704 0l-.002-.001Z"/>
                    </svg>
                </span>
                <span class="bdash-nav__text">{{ translate('Wishlist') }}</span>
            </a>

            {{-- Compare --}}
            <a href="{{ route('compare') }}"
               class="bdash-nav__item {{ areActiveRoutes(['compare'], 'is-active') }}"
            >
                <span class="bdash-nav__icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                        <path fill-rule="evenodd" d="M10 3a.75.75 0 0 1 .75.75v10.638l3.96-4.158a.75.75 0 1 1 1.08 1.04l-5.25 5.5a.75.75 0 0 1-1.08 0l-5.25-5.5a.75.75 0 1 1 1.08-1.04l3.96 4.158V3.75A.75.75 0 0 1 10 3Z" clip-rule="evenodd"/>
                    </svg>
                </span>
                <span class="bdash-nav__text">{{ translate('Compare') }}</span>
            </a>

            {{-- Preorder --}}
            @if (addon_is_activated('preorder'))
                <div class="bdash-nav__sub-wrap" data-sub-nav>
                    <button class="bdash-nav__item bdash-nav__item--toggle" aria-expanded="false" aria-controls="sub-preorder">
                        <span class="bdash-nav__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                        <span class="bdash-nav__text">{{ translate('Preorder') }}</span>
                        <span class="bdash-nav__arrow" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" width="12" height="12">
                                <path fill-rule="evenodd" d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                    </button>
                    <div class="bdash-nav__sub" id="sub-preorder">
                        <a href="{{ route('preorder.order_list') }}" class="bdash-nav__sub-item {{ areActiveRoutes(['preorder.order_list'], 'is-active') }}">
                            {{ translate('Preorder List') }}
                        </a>
                        @if (get_setting('conversation_system') == 1)
                            @php $preorderConversation = get_non_viewed_preorder_conversations(); @endphp
                            <a href="{{ route('preorder-conversations.customer-index') }}" class="bdash-nav__sub-item {{ areActiveRoutes(['preorder-conversations.customer-show'], 'is-active') }}">
                                {{ translate('Preorder Conversations') }}
                                @if ($preorderConversation > 0)
                                    <span class="bdash-nav__badge">{{ $preorderConversation }}</span>
                                @endif
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Auction --}}
            @if (addon_is_activated('auction'))
                <div class="bdash-nav__sub-wrap" data-sub-nav>
                    <button class="bdash-nav__item bdash-nav__item--toggle" aria-expanded="false" aria-controls="sub-auction">
                        <span class="bdash-nav__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                                <path fill-rule="evenodd" d="M9.664 1.319a.75.75 0 0 1 .672 0 41.059 41.059 0 0 1 8.198 5.424.75.75 0 0 1-.254 1.285 31.372 31.372 0 0 0-7.86 3.83.75.75 0 0 1-.84 0 31.508 31.508 0 0 0-2.08-1.287V9.394c0-.244.116-.463.302-.592a35.504 35.504 0 0 1 3.305-2.033.75.75 0 0 0-.714-1.319 37 37 0 0 0-3.446 2.12A2.216 2.216 0 0 0 6 9.393v.38a31.293 31.293 0 0 0-4.28-1.746.75.75 0 0 1-.254-1.285 41.059 41.059 0 0 1 8.198-5.424ZM6 11.459a29.848 29.848 0 0 0-2.455-1.158 41.029 41.029 0 0 0-.39 3.114.75.75 0 0 0 .419.74c.528.256 1.046.53 1.554.82-.21.324-.455.63-.739.914a.75.75 0 1 0 1.06 1.06c.37-.369.69-.77.96-1.193a26.61 26.61 0 0 1 3.095 2.348.75.75 0 0 0 .992 0 26.547 26.547 0 0 1 5.93-3.95.75.75 0 0 0 .42-.739 41.053 41.053 0 0 0-.39-3.114 29.925 29.925 0 0 0-5.199 2.801 2.25 2.25 0 0 1-2.514 0c-.41-.275-.826-.541-1.247-.797Z" clip-rule="evenodd"/>
                                <path fill-rule="evenodd" d="M5.453 16.91a.75.75 0 0 0 .818-1.26c-.08-.052-.16-.105-.24-.157a26.585 26.585 0 0 1-.818 1.417Z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                        <span class="bdash-nav__text">{{ translate('Auction') }}</span>
                        <span class="bdash-nav__arrow" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" width="12" height="12">
                                <path fill-rule="evenodd" d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                    </button>
                    <div class="bdash-nav__sub" id="sub-auction">
                        <a href="{{ route('auction_product_bids.index') }}" class="bdash-nav__sub-item">{{ translate('Bidded Products') }}</a>
                        <a href="{{ route('auction_product.purchase_history') }}" class="bdash-nav__sub-item">{{ translate('Purchase History') }}</a>
                    </div>
                </div>
            @endif
        </div>

        {{-- ── Group: Finance ───────────────────────────────────────────────── --}}
        <div class="bdash-nav__group">
            <span class="bdash-nav__label">{{ translate('Finance') }}</span>

            @if (get_setting('wallet_system') == 1)
                <a href="{{ route('wallet.index') }}"
                   class="bdash-nav__item {{ areActiveRoutes(['wallet.index'], 'is-active') }}"
                >
                    <span class="bdash-nav__icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                            <path d="M1 4.25a3.733 3.733 0 0 1 2.25-.75h13.5c.844 0 1.623.279 2.25.75A2.25 2.25 0 0 0 16.75 2H3.25A2.25 2.25 0 0 0 1 4.25ZM1 7.25a3.733 3.733 0 0 1 2.25-.75h13.5c.844 0 1.623.279 2.25.75A2.25 2.25 0 0 0 16.75 5H3.25A2.25 2.25 0 0 0 1 7.25ZM7 8a1 1 0 0 0-1 1 8 8 0 0 0 8 8 1 1 0 0 0 0-2 6 6 0 0 1-6-6 1 1 0 0 0-1-1Zm4 0a1 1 0 0 0 0 2 4 4 0 0 1 4 4 1 1 0 0 0 2 0 6 6 0 0 0-6-6Z"/>
                        </svg>
                    </span>
                    <span class="bdash-nav__text">{{ translate('My Wallet') }}</span>
                </a>
            @endif

            <a href="{{ route('payment_tokens.index') }}"
               class="bdash-nav__item {{ areActiveRoutes(['payment_tokens.index'], 'is-active') }}"
            >
                <span class="bdash-nav__icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                        <path fill-rule="evenodd" d="M2.5 4A1.5 1.5 0 0 0 1 5.5V6h18v-.5A1.5 1.5 0 0 0 17.5 4h-15ZM19 8.5H1v6A1.5 1.5 0 0 0 2.5 16h15a1.5 1.5 0 0 0 1.5-1.5v-6ZM3 13.25a.75.75 0 0 1 .75-.75h1.5a.75.75 0 0 1 0 1.5h-1.5a.75.75 0 0 1-.75-.75Zm4.75-.75a.75.75 0 0 0 0 1.5h3.5a.75.75 0 0 0 0-1.5h-3.5Z" clip-rule="evenodd"/>
                    </svg>
                </span>
                <span class="bdash-nav__text">{{ translate('Saved Cards') }}</span>
            </a>

            @if (addon_is_activated('refund_request'))
                <a href="{{ route('customer_refund_request') }}"
                   class="bdash-nav__item {{ areActiveRoutes(['customer_refund_request'], 'is-active') }}"
                >
                    <span class="bdash-nav__icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                            <path fill-rule="evenodd" d="M2.293 7.293a1 1 0 0 1 1.414 0L5 8.586l1.293-1.293a1 1 0 1 1 1.414 1.414L6.414 10l1.293 1.293a1 1 0 0 1-1.414 1.414L5 11.414l-1.293 1.293a1 1 0 0 1-1.414-1.414L3.586 10 2.293 8.707a1 1 0 0 1 0-1.414ZM9.5 5.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 0 1h-8a.5.5 0 0 1-.5-.5Zm0 9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 0 1h-8a.5.5 0 0 1-.5-.5ZM9 10a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 0 1h-8A.5.5 0 0 1 9 10Z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                    <span class="bdash-nav__text">{{ translate('Refund Requests') }}</span>
                </a>
            @endif
        </div>

        {{-- ── Group: Discover ──────────────────────────────────────────────── --}}
        @if (get_setting('vendor_system_activation') == 1 || addon_is_activated('affiliate_system'))
        <div class="bdash-nav__group">
            <span class="bdash-nav__label">{{ translate('Discover') }}</span>

            @if (get_setting('vendor_system_activation') == 1)
                <a href="{{ route('followed_seller') }}"
                   class="bdash-nav__item {{ areActiveRoutes(['followed_seller'], 'is-active') }}"
                >
                    <span class="bdash-nav__icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                            <path d="M1 1.75A.75.75 0 0 1 1.75 1h1.628a1.75 1.75 0 0 1 1.734 1.51L5.17 3h9.58a1.75 1.75 0 0 1 1.698 2.186l-1.087 4.5a1.75 1.75 0 0 1-1.698 1.314H6.18l.5 2.668V14c0 .414-.336.75-.75.75H4.463a.75.75 0 0 1-.741-.637L2.17 2.5H1.75A.75.75 0 0 1 1 1.75ZM6 16.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0ZM15.5 18a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z"/>
                        </svg>
                    </span>
                    <span class="bdash-nav__text">{{ translate('Followed Sellers') }}</span>
                </a>
            @endif

            @if (addon_is_activated('affiliate_system'))
                <div class="bdash-nav__sub-wrap" data-sub-nav>
                    <button class="bdash-nav__item bdash-nav__item--toggle" aria-expanded="false" aria-controls="sub-affiliate">
                        <span class="bdash-nav__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                                <path d="M10 9a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM6 8a2 2 0 1 1-4 0 2 2 0 0 1 4 0ZM1.49 15.326a.78.78 0 0 1-.358-.442 3 3 0 0 1 4.308-3.516 6.484 6.484 0 0 0-1.905 3.959c-.023.222-.014.442.025.654a4.97 4.97 0 0 1-2.07-.655ZM16.44 15.98a4.97 4.97 0 0 0 2.07-.654.78.78 0 0 0 .357-.442 3 3 0 0 0-4.308-3.517 6.484 6.484 0 0 1 1.907 3.96 2.32 2.32 0 0 1-.026.654ZM18 8a2 2 0 1 1-4 0 2 2 0 0 1 4 0ZM5.304 16.19a.844.844 0 0 1-.277-.71 5 5 0 0 1 9.947 0 .843.843 0 0 1-.277.71A6.975 6.975 0 0 1 10 18a6.974 6.974 0 0 1-4.696-1.81Z"/>
                            </svg>
                        </span>
                        <span class="bdash-nav__text">{{ translate('Affiliate') }}</span>
                        <span class="bdash-nav__arrow" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" width="12" height="12">
                                <path fill-rule="evenodd" d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                    </button>
                    <div class="bdash-nav__sub" id="sub-affiliate">
                        <a href="{{ route('affiliate.user.index') }}" class="bdash-nav__sub-item {{ areActiveRoutes(['affiliate.user.index','affiliate.payment_settings'], 'is-active') }}">{{ translate('Affiliate System') }}</a>
                        <a href="{{ route('affiliate.user.payment_history') }}" class="bdash-nav__sub-item">{{ translate('Payment History') }}</a>
                        <a href="{{ route('affiliate.user.withdraw_request_history') }}" class="bdash-nav__sub-item">{{ translate('Withdraw History') }}</a>
                    </div>
                </div>
            @endif
        </div>
        @endif

        {{-- ── Group: Account ───────────────────────────────────────────────── --}}
        <div class="bdash-nav__group">
            <span class="bdash-nav__label">{{ translate('Account') }}</span>

            {{-- Loyalty Lounge --}}
            <a href="{{ route('loyalty.hub') }}"
               class="bdash-nav__item {{ areActiveRoutes(['loyalty.hub'], 'is-active') }}"
            >
                <span class="bdash-nav__icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                        <path fill-rule="evenodd" d="M10.868 2.884c-.321-.772-1.415-.772-1.736 0l-1.83 4.401-4.753.381c-.833.067-1.171 1.107-.536 1.651l3.62 3.102-1.106 4.637c-.194.813.691 1.456 1.405 1.02L10 15.591l4.069 2.485c.713.436 1.598-.207 1.404-1.02l-1.106-4.637 3.62-3.102c.635-.544.297-1.584-.536-1.65l-4.752-.382-1.831-4.401Z" clip-rule="evenodd"/>
                    </svg>
                </span>
                <span class="bdash-nav__text">{{ translate('Loyalty Lounge') }}</span>
            </a>

            {{-- Conversations --}}
            @if (get_setting('conversation_system') == 1)
                @php $conversation = get_non_viewed_conversations(); @endphp
                <a href="{{ route('conversations.index') }}"
                   class="bdash-nav__item {{ areActiveRoutes(['conversations.index', 'conversations.show'], 'is-active') }}"
                >
                    <span class="bdash-nav__icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                            <path fill-rule="evenodd" d="M10 2c-2.236 0-4.43.18-6.57.524C1.993 2.755 1 4.014 1 5.426v5.148c0 1.413.993 2.67 2.43 2.902.848.137 1.705.248 2.57.331v3.443a.75.75 0 0 0 1.28.53l3.58-3.579a.78.78 0 0 1 .527-.224 41.202 41.202 0 0 0 5.183-.5c1.437-.232 2.43-1.49 2.43-2.903V5.426c0-1.413-.993-2.67-2.43-2.902A41.289 41.289 0 0 0 10 2Zm0 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2ZM8 9a1 1 0 1 1-2 0 1 1 0 0 1 2 0Zm5 1a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                    <span class="bdash-nav__text">{{ translate('Conversations') }}</span>
                    @if (count($conversation) > 0)
                        <span class="bdash-nav__badge">{{ count($conversation) }}</span>
                    @endif
                </a>
            @endif

            {{-- Support Ticket --}}
            <a href="{{ route('support_ticket.index') }}"
               class="bdash-nav__item {{ areActiveRoutes(['support_ticket.index', 'support_ticket.show'], 'is-active') }}"
            >
                <span class="bdash-nav__icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/>
                    </svg>
                </span>
                <span class="bdash-nav__text">{{ translate('Support') }}</span>
                @if ($support_ticket_count > 0)
                    <span class="bdash-nav__badge">{{ $support_ticket_count }}</span>
                @endif
            </a>

            {{-- Manage Profile --}}
            <a href="{{ route('profile') }}"
               class="bdash-nav__item {{ areActiveRoutes(['profile'], 'is-active') }}"
            >
                <span class="bdash-nav__icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-5.5-2.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0ZM10 12a5.99 5.99 0 0 0-4.793 2.39A6.483 6.483 0 0 0 10 16.5a6.483 6.483 0 0 0 4.793-2.11A5.99 5.99 0 0 0 10 12Z" clip-rule="evenodd"/>
                    </svg>
                </span>
                <span class="bdash-nav__text">{{ translate('Manage Profile') }}</span>
            </a>

            {{-- Delete Account --}}
            <button
                type="button"
                class="bdash-nav__item bdash-nav__item--danger"
                onclick="account_delete_confirm_modal('{{ route('account_delete') }}')"
            >
                <span class="bdash-nav__icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="18" height="18">
                        <path fill-rule="evenodd" d="M8.75 1A2.75 2.75 0 0 0 6 3.75v.443c-.795.077-1.584.176-2.365.298a.75.75 0 1 0 .23 1.482l.149-.022.841 10.518A2.75 2.75 0 0 0 7.596 19h4.807a2.75 2.75 0 0 0 2.742-2.53l.841-10.52.149.023a.75.75 0 0 0 .23-1.482A41.03 41.03 0 0 0 14 4.193V3.75A2.75 2.75 0 0 0 11.25 1h-2.5ZM10 4c.84 0 1.673.025 2.5.075V3.75c0-.69-.56-1.25-1.25-1.25h-2.5c-.69 0-1.25.56-1.25 1.25v.325C8.327 4.025 9.16 4 10 4ZM8.58 7.72a.75.75 0 0 0-1.5.06l.3 7.5a.75.75 0 1 0 1.5-.06l-.3-7.5Zm4.34.06a.75.75 0 1 0-1.5-.06l-.3 7.5a.75.75 0 1 0 1.5.06l.3-7.5Z" clip-rule="evenodd"/>
                    </svg>
                </span>
                <span class="bdash-nav__text">{{ translate('Delete My Account') }}</span>
            </button>
        </div>

        {{-- ── Sign Out ──────────────────────────────────────────────────────── --}}
        <div class="bdash-nav__signout">
            <a href="{{ route('logout') }}" class="bdash-signout-btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="16" height="16" aria-hidden="true">
                    <path fill-rule="evenodd" d="M3 4.25A2.25 2.25 0 0 1 5.25 2h5.5A2.25 2.25 0 0 1 13 4.25v2a.75.75 0 0 1-1.5 0v-2a.75.75 0 0 0-.75-.75h-5.5a.75.75 0 0 0-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 0 0 .75-.75v-2a.75.75 0 0 1 1.5 0v2A2.25 2.25 0 0 1 10.75 18h-5.5A2.25 2.25 0 0 1 3 15.75V4.25Z" clip-rule="evenodd"/>
                    <path fill-rule="evenodd" d="M19 10a.75.75 0 0 0-.75-.75H8.704l1.048-1.08a.75.75 0 1 0-1.004-1.11l-2.5 2.5a.75.75 0 0 0 0 1.08l2.5 2.5a.75.75 0 1 0 1.004-1.108L8.704 10.75H18.25A.75.75 0 0 0 19 10Z" clip-rule="evenodd"/>
                </svg>
                {{ translate('Sign Out') }}
            </a>
        </div>

    </nav>
</aside>

{{-- ─── Sidebar Styles ──────────────────────────────────────────────────────── --}}
<style>
/* ── Fonts ──────────────────────────────────────────────────────────────── */
@import url('https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap');

/* ── Variables ──────────────────────────────────────────────────────────── */
:root {
    --bdash-sidebar-w: 252px;
    --bdash-sidebar-bg: #FFFFFF;
    --bdash-sidebar-border: #E9EFF5;
    --bdash-nav-label-color: #94A3B8;
    --bdash-nav-item-color: #374151;
    --bdash-nav-item-color-hover: #111827;
    --bdash-nav-item-bg-hover: #F1F5F9;
    --bdash-nav-active-color: #0F766E;       /* teal-700 — premium, commerce-trust */
    --bdash-nav-active-bg: #F0FDFA;          /* teal-50 */
    --bdash-nav-active-border: #14B8A6;      /* teal-400 */
    --bdash-icon-color: #9CA3AF;
    --bdash-icon-active: #0F766E;
    --bdash-badge-bg: #DC2626;
    --bdash-badge-color: #FFFFFF;
    --bdash-font-body: var(--mayush-font-body);
    --bdash-font-head: var(--mayush-font-heading);
    --bdash-transition: 160ms ease;
}

/* ── Sidebar Container ──────────────────────────────────────────────────── */
.bdash-sidebar {
    background: var(--bdash-sidebar-bg);
    border: 1px solid var(--bdash-sidebar-border);
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 4px 12px rgba(0,0,0,0.04);
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
    font-family: var(--bdash-font-body);
    overflow: hidden;
    position: sticky;
    top: 88px; /* below the dark top navbar */
    width: var(--bdash-sidebar-w);
}

/* ── Profile Block ──────────────────────────────────────────────────────── */
.bdash-profile {
    align-items: center;
    border-bottom: 1px solid var(--bdash-sidebar-border);
    display: flex;
    gap: 10px;
    padding: 16px 16px 14px;
    position: relative;
}

.bdash-profile__avatar {
    border: 2px solid #E0F2F1;
    border-radius: 50%;
    box-shadow: 0 2px 8px rgba(20, 184, 166, 0.18);
    display: block;
    flex-shrink: 0;
    height: 44px;
    overflow: hidden;
    width: 44px;
}

.bdash-profile__avatar img {
    height: 100%;
    object-fit: cover;
    width: 100%;
}

.bdash-profile__info {
    flex: 1;
    min-width: 0;
}

.bdash-profile__name {
    color: #111827;
    display: block;
    font-family: var(--bdash-font-head);
    font-size: 13px;
    font-weight: 600;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.bdash-profile__role {
    align-items: center;
    color: var(--bdash-nav-active-color);
    display: inline-flex;
    font-size: 10.5px;
    font-weight: 600;
    gap: 3px;
    letter-spacing: 0.01em;
    margin-top: 2px;
}

.bdash-sidebar__close {
    background: none;
    border: none;
    border-radius: 6px;
    color: #6B7280;
    cursor: pointer;
    flex-shrink: 0;
    padding: 4px;
    transition: background var(--bdash-transition), color var(--bdash-transition);
}

.bdash-sidebar__close:hover { background: #F3F4F6; color: #111827; }

/* ── Navigation ─────────────────────────────────────────────────────────── */
.bdash-nav {
    flex: 1;
    overflow-y: auto;
    overscroll-behavior: contain;
    padding: 8px 0 4px;
    scrollbar-width: thin;
    scrollbar-color: #E2E8F0 transparent;
}

.bdash-nav::-webkit-scrollbar { width: 4px; }
.bdash-nav::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 4px; }

/* ── Group ───────────────────────────────────────────────────────────────── */
.bdash-nav__group {
    margin-bottom: 4px;
    padding: 0 10px;
}

.bdash-nav__label {
    color: var(--bdash-nav-label-color);
    display: block;
    font-family: var(--bdash-font-head);
    font-size: 9.5px;
    font-weight: 700;
    letter-spacing: 0.08em;
    padding: 10px 6px 5px;
    text-transform: uppercase;
    user-select: none;
}

/* ── Nav Item ────────────────────────────────────────────────────────────── */
.bdash-nav__item {
    align-items: center;
    background: transparent;
    border: none;
    border-left: 3px solid transparent;
    border-radius: 0 8px 8px 0;
    color: var(--bdash-nav-item-color);
    cursor: pointer;
    display: flex;
    font-family: var(--bdash-font-body);
    font-size: 13px;
    font-weight: 500;
    gap: 10px;
    margin-left: -10px; /* extend to sidebar edge for border-left */
    min-height: 40px;
    padding: 9px 10px 9px 13px;
    text-align: left;
    text-decoration: none;
    transition:
        background var(--bdash-transition),
        border-color var(--bdash-transition),
        color var(--bdash-transition),
        transform var(--bdash-transition);
    width: calc(100% + 10px);
}

.bdash-nav__item:hover {
    background: var(--bdash-nav-item-bg-hover);
    color: var(--bdash-nav-item-color-hover);
    text-decoration: none;
    transform: translateX(2px);
}

.bdash-nav__item.is-active {
    background: var(--bdash-nav-active-bg);
    border-left-color: var(--bdash-nav-active-border);
    color: var(--bdash-nav-active-color);
    font-weight: 600;
}

.bdash-nav__item.is-active .bdash-nav__icon {
    color: var(--bdash-icon-active);
}

.bdash-nav__item--toggle {
    width: 100%;
}

.bdash-nav__item--danger {
    color: #DC2626;
    width: 100%;
}

.bdash-nav__item--danger:hover {
    background: #FEF2F2;
    color: #991B1B;
}

.bdash-nav__item--danger .bdash-nav__icon { color: #DC2626; }

/* ── Icon ────────────────────────────────────────────────────────────────── */
.bdash-nav__icon {
    align-items: center;
    color: var(--bdash-icon-color);
    display: inline-flex;
    flex-shrink: 0;
    height: 18px;
    justify-content: center;
    transition: color var(--bdash-transition);
    width: 18px;
}

.bdash-nav__item:hover .bdash-nav__icon {
    color: var(--bdash-nav-item-color-hover);
}

/* ── Text ────────────────────────────────────────────────────────────────── */
.bdash-nav__text {
    flex: 1;
    line-height: 1.3;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* ── Badge ───────────────────────────────────────────────────────────────── */
.bdash-nav__badge {
    background: var(--bdash-badge-bg);
    border-radius: 20px;
    color: var(--bdash-badge-color);
    flex-shrink: 0;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 0.01em;
    line-height: 1;
    min-width: 18px;
    padding: 3px 5px;
    text-align: center;
}

/* ── Arrow (sub-nav toggle) ──────────────────────────────────────────────── */
.bdash-nav__arrow {
    color: #CBD5E1;
    display: inline-flex;
    flex-shrink: 0;
    margin-left: auto;
    transition: transform 220ms ease, color var(--bdash-transition);
}

.bdash-nav__item--toggle[aria-expanded="true"] .bdash-nav__arrow {
    transform: rotate(180deg);
    color: #94A3B8;
}

/* ── Sub-nav ─────────────────────────────────────────────────────────────── */
.bdash-nav__sub-wrap { }

.bdash-nav__sub {
    display: none;
    padding: 2px 0 4px 28px;
}

.bdash-nav__sub.is-open { display: block; }

.bdash-nav__sub-item {
    align-items: center;
    border-left: 1px solid #E2E8F0;
    color: #64748B;
    display: flex;
    font-size: 12px;
    font-weight: 500;
    gap: 6px;
    min-height: 34px;
    padding: 7px 8px;
    text-decoration: none;
    transition: color var(--bdash-transition), border-color var(--bdash-transition);
}

.bdash-nav__sub-item:hover {
    border-left-color: var(--bdash-nav-active-border);
    color: var(--bdash-nav-active-color);
    text-decoration: none;
}

.bdash-nav__sub-item.is-active {
    border-left-color: var(--bdash-nav-active-border);
    color: var(--bdash-nav-active-color);
    font-weight: 600;
}

/* ── Sign Out ────────────────────────────────────────────────────────────── */
.bdash-nav__signout {
    border-top: 1px solid var(--bdash-sidebar-border);
    padding: 10px;
}

.bdash-signout-btn {
    align-items: center;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
    border-radius: 8px;
    color: #374151;
    cursor: pointer;
    display: flex;
    font-family: var(--bdash-font-body);
    font-size: 13px;
    font-weight: 600;
    gap: 8px;
    justify-content: center;
    padding: 9px 12px;
    text-decoration: none;
    transition: background var(--bdash-transition), border-color var(--bdash-transition), color var(--bdash-transition);
    width: 100%;
}

.bdash-signout-btn:hover {
    background: #FEF2F2;
    border-color: #FECACA;
    color: #DC2626;
    text-decoration: none;
}

/* ── Responsive ──────────────────────────────────────────────────────────── */
@media (max-width: 1199.98px) {
    .bdash-sidebar {
        position: fixed;
        top: 0;
        left: -280px;
        bottom: 0;
        z-index: 1050;
        border-radius: 0;
        width: 260px;
        transition: left 260ms cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: none;
    }

    .bdash-sidebar.is-open {
        left: 0;
        box-shadow: 4px 0 24px rgba(0,0,0,0.12);
    }

    .aiz-mobile-side-nav .bdash-sidebar { left: 0; box-shadow: 4px 0 24px rgba(0,0,0,0.12); }
}

@media (prefers-reduced-motion: reduce) {
    .bdash-nav__item,
    .bdash-nav__arrow,
    .bdash-sidebar { transition: none !important; }
}
</style>

{{-- ─── Sub-nav JS (no jQuery dependency) ─────────────────────────────────── --}}
<script>
(function () {
    'use strict';

    document.querySelectorAll('[data-sub-nav] .bdash-nav__item--toggle').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var expanded = this.getAttribute('aria-expanded') === 'true';
            var targetId = this.getAttribute('aria-controls');
            var target   = document.getElementById(targetId);

            this.setAttribute('aria-expanded', String(!expanded));
            if (target) target.classList.toggle('is-open', !expanded);
        });

        // Auto-expand if a child is active
        var wrap  = btn.closest('[data-sub-nav]');
        var sub   = wrap && wrap.querySelector('.bdash-nav__sub');
        if (sub && sub.querySelector('.is-active')) {
            btn.setAttribute('aria-expanded', 'true');
            sub.classList.add('is-open');
        }
    });
}());
</script>
