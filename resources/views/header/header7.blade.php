@php
    $headerLogo = get_setting('header_logo');
    $topCategories = \App\Models\Category::where('level', 0)
        ->orderBy('order_level', 'desc')
        ->limit(12)
        ->get();
    $systemCurrency = get_setting('show_currency_switcher') == 'on' ? get_system_currency() : null;
@endphp

@once
    <style>
        .mayush-market-header {
            --market-header-bg: #111827;
            --market-header-sub-bg: #243244;
            --market-header-accent: #d97434;
            --market-header-accent-hover: #bf5f21;
            --market-header-text: #ffffff;
            --market-header-muted: rgba(255, 255, 255, 0.72);
            background: var(--market-header-bg);
            color: var(--market-header-text);
        }

        .mayush-market-header a,
        .mayush-market-header button {
            color: inherit;
        }

        .market-main-row {
            min-height: 58px;
            gap: 12px;
        }

        .market-logo-link {
            min-width: 150px;
        }

        .market-logo-link img {
            max-height: 38px;
            width: auto;
            object-fit: contain;
        }

        .market-location {
            min-width: 128px;
            padding: 8px 9px;
            border: 1px solid transparent;
            border-radius: 3px;
            line-height: 1.05;
        }

        .market-location:hover,
        .market-action-link:hover,
        .market-menu-trigger:hover {
            color: #fff;
            text-decoration: none;
        }

        .market-location .label,
        .market-action-link .label {
            color: var(--market-header-muted);
            font-size: 11px;
            font-weight: 600;
        }

        .market-location .value,
        .market-action-link .value {
            color: #fff;
            font-size: 13px;
            font-weight: 800;
        }

        .market-search {
            min-width: 260px;
            margin-top: 12px;
            margin-bottom: 12px;
        }

        .market-search-form {
            height: 42px;
            border-radius: 4px;
            overflow: visible;
            background: #fff;
        }

        .market-search-category {
            width: 130px;
            height: 42px;
            border: 0;
            border-right: 1px solid #d7d7d7;
            background: #f3f4f6;
            color: #374151;
            font-size: 12px;
            padding: 0 8px;
            outline: 0;
        }

        .market-search-input {
            height: 42px;
            min-width: 0;
            border: 0;
            box-shadow: none;
            color: #111827;
        }

        .market-search-button {
            width: 48px;
            height: 42px;
            border: 0;
            background: var(--market-header-accent);
            color: #111827;
            font-size: 22px;
        }

        .market-search-button:hover {
            background: var(--market-header-accent-hover);
            color: #111827;
        }

        .market-action-link {
            min-height: 46px;
            padding: 7px 8px;
            border: 1px solid transparent;
            border-radius: 3px;
            line-height: 1.05;
            white-space: nowrap;
        }

        .market-switcher {
            min-height: 46px;
            border: 1px solid transparent;
            border-radius: 3px;
        }


        .market-switcher > a {
            min-height: 44px;
            padding: 0 8px;
            display: inline-flex;
            align-items: center;
            font-size: 13px;
            font-weight: 800;
            color: #fff;
            white-space: nowrap;
        }

        .market-cart-wrap {
            min-height: 46px;
            border: 1px solid transparent;
            border-radius: 3px;
        }


        .market-cart-wrap .nav-cart-box > a {
            padding-left: 8px !important;
            padding-right: 8px !important;
        }

        .market-sub-row {
            min-height: 40px;
            background: var(--market-header-sub-bg);
        }

        .market-menu-trigger {
            min-height: 36px;
            padding: 0 10px;
            border: 1px solid transparent;
            border-radius: 3px;
            font-weight: 800;
        }

        .market-nav-link {
            min-height: 36px;
            display: inline-flex;
            align-items: center;
            padding: 0 10px;
            border: 1px solid transparent;
            border-radius: 3px;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
        }

        .market-nav-link:hover {
            color: #fff;
            text-decoration: none;
        }

        .market-typed-search {
            z-index: 1050;
        }

        @media (max-width: 1199.98px) {
            .market-main-row {
                min-height: auto;
                gap: 8px;
                padding: 8px 0;
                flex-wrap: wrap;
            }

            .market-logo-link {
                min-width: 0;
                flex: 1;
            }

            .market-logo-link img {
                max-height: 32px;
            }

            .market-search {
                order: 5;
                flex-basis: 100%;
                min-width: 100%;
                margin-top: 8px;
                margin-bottom: 8px;
            }

            .market-search-category {
                width: 86px;
                font-size: 11px;
            }

            .market-action-link {
                min-height: 38px;
                padding: 4px 6px;
            }

            .market-action-link .label {
                display: none;
            }
        }

        @media (max-width: 575.98px) {
            .market-main-row {
                gap: 6px;
            }

            .market-logo-link img {
                max-width: 116px;
            }

            .market-search-form {
                height: 40px;
            }

            .market-search-category,
            .market-search-input,
            .market-search-button {
                height: 40px;
            }

            .market-search-category {
                width: 72px;
            }

            .market-switcher > a {
                min-height: 36px;
                padding: 0 5px;
                font-size: 11px;
            }
        }
    </style>
