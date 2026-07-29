@php
    $currentLang = $lang ?? request()->get('lang', 'fr');
    $activeLanguages = collect(get_all_active_language());
    $configuredTargetLanguage = (string) config('product_translation.target_language', 'ma');
    $arabicLanguageCode = optional($activeLanguages->first(function ($language) use ($configuredTargetLanguage) {
        return (string) $language->code === $configuredTargetLanguage;
    }))->code;
    $arabicLanguageCode = $arabicLanguageCode ?: optional($activeLanguages->first(function ($language) {
        return (int) ($language->rtl ?? 0) === 1;
    }))->code ?: 'ar';
    $serverFrenchSource = [];
    if (isset($product) && $product instanceof \App\Models\Product) {
        $serverFrenchSource = app(\App\Services\ProductTranslationStatusService::class)->sourceValues($product, false);
    }
@endphp

<!-- Button container (displayed when Arabic tab is active) -->
<div id="copy-french-btn-wrapper" class="mb-3 text-right" data-arabic-language="{{ $arabicLanguageCode }}" data-french-source-fields="{{ e(json_encode($serverFrenchSource, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) }}" style="{{ $currentLang == $arabicLanguageCode ? '' : 'display: none;' }}">
    <button type="button" class="btn btn-sm btn-soft-primary fw-600" id="btn-copy-french-content">
        <i class="las la-copy mr-1 fs-16"></i> {{ translate('Copier le contenu français') }}
    </button>
    <button type="button" class="btn btn-sm btn-soft-success fw-600 ml-1" id="btn-translate-arabic-content"
        data-translate-url="{{ request()->routeIs('seller.*') ? route('seller.products.translate_to_arabic') : route('products.translate_to_arabic') }}">
        <i class="las la-language mr-1 fs-16"></i> {{ translate('Traduire le contenu en arabe') }}
    </button>
</div>

<!-- Modal confirmation for existing Arabic content -->
<div class="modal fade" id="modal-copy-french-confirm" tabindex="-1" role="dialog" aria-labelledby="modalCopyFrenchTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-600" id="modalCopyFrenchTitle">{{ translate('Copier le contenu français') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ translate('Close') }}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="fs-14 mb-0">
                    {{ translate('Certains champs de la version arabe contiennent déjà des données. Souhaitez-vous remplir uniquement les champs vides ou remplacer toutes les données arabes par la version française ?') }}
                </p>
            </div>
            <div class="modal-footer flex-column flex-sm-row">
                <button type="button" class="btn btn-soft-primary btn-sm mb-2 mb-sm-0 w-100 w-sm-auto" id="btn-fill-empty-only">
                    {{ translate('Remplir uniquement les champs vides') }}
                </button>
                <button type="button" class="btn btn-danger btn-sm mb-2 mb-sm-0 w-100 w-sm-auto" id="btn-replace-all">
                    {{ translate('Remplacer tout le contenu arabe') }}
                </button>
                <button type="button" class="btn btn-light btn-sm w-100 w-sm-auto" data-dismiss="modal">
                    {{ translate('Annuler') }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal confirmation for existing Arabic text before translation -->
<div class="modal fade" id="modal-translate-arabic-confirm" tabindex="-1" role="dialog" aria-labelledby="modalTranslateArabicTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-600" id="modalTranslateArabicTitle">{{ translate('Traduire le contenu en arabe') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{ translate('Close') }}"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="fs-14 mb-0">{{ translate('Certains champs contiennent déjà du texte arabe. Choisissez les champs à traduire.') }}</p>
            </div>
            <div class="modal-footer flex-column flex-sm-row">
                <button type="button" class="btn btn-soft-primary btn-sm mb-2 mb-sm-0 w-100 w-sm-auto" id="btn-translate-french-only">{{ translate('Traduire uniquement les champs encore en français') }}</button>
                <button type="button" class="btn btn-danger btn-sm mb-2 mb-sm-0 w-100 w-sm-auto" id="btn-retranslate-all">{{ translate('Retraduire tous les champs textuels') }}</button>
                <button type="button" class="btn btn-light btn-sm w-100 w-sm-auto" data-dismiss="modal">{{ translate('Annuler') }}</button>
            </div>
        </div>
    </div>
</div>

<script defer src="{{ static_asset('assets/js/mayush-copy-french-content.js') }}?v={{ file_exists(public_path('assets/js/mayush-copy-french-content.js')) ? filemtime(public_path('assets/js/mayush-copy-french-content.js')) : time() }}"></script>
