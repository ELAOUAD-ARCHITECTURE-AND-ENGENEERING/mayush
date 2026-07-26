(function ($) {
    'use strict';

    var root = $('#translation-diagnostics');
    if (!root.length) return;

    var csrf = $('meta[name="csrf-token"]').attr('content');
    var pollTimer = null;
    var pollFailures = 0;
    var activeRun = root.data('active-run') || null;
    var urls = {
        preview: root.data('preview-url'),
        start: root.data('start-url'),
        progress: root.data('progress-base-url'),
        retry: root.data('retry-base-url')
    };

    function request(options) {
        options.headers = $.extend({}, options.headers, { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' });
        return $.ajax(options);
    }

    function showRun() { $('#translation-run-panel').removeClass('d-none'); }

    function stopPolling() {
        if (pollTimer) {
            window.clearTimeout(pollTimer);
            pollTimer = null;
        }
        window.onbeforeunload = null;
    }

    function renderRun(run) {
        if (!run) return;
        showRun();
        activeRun = run.id;
        var pct = Math.min(100, Math.max(0, Number(run.percentage || 0)));
        $('#translation-run-progress').css('width', pct + '%').attr('aria-valuenow', pct);
        $('#run-processed').text((run.processed || 0) + ' / ' + (run.total || 0));
        $('#run-success').text(run.success || 0);
        $('#run-skipped').text(run.skipped || 0);
        $('#run-failed').text(run.failed || 0);
        $('#translation-run-status').text({ queued: 'En attente de traitement', running: 'Traduction automatique séquentielle en cours', retrying: 'Nouvelle tentative automatique en cours', waiting_for_quota: 'Limite temporaire du service atteinte; reprise automatique', waiting_for_rate_limit: 'Limite temporaire du service atteinte; reprise automatique', paused: 'Traitement en pause', completed: 'Correction terminée', completed_with_errors: 'Terminée avec des erreurs', failed: 'Exécution interrompue' }[run.status] || run.status);
        $('#translation-run-panel').toggleClass('is-paused', run.status === 'paused' || run.status === 'waiting_for_quota' || run.status === 'waiting_for_rate_limit').toggleClass('is-error', run.status === 'failed' || run.status === 'completed_with_errors');
        $('#translation-run-connection').text(run.status === 'paused' ? 'Relance manuelle' : ((run.status === 'waiting_for_quota' || run.status === 'waiting_for_rate_limit') ? 'Reprise automatique programmée' : 'Suivi actif'));
        if (run.current_product) $('#run-current-product').removeClass('d-none').html('Produit en cours : <strong>' + $('<div>').text(run.current_product.name || ('#' + run.current_product.id)).html() + '</strong>');
        else $('#run-current-product').addClass('d-none');
        if (run.failure_reason) $('#run-warning').removeClass('d-none').text(run.failure_reason); else $('#run-warning').addClass('d-none');
        if (run.status === 'paused') {
            $('#run-final-actions').toggleClass('d-none', !(run.failed > 0));
            stopPolling();
            return;
        }
        if (run.status === 'completed' || run.status === 'completed_with_errors' || run.status === 'failed') {
            $('#run-final-actions').toggleClass('d-none', !(run.failed > 0));
            $('#translation-run-result-body').html('<p>La correction a traité <strong>' + (run.processed || 0) + '</strong> produit(s), dont <strong>' + (run.success || 0) + '</strong> corrigé(s) et <strong>' + (run.failed || 0) + '</strong> échec(s).</p>');
            $('#translation-run-result-modal').modal('show');
            stopPolling();
            return;
        }
        window.onbeforeunload = function () { return 'Une correction de traductions est en cours.'; };
        pollTimer = window.setTimeout(poll, 2000);
    }

    function poll() {
        if (!activeRun) return;
        request({ url: urls.progress + '/' + activeRun, method: 'GET' }).done(function (data) {
            pollFailures = 0;
            $('#translation-run-connection').text('Suivi actif');
            renderRun(data.run);
        }).fail(function () {
            pollFailures++;
            if (pollFailures >= 3) {
                $('#translation-run-connection').text('Suivi interrompu');
                $('#run-warning').removeClass('d-none').text('Le suivi de cette exécution est momentanément indisponible. Actualisez la page pour consulter son état.');
                stopPolling();
                return;
            }
            $('#translation-run-connection').text('Reconnexion…');
            pollTimer = window.setTimeout(poll, 4000);
        });
    }

    $('#translation-run-start').on('click', function () {
        request({ url: urls.preview, method: 'GET' }).done(function (data) {
            $('#preview-products').text(data.processable || 0);
            $('#preview-fields').text(data.estimated_fields || 0);
            $('#preview-characters').text(data.estimated_characters || 0);
            $('#translation-run-preview-modal').modal('show');
        }).fail(function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.message) || 'Impossible de préparer la correction.'); });
    });

    $('#translation-run-confirm').on('click', function () {
        var button = $(this).prop('disabled', true);
        request({ url: urls.start, method: 'POST', data: {} }).done(function (data) { $('#translation-run-preview-modal').modal('hide'); renderRun(data.run); }).fail(function (xhr) {
            if (xhr.status === 409 && xhr.responseJSON && xhr.responseJSON.run) {
                $('#translation-run-preview-modal').modal('hide');
                renderRun(xhr.responseJSON.run);
                return;
            }
            alert((xhr.responseJSON && xhr.responseJSON.message) || 'Impossible de démarrer la correction.');
        }).always(function () { button.prop('disabled', false); });
    });

    $('#translation-run-retry').on('click', function () {
        if (!activeRun) return;
        request({ url: urls.retry + '/' + activeRun + '/retry-failed', method: 'POST' }).done(function (data) { renderRun(data.run); }).fail(function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.message) || 'Impossible de relancer les échecs.'); });
    });

    root.on('click', '.js-repair-product', function () {
        var button = $(this), original = button.html();
        button.prop('disabled', true).html('<i class="las la-spinner la-spin"></i>');
        request({ url: button.data('url'), method: 'POST' }).done(function (data) { if (data.success) window.location.reload(); else alert(data.message || 'Le produit n’a pas été corrigé.'); }).fail(function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.message) || 'La correction a échoué.'); }).always(function () { button.prop('disabled', false).html(original); });
    });

    if (activeRun) { showRun(); poll(); }
}(jQuery));