@endonce

<header class="mayush-market-header @if (get_setting('header_stikcy') == 'on') sticky-top @endif z-1020 stikcy-header-visibility">
    <div class="market-primary-row">
        <div class="container">
            <div class="market-main-row d-flex align-items-center">
                <button type="button" class="btn d-xl-none p-0 mr-1 market-menu-trigger" data-toggle="class-toggle"
                    data-target=".aiz-top-menu-sidebar" aria-label="{{ translate('Open menu') }}">
                    <i class="las la-bars la-2x"></i>
                </button>

                <a class="market-logo-link d-flex align-items-center py-2" href="{{ route('home') }}">
                    @if ($headerLogo != null)
                        <img id="header-logo-preview" src="{{ uploaded_asset($headerLogo) }}" alt="{{ env('APP_NAME') }}">
                    @else
                        <img id="header-logo-preview" src="{{ static_asset('assets/img/logo.png') }}" alt="{{ env('APP_NAME') }}">
                    @endif
                </a>

                <a href="{{ route('contact') }}" class="market-location d-none d-xl-flex align-items-center">
                    <i class="las la-map-marker-alt la-lg mr-1"></i>
                    <span>
                        <span class="label d-block">{{ translate('Deliver to') }}</span>
                        <span class="value d-block">{{ translate('Morocco') }}</span>
                    </span>
                </a>

                <div class="market-search flex-grow-1 ml-3 ml-md-4">
                    <div class="position-relative">
                        <form action="{{ route('search') }}" method="GET" class="stop-propagation">
                            <div class="d-flex position-relative align-items-center">
                                <div class="search-input-box d-flex align-items-center bg-white border border-soft-light rounded-pill overflow-hidden hov-animate-outline" style="height: 48px; flex: 1;">
                                    <div class="ai-mode-toggle-wrap ml-3" data-toggle="tooltip" data-placement="bottom" title="{{ translate('AI Semantic Search') }}" onclick="toggleAiMode()">
                                        <div class="ai-toggle-btn" id="ai-mode-toggle"></div>
                                        <span class="ai-toggle-label">✨ AI</span>
                                    </div>
                                    
                                    <input type="text"
                                        class="border-0 shadow-none form-control fs-14 h-100 bg-transparent px-2 market-search-input"
                                        id="search" name="keyword" @isset($query) value="{{ $query }}" @endisset
                                        placeholder="{{ translate('Search Mayush Design') }}" autocomplete="off" style="min-width: 0; color: #111827;">

                                    <div class="d-flex align-items-center pr-2 pl-2 border-left border-soft-light" style="gap: 4px; height: 32px;">
                                        {{-- Visual Search Camera Button --}}
                                        <button type="button" class="btn btn-sm btn-icon text-secondary hov-text-primary visual-search-btn" onclick="document.getElementById('visual-search-input').click()" title="{{ translate('Search by Image') }}" style="background: none; border: none; outline: none; box-shadow: none;">
                                            <i class="las la-camera la-xl"></i>
                                        </button>
                                        
                                        <button type="submit" class="btn btn-sm btn-icon text-secondary hov-text-primary p-2" title="{{ translate('Search') }}" style="background: none; border: none; outline: none; box-shadow: none;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 20.001 20">
                                                <path d="M9.847,17.839a7.993,7.993,0,1,1,7.993-7.993A8,8,0,0,1,9.847,17.839Zm0-14.387a6.394,6.394,0,1,0,6.394,6.394A6.4,6.4,0,0,0,9.847,3.453Z" transform="translate(-1.854 -1.854)" fill="currentColor" />
                                                <path d="M24.4,25.2a.8.8,0,0,1-.565-.234l-6.15-6.15a.8.8,0,0,1,1.13-1.13l6.15,6.15A.8.8,0,0,1,24.4,25.2Z" transform="translate(-5.2 -5.2)" fill="currentColor" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="typed-search-box market-typed-search stop-propagation document-click-d-none d-none bg-white rounded shadow-lg position-absolute left-0 top-100 w-100"
                            style="min-height: 200px">
                            <div class="search-preloader absolute-top-center">
                                <div class="dot-loader">
                                    <div></div>
                                    <div></div>
                                    <div></div>
                                </div>
                            </div>
                            <div class="search-nothing d-none p-3 text-center fs-16"></div>
                            <div id="search-content" class="text-left"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="market-sub-row d-none d-lg-block position-relative">
        <div class="container h-100">
            <div class="d-flex align-items-center h-100 justify-content-between">
                
                <!-- Left Section: Category Toggler & Navigation links -->
                <div class="d-flex align-items-center overflow-hidden">
                    <button type="button" class="btn market-menu-trigger d-flex align-items-center mr-3" id="category-menu-bar" style="cursor: pointer;">
                        <i class="las la-bars la-lg mr-1" id="category-menu-bar-icon"></i>
                        {{ translate('All') }}
                    </button>

                    <div class="d-flex align-items-center hor-swipe c-scrollbar-light overflow-hidden">
                        @if (get_setting('header_menu_labels') != null)
                            @foreach (json_decode(get_setting('header_menu_labels'), true) as $key => $value)
                                <a href="{{ json_decode(get_setting('header_menu_links'), true)[$key] ?? '#' }}"
                                    class="market-nav-link @if (url()->current() == (json_decode(get_setting('header_menu_links'), true)[$key] ?? null)) active @endif">
                                    {{ translate($value) }}
                                </a>
                            @endforeach
                        @else
                            <a href="{{ route('categories.all') }}" class="market-nav-link">{{ translate('Categories') }}</a>
                            <a href="{{ route('search') }}" class="market-nav-link">{{ translate("Today's Deals") }}</a>
                            @if (get_setting('vendor_system_activation') == 1)
                                <a href="{{ route(get_setting('seller_registration_verify') === '1' ? 'shop-reg.verification' : 'shops.create') }}"
                                    class="market-nav-link">{{ translate('Sell') }}</a>
                            @endif
                            <a href="{{ route('contact') }}" class="market-nav-link">{{ translate('Customer Service') }}</a>
                        @endif

                        <!-- Become Seller button (always show if vendor system activated) -->
                        @if (get_setting('vendor_system_activation') == 1)
                            <a href="{{ route(get_setting('seller_registration_verify') === '1' ? 'shop-reg.verification' : 'shops.create') }}" class="market-nav-link text-warning ml-3 fw-700">
                                <i class="las la-store mr-1"></i>{{ translate('Become a Seller') }}
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Right Section: Relocated switchers/actions block -->
                <div class="d-flex align-items-center ml-auto">
                    @if (get_setting('show_language_switcher') == 'on')
                        <div class="dropdown market-switcher lang-visibility js-lang-change mr-2" id="lang-change">
                            <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" data-display="static">
                                <span class="text-uppercase d-xl-none">{{ $system_language->code }}</span>
                                <span class="d-none d-xl-inline">{{ $system_language->name }}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right">
                                @foreach (get_all_active_language() as $language)
                                    <li>
                                        <a href="javascript:void(0)" data-flag="{{ $language->code }}"
                                            class="dropdown-item text-dark @if ($system_language->code == $language->code) active @endif">
                                            <img src="{{ static_asset('assets/img/placeholder.jpg') }}"
                                                data-src="{{ static_asset('assets/img/flags/' . $language->code . '.png') }}"
                                                class="mr-1 lazyload" alt="{{ $language->name }}" height="11">
                                            <span class="language">{{ $language->name }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if ($systemCurrency)
                        <div class="dropdown market-switcher currency-visibility js-currency-change mr-2" id="currency-change">
                            <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown" data-display="static">
                                {{ $systemCurrency->code ?? $systemCurrency->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right">
                                @foreach (get_all_active_currency() as $currency)
                                    <li>
                                        <a href="javascript:void(0)" data-currency="{{ $currency->code }}"
                                            class="dropdown-item text-dark @if (($systemCurrency->code ?? null) == $currency->code) active @endif">
                                            {{ $currency->name }} ({{ $currency->symbol }})
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="dropdown d-none d-lg-block mr-2">
                        <a href="javascript:void(0)" class="market-action-link d-flex flex-column justify-content-center" data-toggle="dropdown">
                            <span class="label">{{ Auth::check() ? translate('Hello') . ', ' . \Illuminate\Support\Str::limit($user->name, 12) : translate('Hello, sign in') }}</span>
                            <span class="value">{{ translate('Account & Lists') }}</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right py-0">
                            @auth
                                <a class="dropdown-item py-2" href="{{ isAdmin() ? route('admin.dashboard') : route('dashboard') }}">{{ translate('Dashboard') }}</a>
                                @if (isCustomer())
                                    <a class="dropdown-item py-2" href="{{ route('purchase_history.index') }}">{{ translate('Purchase History') }}</a>
                                    <a class="dropdown-item py-2" href="{{ route('wishlists.index') }}">{{ translate('Wishlist') }}</a>
                                @endif
                                <a class="dropdown-item py-2 text-primary" href="{{ route('logout') }}">{{ translate('Logout') }}</a>
                            @else
                                <a class="dropdown-item py-2" href="{{ route('user.login') }}">{{ translate('Login') }}</a>
                                <a class="dropdown-item py-2" href="{{ route('user.registration') }}">{{ translate('Registration') }}</a>
                            @endauth
                        </div>
                    </div>

                    <div class="market-cart-wrap dropdown ml-1" data-hover="dropdown">
                        <div class="nav-cart-box dropdown h-100" id="cart_items">
                            @include('frontend.partials.cart.cart')
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Categoty Menus -->
        <div class="hover-category-menu position-absolute w-100 top-100 left-0 right-0 z-3 d-none"
            id="click-category-menu">
            <div class="container">
                <div class="row gutters-10 position-relative">
                    <div class="col-lg-3 position-static">
                        @include('frontend.' . safe_homepage_select() . '.partials.category_menu')
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
    (function() {
        function initCategoryMenu() {
            var menuBars = document.querySelectorAll('#category-menu-bar');
            menuBars.forEach(function(menuBar) {
                if (menuBar.getAttribute('data-bound')) {
                    return;
                }
                menuBar.setAttribute('data-bound', 'true');
                menuBar.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (e.stopImmediatePropagation) {
                        e.stopImmediatePropagation();
                    }
                    
                    if (window.jQuery) {
                        var $ = window.jQuery;
                        var $menu = $('#click-category-menu');
                        if ($menu.hasClass('d-none')) {
                            $menu.removeClass('d-none').hide();
                        }
                        $menu.slideToggle("fast");
                        $('#category-menu-bar-icon').toggleClass('show');
                    }
                });
            });
        }

        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", initCategoryMenu);
        } else {
            initCategoryMenu();
        }
    })();
</script>
