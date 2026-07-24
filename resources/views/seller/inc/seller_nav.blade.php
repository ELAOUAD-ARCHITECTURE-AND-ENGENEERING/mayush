<div class="aiz-topbar px-15px px-lg-25px d-flex align-items-stretch justify-content-between">
    <div class="d-flex">
        <div class="aiz-topbar-nav-toggler d-flex align-items-center justify-content-start mr-2 mr-md-3 ml-0" data-toggle="aiz-mobile-nav">
            <button class="aiz-mobile-toggler">
                <span></span>
            </button>
        </div>
    </div>
    <div class="d-flex justify-content-between align-items-stretch flex-grow-xl-1">
        <div class="d-flex justify-content-around align-items-center align-items-stretch">
            <div class="d-flex justify-content-around align-items-center align-items-stretch">
                <div class="aiz-topbar-item">
                    <div class="d-flex align-items-center">
                        <a class="btn btn-icon btn-circle btn-light" href="{{ route('home')}}" target="_blank" title="{{ translate('Browse Website') }}">
                            <i class="las la-globe"></i>
                        </a>
                    </div>
                </div>
                <div class="aiz-topbar-item ml-3">
                    <div class="d-flex align-items-center">
                        <a class="btn btn-icon btn-circle btn-light" href="{{ route('seller.cache.clear') }}" title="{{ translate('Clear Cache') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16">
                                <path id="_74846e5be5db5b666d3893933be03656" data-name="74846e5be5db5b666d3893933be03656" d="M7.719,8.911H8.9V10.1H7.719v1.185H6.539V10.1H5.36V8.911h1.18V7.726h1.18ZM5.36,13.652h1.18v1.185H5.36v1.185H4.18V14.837H3V13.652H4.18V12.467H5.36Zm13.626-2.763H10.138V10.3a1.182,1.182,0,0,1,1.18-1.185h2.36V2h1.77V9.111h2.36a1.182,1.182,0,0,1,1.18,1.185ZM18.4,18H16.044a9.259,9.259,0,0,0,.582-2.963.59.59,0,1,0-1.18,0A7.69,7.69,0,0,1,14.755,18H12.5a9.259,9.259,0,0,0,.582-2.963.59.59,0,1,0-1.18,0A7.69,7.69,0,0,1,11.216,18H8.958a22.825,22.825,0,0,0,1.163-5.926H18.99A19.124,19.124,0,0,1,18.4,18Z" transform="translate(-3 -2)" fill="#717580"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            @if (addon_is_activated('pos_system'))
                <div class="d-flex justify-content-around align-items-center align-items-stretch ml-3">
                    <div class="aiz-topbar-item">
                        <div class="d-flex align-items-center">
                            <a class="btn btn-icon btn-circle btn-light" href="{{ route('poin-of-sales.seller_index') }}" target="_blank" title="{{ translate('POS') }}">
                                <i class="las la-print"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endif
            <div class="aiz-topbar-item ml-3">
                <div class="d-flex align-items-center h-100 seller-topbar-mode-wrap">
                    @include('partials.account_mode_switcher')
                </div>
            </div>

            {{-- ── Seller-context mode-switcher override ──────────────────────────
                 The global .ms-pill uses dark-glass styling (for dark buyer navbar).
                 Here it sits on the white/light seller topbar, so we override it to
                 be fully visible: solid dark pill with amber seller accent.
            ──────────────────────────────────────────────────────────────────── --}}
            <style>
                /* ── Scoped to the seller topbar only ────────────────────────── */
                .aiz-topbar {
                    background: #FFFFFF !important;
                    border-bottom: 1px solid #CFFAFE !important; /* cyan-100 */
                }
                .aiz-topbar .btn-light {
                    background: #ECFEFF !important; /* cyan-50 */
                    color: #0891B2 !important; /* cyan-600 */
                    border-color: #CFFAFE !important;
                }
                .aiz-topbar .btn-light:hover {
                    background: #CFFAFE !important;
                }

                .seller-topbar-mode-wrap .ms-pill {
                    background: #164E63;                       /* cyan-900 solid */
                    border: none;
                    border-radius: 12px;
                    box-shadow: 0 2px 8px rgba(22, 78, 99, 0.22),
                                0 0 0 1px rgba(22, 78, 99, 0.10);
                    height: 38px;
                    padding: 0;
                    transition: box-shadow 180ms ease;
                }

                .seller-topbar-mode-wrap .ms-pill:hover {
                    box-shadow: 0 4px 16px rgba(22, 78, 99, 0.30),
                                0 0 0 1px rgba(22, 78, 99, 0.14);
                }

                /* Current mode segment — cyan for seller */
                .seller-topbar-mode-wrap .ms-pill__mode--seller {
                    color: #22D3EE;                            /* cyan-400 */
                    font-size: 11.5px;
                    font-family: 'Poppins', -apple-system, sans-serif;
                    font-weight: 700;
                    letter-spacing: 0.03em;
                    padding: 0 12px;
                }

                /* Current mode segment — emerald for buyer (edge case) */
                .seller-topbar-mode-wrap .ms-pill__mode--buyer {
                    color: #6EE7B7;
                    font-size: 11.5px;
                    font-family: 'Poppins', -apple-system, sans-serif;
                    font-weight: 700;
                    letter-spacing: 0.03em;
                    padding: 0 12px;
                }

                /* Divider */
                .seller-topbar-mode-wrap .ms-pill__divider {
                    background: rgba(255, 255, 255, 0.12);
                }

                /* Action button */
                .seller-topbar-mode-wrap .ms-pill__btn {
                    background: rgba(34, 211, 238, 0.12);     /* cyan tint */
                    border-left: 1px solid rgba(34, 211, 238, 0.18);
                    border-radius: 0 12px 12px 0;
                    color: #A5F3FC;                            /* cyan-200 */
                    font-size: 11.5px;
                    font-family: 'Poppins', -apple-system, sans-serif;
                    font-weight: 600;
                    gap: 6px;
                    padding: 0 13px;
                    transition: background 180ms ease, color 180ms ease;
                }

                .seller-topbar-mode-wrap .ms-pill__btn:hover {
                    background: rgba(34, 211, 238, 0.22);
                    color: #FFFFFF;
                }

                .seller-topbar-mode-wrap .ms-pill__btn:focus-visible {
                    background: rgba(34, 211, 238, 0.22);
                    box-shadow: inset 0 0 0 2px rgba(165, 243, 252, 0.6);
                    color: #FFFFFF;
                }

                /* Arrow icon inherits button color */
                .seller-topbar-mode-wrap .ms-pill__btn svg {
                    opacity: 0.85;
                }
            </style>
        </div>
        <div class="d-flex justify-content-around align-items-center align-items-stretch">

            @if (config('notifications_v2.enabled'))
                {{-- The shared trigger owns authoritative fetches and live inbox state. --}}
                @include('partials.notification-center-trigger', ['variant' => 'seller'])
            @else
                {{-- Preserve the existing seller inbox while the additive v2 rollout is disabled. --}}
                @include('partials.legacy-notification-trigger', ['variant' => 'seller'])
            @endif

            {{-- language --}}
            @php
                if(Session::has('locale')){
                    $locale = Session::get('locale', Config::get('app.locale'));
                }
                else{
                    $locale = env('DEFAULT_LANGUAGE');
                }
            @endphp
            <div class="aiz-topbar-item ml-2">
                <div class="align-items-stretch d-flex dropdown " id="lang-change">
                    <a class="dropdown-toggle no-arrow" data-toggle="dropdown" href="javascript:void(0);" role="button" aria-haspopup="false" aria-expanded="false">
                        <span class="btn btn-icon">
                            <img src="{{ static_asset('assets/img/flags/'.$locale.'.png') }}" height="11">
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-right dropdown-menu-animated dropdown-menu-xs">

                        @foreach (\App\Models\Language::where('status', 1)->get() as $key => $language)
                            <li>
                                <a href="javascript:void(0)" data-flag="{{ $language->code }}" class="dropdown-item @if($locale == $language->code) active @endif">
                                    <img src="{{ static_asset('assets/img/flags/'.$language->code.'.png') }}" class="mr-2">
                                    <span class="language">{{ $language->name }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="aiz-topbar-item ml-2">
                <div class="align-items-stretch d-flex dropdown">
                    <a class="dropdown-toggle no-arrow text-dark" data-toggle="dropdown" href="javascript:void(0);" role="button" aria-haspopup="false" aria-expanded="false">
                        <span class="d-flex align-items-center">
                            <span class="avatar avatar-sm mr-md-2">
                                <img
                                    src="{{ uploaded_asset(Auth::user()->avatar_original) }}"
                                    onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';"
                                >
                            </span>
                            <span class="d-none d-md-block">
                                <span class="d-block fw-500">{{Auth::user()->name}}</span>
                                <span class="d-block small opacity-60">{{Auth::user()->user_type}}</span>
                            </span>
                        </span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right dropdown-menu-animated dropdown-menu-md">
                        <a href="{{ route('seller.profile.index') }}" class="dropdown-item">
                            <i class="las la-user-circle"></i>
                            <span>{{translate('Profile')}}</span>
                        </a>

                        <a href="{{ route('logout')}}" class="dropdown-item">
                            <i class="las la-sign-out-alt"></i>
                            <span>{{translate('Logout')}}</span>
                        </a>
                    </div>
                </div>
            </div><!-- .aiz-topbar-item -->
        </div>
    </div>
</div><!-- .aiz-topbar -->
