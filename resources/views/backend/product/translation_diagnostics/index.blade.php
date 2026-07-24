@extends('backend.layouts.app')

@section('style')
<style>
    .translation-diagnostics .diagnostic-card { border: 1px solid #edf0f5; transition: transform .2s ease, box-shadow .2s ease; }
    .translation-diagnostics .diagnostic-card:hover, .translation-diagnostics .diagnostic-card:focus-within { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(35,39,52,.08); }
    .translation-diagnostics .diagnostic-number { font-size: 1.55rem; line-height: 1; }
    .translation-diagnostics .diagnostic-dot { width: 9px; height: 9px; border-radius: 50%; display: inline-block; }
    .translation-diagnostics .status-complete { color: #137333; background: #e8f5e9; }
    .translation-diagnostics .status-partial { color: #936b00; background: #fff8dc; }
    .translation-diagnostics .status-missing_arabic, .translation-diagnostics .status-failed { color: #b42318; background: #fff0ee; }
    .translation-diagnostics .status-missing_french_source { color: #6b4e8a; background: #f4effa; }
    .translation-diagnostics .status-contains_french_in_arabic { color: #9a3412; background: #fff3e8; }
    .translation-diagnostics .status-pill { border-radius: 20px; padding: 4px 9px; font-size: 11px; font-weight: 600; white-space: nowrap; }
    .translation-diagnostics .translation-preview { max-width: 330px; white-space: normal; word-break: break-word; }
    .translation-diagnostics .run-panel { border-left: 4px solid #009ef7; }
    .translation-diagnostics .run-panel.is-paused { border-left-color: #ffc700; }
    .translation-diagnostics .run-panel.is-error { border-left-color: #f0416c; }
    .translation-diagnostics .progress { height: 10px; border-radius: 10px; background: #edf2f7; }
    .translation-diagnostics .progress-bar { transition: width .25s ease; }
    .translation-diagnostics .focus-ring:focus { outline: 3px solid rgba(0,158,247,.35); outline-offset: 2px; }
    @media (prefers-reduced-motion: reduce) { .translation-diagnostics * { transition: none !important; animation: none !important; } }
</style>
@endsection

@section('content')
@php
    $statusLabels = [
        'complete' => 'Complète',
        'partial' => 'Partielle',
        'missing_arabic' => 'Arabe manquant',
        'missing_french_source' => 'Source française incomplète',
        'contains_french_in_arabic' => 'Français présent',
        'failed' => 'Échec récent',
    ];
    $statusClasses = [
        'complete' => 'success', 'partial' => 'warning', 'missing_arabic' => 'danger',
        'missing_french_source' => 'info', 'contains_french_in_arabic' => 'warning', 'failed' => 'danger',
    ];
@endphp
<div class="translation-diagnostics" id="translation-diagnostics"
    data-preview-url="{{ route('admin.product_translation_diagnostics.preview') }}"
    data-start-url="{{ route('admin.product_translation_diagnostics.start') }}"
    data-progress-base-url="{{ url('/admin/products/translation-diagnostics/runs') }}"
    data-retry-base-url="{{ url('/admin/products/translation-diagnostics/runs') }}"
    data-target-language="{{ $diagnoses ? ($diagnoses[array_key_first($diagnoses)]['target_language'] ?? 'ma') : 'ma' }}"
    data-active-run="{{ $activeRun?->id }}">
    <div class="aiz-titlebar d-flex align-items-center justify-content-between flex-wrap mb-3">
        <div>
            <h1 class="h3 fw-700 mb-1">Diagnostic des traductions</h1>
            <p class="text-muted mb-0">Contrôlez le français source et corrigez les contenus arabes sans écraser les valeurs valides.</p>
        </div>
        <button type="button" class="btn btn-primary mt-2 mt-md-0 focus-ring" id="translation-run-start">
            <i class="las la-language mr-1"></i> Corriger les traductions
        </button>
    </div>

    <div id="translation-run-panel" class="card run-panel mb-3 d-none" aria-live="polite">
        <div class="card-body">
            <div class="d-flex align-items-start justify-content-between flex-wrap">
                <div><h2 class="h5 mb-1">Correction en arrière-plan</h2><p class="text-muted mb-0" id="translation-run-status">Préparation…</p></div>
                <span class="badge badge-soft-primary mt-1" id="translation-run-connection">Suivi actif</span>
            </div>
            <div class="progress mt-3" role="progressbar" aria-label="Progression de la correction" aria-valuemin="0" aria-valuemax="100"><div id="translation-run-progress" class="progress-bar bg-primary" style="width:0%"></div></div>
            <div class="row mt-3 small text-muted">
                <div class="col-6 col-md-3 mb-2">Traités <strong class="d-block text-dark" id="run-processed">0 / 0</strong></div>
                <div class="col-6 col-md-3 mb-2">Corrigés <strong class="d-block text-dark" id="run-success">0</strong></div>
                <div class="col-6 col-md-3 mb-2">Ignorés <strong class="d-block text-dark" id="run-skipped">0</strong></div>
                <div class="col-6 col-md-3 mb-2">Échecs <strong class="d-block text-dark" id="run-failed">0</strong></div>
            </div>
            <div class="small text-muted d-none" id="run-current-product"></div>
            <div class="alert alert-warning mt-3 mb-0 d-none" id="run-warning"></div>
            <div class="mt-3 d-none" id="run-final-actions"><button type="button" class="btn btn-sm btn-outline-danger" id="translation-run-retry"><i class="las la-redo mr-1"></i> Réessayer les échecs</button></div>
        </div>
    </div>

    <div class="row gutters-16 mb-3">
        @foreach ([
            ['key'=>'total','label'=>'Produits analysés','icon'=>'la-layer-group','class'=>'primary'],
            ['key'=>'complete','label'=>'Complètes','icon'=>'la-check-circle','class'=>'success'],
            ['key'=>'partial','label'=>'Partielles','icon'=>'la-adjust','class'=>'warning'],
            ['key'=>'missing_arabic','label'=>'Arabe manquant','icon'=>'la-language','class'=>'danger'],
            ['key'=>'missing_french_source','label'=>'Source incomplète','icon'=>'la-file-alt','class'=>'info'],
            ['key'=>'failed','label'=>'Échecs récents','icon'=>'la-exclamation-triangle','class'=>'danger'],
        ] as $card)
        <div class="col-6 col-xl-2 mb-3">
            <a class="card diagnostic-card h-100 text-reset text-decoration-none" href="{{ request()->fullUrlWithQuery(['status' => $card['key'] === 'total' ? null : $card['key'], 'page' => 1]) }}" aria-label="Filtrer: {{ $card['label'] }}">
                <div class="card-body d-flex align-items-center justify-content-between p-3"><div><div class="small text-muted mb-2">{{ $card['label'] }}</div><div class="diagnostic-number fw-700">{{ $summary[$card['key']] ?? 0 }}</div></div><i class="las {{ $card['icon'] }} fs-30 text-{{ $card['class'] }} opacity-7"></i></div>
            </a>
        </div>
        @endforeach
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row align-items-end gutters-8">
                <div class="col-12 col-md-4 mb-2"><label class="small text-muted" for="diagnostic-search">Recherche</label><input id="diagnostic-search" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="ID, nom ou description"></div>
                <div class="col-6 col-md-2 mb-2"><label class="small text-muted" for="diagnostic-status">Statut</label><select id="diagnostic-status" name="status" class="form-control form-control-sm"><option value="">Tous</option>@foreach($statusOptions as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach</select></div>
                <div class="col-6 col-md-2 mb-2"><label class="small text-muted" for="diagnostic-brand">Marque</label><select id="diagnostic-brand" name="brand_id" class="form-control form-control-sm"><option value="">Toutes</option>@foreach($brands as $brand)<option value="{{ $brand->id }}" @selected((string)request('brand_id') === (string)$brand->id)>{{ $brand->name }}</option>@endforeach</select></div>
                <div class="col-6 col-md-2 mb-2"><label class="small text-muted" for="diagnostic-category">Catégorie</label><select id="diagnostic-category" name="category_id" class="form-control form-control-sm"><option value="">Toutes</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((string)request('category_id') === (string)$category->id)>{{ $category->name }}</option>@endforeach</select></div>
                <div class="col-6 col-md-2 mb-2"><button class="btn btn-sm btn-primary w-100" type="submit"><i class="las la-filter mr-1"></i> Filtrer</button></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center"><h2 class="h5 mb-0">État produit par produit</h2><span class="small text-muted">{{ $products->total() }} résultats</span></div>
        <div class="table-responsive">
            <table class="table aiz-table mb-0">
                <thead><tr><th>Produit</th><th>Français</th><th>Arabe</th><th>Statut</th><th>Champs concernés</th><th class="text-right">Action</th></tr></thead>
                <tbody>
                @forelse($products as $product)
                    @php $diagnosis = $diagnoses[$product->id] ?? null; $status = $diagnosis['status'] ?? 'missing_arabic'; @endphp
                    <tr>
                        <td class="align-middle"><div class="d-flex align-items-center"><img src="{{ uploaded_asset($product->thumbnail_img) }}" width="42" height="42" class="rounded mr-2" style="object-fit:cover" alt=""><div><div class="fw-600">{{ $product->id }} · {{ Str::limit($product->name, 42) }}</div><a class="small text-primary" href="{{ route('products.admin.edit', ['id'=>$product->id, 'lang'=>$diagnosis['target_language'] ?? 'ma']) }}">Ouvrir l’onglet arabe <i class="las la-external-link-alt"></i></a></div></div></td>
                        <td class="align-middle"><div class="translation-preview">{!! $diagnosis['fields']['name']['source'] ?? '—' !!}</div></td>
                        <td class="align-middle"><div class="translation-preview text-right" dir="rtl">{!! $diagnosis['fields']['name']['target'] ?? '—' !!}</div></td>
                        <td class="align-middle"><span class="status-pill status-{{ $status }}">{{ $statusLabels[$status] ?? $status }}</span></td>
                        <td class="align-middle"><div class="small">{{ implode(', ', $diagnosis['missing_fields'] ?? []) ?: 'Aucun' }}</div>@if(!empty($diagnosis['last_error']))<div class="small text-danger mt-1">{{ Str::limit($diagnosis['last_error'], 80) }}</div>@endif</td>
                        <td class="align-middle text-right"><button type="button" class="btn btn-sm btn-soft-primary js-repair-product" data-url="{{ route('admin.product_translation_diagnostics.repair', ['product'=>$product->id]) }}" data-name="{{ $product->name }}" @disabled($status === 'complete' || $status === 'missing_french_source')><i class="las la-magic"></i><span class="sr-only">Réparer {{ $product->name }}</span></button></td>
                    </tr>
                @empty <tr><td colspan="6" class="text-center py-5 text-muted">Aucun produit ne correspond à ces filtres.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $products->links() }}</div>
    </div>
</div>

<div class="modal fade" id="translation-run-preview-modal" tabindex="-1" role="dialog" aria-labelledby="translation-run-preview-title" aria-hidden="true"><div class="modal-dialog modal-dialog-centered" role="document"><div class="modal-content"><div class="modal-header"><h2 class="modal-title h5" id="translation-run-preview-title">Confirmer la correction</h2><button type="button" class="close" data-dismiss="modal" aria-label="Fermer"><span aria-hidden="true">&times;</span></button></div><div class="modal-body"><p>La correction utilise Azure et traite les produits un par un. Les champs arabes déjà valides seront conservés.</p><dl class="row mb-0"><dt class="col-7">Produits concernés</dt><dd class="col-5 text-right" id="preview-products">—</dd><dt class="col-7">Champs estimés</dt><dd class="col-5 text-right" id="preview-fields">—</dd><dt class="col-7">Caractères estimés</dt><dd class="col-5 text-right" id="preview-characters">—</dd></dl><div class="alert alert-warning mt-3 mb-0 small">Une seule exécution peut être active à la fois.</div></div><div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Annuler</button><button type="button" class="btn btn-primary" id="translation-run-confirm"><i class="las la-play mr-1"></i> Démarrer</button></div></div></div></div>
<div class="modal fade" id="translation-run-result-modal" tabindex="-1" role="dialog" aria-labelledby="translation-run-result-title" aria-hidden="true"><div class="modal-dialog modal-dialog-centered" role="document"><div class="modal-content"><div class="modal-header"><h2 class="modal-title h5" id="translation-run-result-title">Correction terminée</h2></div><div class="modal-body" id="translation-run-result-body"></div><div class="modal-footer"><button type="button" class="btn btn-primary" data-dismiss="modal">Fermer</button></div></div></div></div>
@endsection

@section('script')
<script src="{{ static_asset('assets/js/mayush-translation-diagnostics.js') }}"></script>
@endsection
