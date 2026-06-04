@if (can_switch_account_mode())
    @php
        $currentMode = active_account_mode();
        $targetMode  = $currentMode === 'seller' ? 'buyer' : 'seller';
        $isSeller    = $currentMode === 'seller';
    @endphp

    {{-- ─── Mode Switcher Pill ─────────────────────────────────────────────── --}}
    <div class="ms-pill" role="status" aria-label="{{ translate('Current mode') }}: {{ $isSeller ? translate('Seller') : translate('Buyer') }}">

        {{-- Active mode label --}}
        <span class="ms-pill__mode {{ $isSeller ? 'ms-pill__mode--seller' : 'ms-pill__mode--buyer' }}">
            @if ($isSeller)
                {{-- Store icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="13" height="13" aria-hidden="true">
                    <path d="M2 3a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v1a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3Z"/>
                    <path fill-rule="evenodd" d="M3 6h14v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6Zm5 4a1 1 0 0 0-1 1v2a1 1 0 0 0 1 1h4a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1H8Z" clip-rule="evenodd"/>
                </svg>
                {{ translate('Seller') }}
            @else
                {{-- Shopping bag icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="13" height="13" aria-hidden="true">
                    <path fill-rule="evenodd" d="M6 5v1H4.667a1.75 1.75 0 0 0-1.743 1.598l-.826 9.5A1.75 1.75 0 0 0 3.84 19H16.16a1.75 1.75 0 0 0 1.743-1.902l-.826-9.5A1.75 1.75 0 0 0 15.333 6H14V5a4 4 0 0 0-8 0Zm4-2.5A2.5 2.5 0 0 0 7.5 5v1h5V5A2.5 2.5 0 0 0 10 2.5ZM7.5 10a2.5 2.5 0 0 0 5 0V8.75a.75.75 0 0 1 1.5 0V10a4 4 0 0 1-8 0V8.75a.75.75 0 0 1 1.5 0V10Z" clip-rule="evenodd"/>
                </svg>
                {{ translate('Buyer') }}
            @endif
        </span>

        {{-- Divider --}}
        <span class="ms-pill__divider" aria-hidden="true"></span>

        {{-- Switch action --}}
        <form method="POST" action="{{ route('account-mode.switch') }}" class="ms-pill__form" data-account-mode-switcher>
            @csrf
            <input type="hidden" name="mode" value="{{ $targetMode }}">
            <button
                type="submit"
                class="ms-pill__btn"
                title="{{ $isSeller ? translate('Switch to Buyer') : translate('Switch to Seller') }}"
                aria-label="{{ $isSeller ? translate('Switch to Buyer') : translate('Switch to Seller') }}"
            >
                {{-- Swap / arrows icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="12" height="12" aria-hidden="true">
                    <path d="M7.075 3.708a.75.75 0 0 1-.008 1.06L5.154 6.75h9.096a.75.75 0 0 1 0 1.5H5.154l1.913 1.982a.75.75 0 0 1-1.084 1.036l-3.25-3.37a.75.75 0 0 1 0-1.036l3.25-3.37a.75.75 0 0 1 1.092.016ZM12.925 16.292a.75.75 0 0 1 .008-1.06L14.846 13.25H5.75a.75.75 0 0 1 0-1.5h9.096l-1.913-1.982a.75.75 0 0 1 1.084-1.036l3.25 3.37a.75.75 0 0 1 0 1.036l-3.25 3.37a.75.75 0 0 1-1.092-.016Z"/>
                </svg>
                {{ $isSeller ? translate('Go to Buyer') : translate('Go to Seller') }}
            </button>
        </form>
    </div>

    {{-- ─── Scoped Styles ───────────────────────────────────────────────────── --}}
    <style>
        .ms-pill {
            align-items: center;
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid rgba(255, 255, 255, 0.14);
            border-radius: 10px;
            display: inline-flex;
            gap: 0;
            height: 34px;
            overflow: hidden;
        }

        .ms-pill__mode {
            align-items: center;
            display: inline-flex;
            font-size: 11px;
            font-weight: 700;
            gap: 5px;
            letter-spacing: 0.02em;
            padding: 0 10px;
            white-space: nowrap;
        }

        .ms-pill__mode--buyer {
            color: #6EE7B7; /* emerald-300 */
        }

        .ms-pill__mode--seller {
            color: #FCD34D; /* amber-300 */
        }

        .ms-pill__divider {
            background: rgba(255, 255, 255, 0.14);
            flex-shrink: 0;
            height: 100%;
            width: 1px;
        }

        .ms-pill__form {
            display: contents;
        }

        .ms-pill__btn {
            align-items: center;
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.75);
            cursor: pointer;
            display: inline-flex;
            font-size: 11px;
            font-weight: 600;
            gap: 5px;
            height: 100%;
            outline: none;
            padding: 0 10px;
            transition: background 180ms ease, color 180ms ease;
            white-space: nowrap;
        }

        .ms-pill__btn:hover {
            background: rgba(255, 255, 255, 0.12);
            color: #FFFFFF;
        }

        .ms-pill__btn:focus-visible {
            background: rgba(255, 255, 255, 0.12);
            box-shadow: inset 0 0 0 2px rgba(255, 255, 255, 0.5);
            color: #FFFFFF;
        }
    </style>
@endif
