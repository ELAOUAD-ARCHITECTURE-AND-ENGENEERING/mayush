@extends('frontend.layouts.user_panel')

@section('panel_content')
<div class="aiz-titlebar mt-2 mb-4">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="h3 fw-700 text-dark">{{ translate('Programme Partenaire & Affiliation') }}</h1>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 rounded-lg overflow-hidden mb-4 bg-white">
    <!-- Header Banner -->
    <div class="p-5 text-center text-white position-relative" style="background: linear-gradient(135deg, #111827 0%, #1f2937 100%);">
        <div class="d-inline-flex align-items-center justify-content-center bg-warning text-dark font-weight-bold px-3 py-1 rounded-pill mb-3 fs-12 text-uppercase tracking-wider">
            <i class="las la-clock mr-1"></i> {{ translate('Bientôt disponible') }}
        </div>
        <h2 class="h1 fw-800 mb-3 text-white">{{ translate('Rejoignez le Club Partenaire Mayush Design') }}</h2>
        <p class="fs-16 opacity-80 mx-auto mb-0" style="max-width: 650px;">
            {{ translate('Notre programme d\'affiliation arrive très prochainement. Vous pourrez recommander nos collections de mobilier et décoration haut de gamme et percevoir des commissions exclusives sur chaque vente.') }}
        </p>
    </div>

    <!-- Feature Grid -->
    <div class="card-body p-4 p-md-5">
        <div class="row gutters-15 mb-4">
            <div class="col-md-4 mb-3">
                <div class="p-4 rounded-lg bg-light h-100 text-center border border-light">
                    <div class="btn btn-soft-primary btn-circle btn-icon mb-3" style="width: 54px; height: 54px; pointer-events: none;">
                        <i class="las la-percentage la-2x"></i>
                    </div>
                    <h5 class="fw-700 fs-16 mb-2">{{ translate('Commissions Attractives') }}</h5>
                    <p class="text-muted fs-13 mb-0">
                        {{ translate('Percevez un pourcentage sur chaque commande confirmée via votre lien de parrainage personnel.') }}
                    </p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="p-4 rounded-lg bg-light h-100 text-center border border-light">
                    <div class="btn btn-soft-success btn-circle btn-icon mb-3" style="width: 54px; height: 54px; pointer-events: none;">
                        <i class="las la-chart-line la-2x"></i>
                    </div>
                    <h5 class="fw-700 fs-16 mb-2">{{ translate('Suivi en Temps Réel') }}</h5>
                    <p class="text-muted fs-13 mb-0">
                        {{ translate('Accédez à un tableau de bord intuitif pour analyser vos clics, vos conversions et l\'évolution de vos revenus.') }}
                    </p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="p-4 rounded-lg bg-light h-100 text-center border border-light">
                    <div class="btn btn-soft-warning btn-circle btn-icon mb-3" style="width: 54px; height: 54px; pointer-events: none;">
                        <i class="las la-wallet la-2x"></i>
                    </div>
                    <h5 class="fw-700 fs-16 mb-2">{{ translate('Paiements Sécurisés') }}</h5>
                    <p class="text-muted fs-13 mb-0">
                        {{ translate('Recevez directement vos gains chaque mois sur votre compte bancaire ou moyen de paiement favori.') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="text-center pt-3 border-top">
            <a href="{{ route('home') }}" class="btn btn-primary btn-lg px-5 shadow-sm rounded-pill">
                <i class="las la-shopping-bag mr-2"></i>{{ translate('Découvrir nos Produits') }}
            </a>
        </div>
    </div>
</div>
@endsection
