@php
    $currentLang = $lang ?? request()->get('lang', 'fr');
@endphp

<!-- Button container (displayed when Arabic tab is active) -->
<div id="copy-french-btn-wrapper" class="mb-3 text-right" style="{{ $currentLang == 'ar' ? '' : 'display: none;' }}">
    <button type="button" class="btn btn-sm btn-soft-primary fw-600" id="btn-copy-french-content">
        <i class="las la-copy mr-1 fs-16"></i> {{ translate('Copier le contenu français') }}
    </button>
</div>

<!-- Modal confirmation for existing Arabic content -->
<div class="modal fade" id="modal-copy-french-confirm" tabindex="-1" role="dialog" aria-labelledby="modalCopyFrenchTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-600" id="modalCopyFrenchTitle">{{ translate('Copier le contenu français') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
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

<script src="{{ static_asset('assets/js/mayush-copy-french-content.js') }}"></script>
