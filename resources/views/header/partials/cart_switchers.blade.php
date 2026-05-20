@php
    $headerSwitcherColor = $headerSwitcherColor ?? get_setting('bottom_header_text_color');
    $showLanguageSwitcher = get_setting('show_language_switcher') == 'on';
    $showCurrencySwitcher = get_setting('show_currency_switcher') == 'on';
@endphp

@if ($showLanguageSwitcher || $showCurrencySwitcher)
    @once
        <style>
            .header-cart-switchers {
                gap: 0.35rem;
                min-width: 0;
                padding-inline: 0.35rem;
            }

            .header-cart-switcher > a {
                min-height: 50px;
                padding-inline: 0.55rem;
                display: inline-flex;
                align-items: center;
                color: inherit;
                white-space: nowrap;
            }

            .header-cart-switcher .dropdown-menu {
                margin-top: 0;
            }

            @media (max-width: 1199.98px) {
                .top-navbar .mobile-header-switcher > a {
                    max-width: 92px;
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                }
            }

            @media (max-width: 575.98px) {
                .top-navbar .mobile-header-switcher > a {
                    max-width: 72px;
                    padding-left: 0.25rem;
                    padding-right: 0.25rem;
                    font-size: 11px;
                }
            }
        </style>
    @endonce

    <div class="header-cart-switchers d-flex align-items-center h-100 bottom-text-color-visibility"
        style="color: {{ $headerSwitcherColor }}">
        @if ($showLanguageSwitcher)
            <div class="dropdown header-cart-switcher lang-visibility js-lang-change" id="lang-change">
                <a href="javascript:void(0)" class="dropdown-toggle fs-12 fw-700 bottom-text-color-visibility"
                    style="color: {{ $headerSwitcherColor }}" data-toggle="dropdown" data-display="static">
                    <span class="d-inline-block text-uppercase d-xl-none">{{ $system_language->code }}</span>
                    <span class="d-none d-xl-inline">{{ $system_language->name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-right">
                    @foreach (get_all_active_language() as $key => $language)
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

        @if ($showCurrencySwitcher)
            @php
                $system_currency = get_system_currency();
            @endphp
            <div class="dropdown header-cart-switcher currency-visibility js-currency-change" id="currency-change">
                <a href="javascript:void(0)" class="dropdown-toggle fs-12 fw-700 bottom-text-color-visibility"
                    style="color: {{ $headerSwitcherColor }}" data-toggle="dropdown" data-display="static">
                    <span class="d-inline-block text-uppercase">{{ $system_currency->code ?? $system_currency->name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-right">
                    @foreach (get_all_active_currency() as $key => $currency)
                        <li>
                            <a class="dropdown-item @if (($system_currency->code ?? null) == $currency->code) active @endif text-dark"
                                href="javascript:void(0)" data-currency="{{ $currency->code }}">
                                {{ $currency->name }} ({{ $currency->symbol }})
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endif
