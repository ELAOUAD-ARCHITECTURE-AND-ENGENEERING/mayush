@extends('frontend.layouts.app')
@section('hide_default_frontend_nav', '1')
@section('hide_default_frontend_footer', '1')
@section('content')
<style>
    /* ══ Shell ══════════════════════════════════════════════════════════════ */
    .buyer-account-shell {
        background: linear-gradient(170deg, #F0FDFA 0%, #F8FAFC 40%, #FFFFFF 100%);
        min-height: 100vh;
    }

    /* ══ Top Navbar ════════════════════════════════════════════════════════ */
    .buyer-dashboard-navbar {
        background: #111827;
        border-bottom: 1px solid rgba(255, 255, 255, 0.07);
        box-shadow: 0 4px 20px rgba(17, 24, 39, 0.22);
        color: #FFFFFF;
        position: sticky;
        top: 0;
        z-index: 1020;
    }

    .buyer-dashboard-navbar-inner {
        min-height: 66px;
    }

    /* ── Brand ──────────────────────────────────────────────────────────── */
    .buyer-dashboard-brand {
        align-items: center;
        color: #FFFFFF;
        display: inline-flex;
        font-family: var(--mayush-font-body);
        font-size: 14px;
        font-weight: 700;
        gap: 0;
        letter-spacing: 0;
        text-decoration: none;
    }

    .buyer-dashboard-brand:hover { color: #FFFFFF; text-decoration: none; }

    .buyer-dashboard-brand img {
        max-height: 32px;
        max-width: 108px;
        object-fit: contain;
    }

    .buyer-dashboard-brand-divider {
        background: rgba(255, 255, 255, 0.18);
        display: inline-block;
        height: 22px;
        margin: 0 12px;
        width: 1px;
    }

    /* ── Quick-jump nav (slim — 3 links max) ────────────────────────────── */
    .buyer-dashboard-nav {
        align-items: center;
        display: flex;
        gap: 4px;
    }

    .buyer-dashboard-nav-link {
        align-items: center;
        border: 1px solid transparent;
        border-radius: 7px;
        color: rgba(255, 255, 255, 0.72);
        display: inline-flex;
        font-size: 12.5px;
        font-weight: 600;
        gap: 6px;
        min-height: 34px;
        padding: 7px 10px;
        text-decoration: none;
        transition: background 160ms ease, border-color 160ms ease, color 160ms ease;
        white-space: nowrap;
    }

    .buyer-dashboard-nav-link svg { flex-shrink: 0; }

    .buyer-dashboard-nav-link:hover,
    .buyer-dashboard-nav-link.active {
        background: rgba(20, 184, 166, 0.14);
        border-color: rgba(20, 184, 166, 0.38);
        color: #FFFFFF;
        text-decoration: none;
    }

    /* ── Right actions row ──────────────────────────────────────────────── */
    .buyer-dashboard-actions {
        align-items: center;
        display: flex;
        gap: 8px;
    }

    .buyer-dashboard-notification {
        align-items: center;
        display: inline-flex;
        flex: 0 0 auto;
    }

    /* ── User chip ──────────────────────────────────────────────────────── */
    .buyer-dashboard-user {
        color: #FFFFFF;
        line-height: 1.2;
    }

    .buyer-dashboard-user small {
        color: rgba(255, 255, 255, 0.55);
        display: block;
        font-size: 10.5px;
        font-weight: 500;
    }

    .buyer-dashboard-avatar {
        border: 2px solid rgba(20, 184, 166, 0.4);
    }

    /* ══ Content Section ═══════════════════════════════════════════════════ */
    .buyer-account-section {
        padding: 22px 0 40px;
    }

    /* ── Page header card ───────────────────────────────────────────────── */
    .buyer-account-header {
        background: linear-gradient(135deg, #F0FDFA 0%, #FFFFFF 80%);
        border: 1px solid #99F6E4;
        border-radius: 10px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05), 0 6px 18px rgba(20, 184, 166, 0.07);
        margin-bottom: 20px;
        padding: 16px 20px;
    }

    .buyer-account-title {
        color: #0F766E;
        font-family: var(--mayush-font-body);
        font-size: 20px;
        font-weight: 700;
        line-height: 1.25;
        margin-bottom: 2px;
    }

    .buyer-account-subtitle {
        color: #64748B;
        font-size: 12.5px;
        font-weight: 500;
    }

    .buyer-account-avatar {
        border: 2px solid #99F6E4;
        box-shadow: 0 4px 12px rgba(20, 184, 166, 0.14);
    }

    /* ══ Two-column layout ══════════════════════════════════════════════════ */
    .buyer-account-layout {
        align-items: flex-start;
        display: flex;
        gap: 20px;
    }

    .buyer-account-layout .aiz-user-panel {
        flex: 1;
        min-width: 0;
    }

    /* ── Responsive ─────────────────────────────────────────────────────── */
    @media (max-width: 1199.98px) {
        .buyer-account-layout { flex-direction: column; }
        .bdash-sidebar { display: none; }
    }

    @media (max-width: 991.98px) {
        .buyer-dashboard-navbar { position: static; }
        .buyer-dashboard-navbar-inner { padding: 12px 0; }
        .buyer-dashboard-nav { margin-top: 10px; order: 3; width: 100%; }
        .buyer-dashboard-actions {
            justify-content: flex-end;
            margin-top: 8px;
            width: 100%;
        }
    }

    @media (max-width: 767.98px) {
        .buyer-dashboard-brand-divider,
        .buyer-dashboard-brand > span { display: none; }
        .buyer-dashboard-nav { overflow-x: auto; flex-wrap: nowrap; padding-bottom: 2px; }
    }

    @media (prefers-reduced-motion: reduce) {
        .buyer-dashboard-nav-link,
        .buyer-dashboard-brand { transition: none !important; }
    }
</style>
@php
    $panelUser = auth()->user();
    $panelAvatar = $panelUser && $panelUser->avatar_original ? uploaded_asset($panelUser->avatar_original) : static_asset('assets/img/avatar-place.png');
@endphp
<div class="buyer-account-shell">
    <header class="buyer-dashboard-navbar">
        <div class="container">
            <div class="buyer-dashboard-navbar-inner d-lg-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center justify-content-between">
                    <a href="{{ route('dashboard') }}" class="buyer-dashboard-brand">
                        <img src="{{ uploaded_asset(get_setting('header_logo')) }}" alt="{{ get_setting('site_name') }}" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/logo.png') }}';">
                        <span class="buyer-dashboard-brand-divider"></span>
                        <span>{{ translate('Buyer Dashboard') }}</span>
                    </a>
                    <div class="d-lg-none d-flex align-items-center">
                        <span class="avatar avatar-sm buyer-dashboard-avatar mr-2">
                            <img src="{{ $panelAvatar }}" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';" alt="{{ translate('avatar') }}">
                        </span>
                    </div>
                </div>

                {{-- Quick-jump: Overview · Orders · Shop (full nav lives in sidebar) --}}
                <nav class="buyer-dashboard-nav" aria-label="{{ translate('Buyer dashboard quick navigation') }}">
                    <a href="{{ route('dashboard') }}" class="buyer-dashboard-nav-link {{ areActiveRoutes(['dashboard'], 'active') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 18" fill="currentColor" width="15" height="15" aria-hidden="true">
                            <path fill-rule="evenodd" d="M8.293 1.793a1 1 0 0 1 1.414 0l6.5 6.5a1 1 0 0 1-1.414 1.414L14 9.914V16.5a.5.5 0 0 1-.5.5H11a.5.5 0 0 1-.5-.5V13h-3v3.5a.5.5 0 0 1-.5.5H4.5a.5.5 0 0 1-.5-.5V9.914l-.793.793a1 1 0 1 1-1.414-1.414l6.5-6.5Z" clip-rule="evenodd"/>
                        </svg>
                        {{ translate('Overview') }}
                    </a>
                    <a href="{{ route('purchase_history.index') }}" class="buyer-dashboard-nav-link {{ areActiveRoutes(['purchase_history.index', 'purchase_history.details'], 'active') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 18" fill="currentColor" width="15" height="15" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.5 4.5a.5.5 0 0 1 1 0V5h5v-.5a.5.5 0 0 1 1 0V5h.25A2.25 2.25 0 0 1 15 7.25v7A2.25 2.25 0 0 1 12.75 16.5H5.25A2.25 2.25 0 0 1 3 14.25v-7A2.25 2.25 0 0 1 5.25 5H5.5V4.5ZM4.5 8.25v6a.75.75 0 0 0 .75.75h7.5a.75.75 0 0 0 .75-.75v-6H4.5Z" clip-rule="evenodd"/>
                        </svg>
                        {{ translate('Orders') }}
                    </a>
                    <a href="{{ route('home') }}" class="buyer-dashboard-nav-link">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 18 18" fill="currentColor" width="15" height="15" aria-hidden="true">
                            <path d="M1.5 10.75a.75.75 0 0 1 .75-.75h.5A.75.75 0 0 1 3.5 10.75V14h10.5v-3.25a.75.75 0 0 1 .75-.75h.5a.75.75 0 0 1 .75.75V14.75A.75.75 0 0 1 15.25 15.5H2.75A.75.75 0 0 1 2 14.75V10.75ZM9 2.5A.75.75 0 0 0 8.25 3v6.69L5.78 7.22a.75.75 0 1 0-1.06 1.06l3.75 3.75a.75.75 0 0 0 1.06 0l3.75-3.75a.75.75 0 1 0-1.06-1.06L9.75 9.69V3A.75.75 0 0 0 9 2.5Z"/>
                        </svg>
                        {{ translate('Shop') }}
                    </a>
                </nav>

                <div class="buyer-dashboard-actions">
                    {{-- Mode switcher pill --}}
                    @if (can_switch_account_mode())
                        @include('partials.account_mode_switcher')
                    @endif
                    {{-- Shared inbox entry point; its runtime owns fetch and realtime updates. --}}
                    <div class="buyer-dashboard-notification">
                        @include('partials.notification-center-trigger', ['variant' => 'buyer'])
                    </div>
                    {{-- User chip --}}
                    <div class="d-none d-lg-flex align-items-center" style="gap:8px;">
                        <span class="avatar avatar-sm buyer-dashboard-avatar">
                            <img src="{{ $panelAvatar }}" onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';" alt="{{ translate('avatar') }}">
                        </span>
                        <div class="buyer-dashboard-user">
                            <span style="font-size:13px;font-weight:700;">{{ $panelUser?->name }}</span>
                            <small>{{ translate('Buyer account') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <section class="buyer-account-section">
        <div class="container">

            {{-- Page header card --}}
            <div class="buyer-account-header">
                <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:12px;">
                    <div class="d-flex align-items-center" style="gap:14px;">
                        <span class="avatar avatar-md buyer-account-avatar" style="width:48px;height:48px;flex-shrink:0;">
                            <img
                                src="{{ $panelAvatar }}"
                                onerror="this.onerror=null;this.src='{{ static_asset('assets/img/avatar-place.png') }}';"
                                alt="{{ $panelUser?->name }}"
                            >
                        </span>
                        <div>
                            <div class="buyer-account-title">@yield('page_title', translate('Buyer Dashboard'))</div>
                            <div class="buyer-account-subtitle">
                                {{ translate('Welcome back') }},
                                <strong style="color:#0F766E;">{{ $panelUser?->name }}</strong>
                            </div>
                        </div>
                    </div>
                    {{-- Breadcrumb / page context --}}
                    <div class="d-none d-md-flex" style="align-items:center;gap:6px;font-size:12px;color:#94A3B8;">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" width="13" height="13" aria-hidden="true" style="color:#14B8A6;">
                            <path fill-rule="evenodd" d="M8.36 1.37 13.5 6.218v6.783A1.5 1.5 0 0 1 12 14.5H4a1.5 1.5 0 0 1-1.5-1.5V6.218L7.64 1.37a.5.5 0 0 1 .72 0ZM7.25 9.5a.75.75 0 0 0 0 1.5h1.5a.75.75 0 0 0 0-1.5h-1.5Z" clip-rule="evenodd"/>
                        </svg>
                        <a href="{{ route('home') }}" style="color:inherit;text-decoration:none;">{{ translate('Store') }}</a>
                        <span aria-hidden="true">›</span>
                        <span style="color:#475569;font-weight:600;">@yield('page_title', translate('Dashboard'))</span>
                    </div>
                </div>
            </div>

            {{-- Two-column layout: sidebar + main --}}
            <div class="buyer-account-layout">
                @include('frontend.inc.user_side_nav')
                <div class="aiz-user-panel">
                    @yield('panel_content')
                </div>
            </div>

        </div>
    </section>
</div>
@endsection
