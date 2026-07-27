(function ($) {
    'use strict';

    var root = $('#translation-diagnostics');
    if (!root.length) return;

    var csrf = $('meta[name="csrf-token"]').attr('content');
    var pollTimer = null;
    var retryCooldownTimer = null;
    var pollFailures = 0;
    var activeRun = root.data('active-run') || null;
    var urls = {
        preview: root.data('preview-url'),
        start: root.data('start-url'),
        progress: root.data('progress-base-url'),
        retry: root.data('retry-base-url'),
        stop: root.data('stop-base-url')
    };

    function request(options) {
        options.headers = $.extend({}, options.headers, { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' });
        return $.ajax(options);
    }

    function showRun() { $('#translation-run-panel').removeClass('d-none'); }

    function retryAfterSeconds(xhr) {
        var headerValue = xhr.getResponseHeader('Retry-After');
        var bodyValue = xhr.responseJSON && xhr.responseJSON.result && xhr.responseJSON.result.retry_after;
        var seconds = parseInt(headerValue || bodyValue || 60, 10);
        return isNaN(seconds) ? 60 : Math.max(1, Math.min(seconds, 3600));
    }

    function holdRepairButton(button, original, seconds) {
        var retryAt = new Date(Date.now() + seconds * 1000);
        button.prop('disabled', true)
            .html('<i class="las la-clock mr-1"></i> Réessayez après ' + retryAt.toLocaleTimeString())
            .attr('title', 'OpenRouter limite temporaire. Réessayez après ' + retryAt.toLocaleString());
        window.setTimeout(function () {
            button.prop('disabled', false).html(original).removeAttr('title');
        }, seconds * 1000);
    }

    function renderRetryCooldown(run) {
        var retryButton = $('#translation-run-retry');
        var retryAt = run.next_retry_at ? new Date(run.next_retry_at) : null;
        var blocked = run.status === 'paused' && retryAt && !isNaN(retryAt.getTime()) && retryAt.getTime() > Date.now();

        if (retryCooldownTimer) {
            window.clearTimeout(retryCooldownTimer);
            retryCooldownTimer = null;
        }
        retryButton.prop('disabled', !!blocked);
        if (blocked) {
            retryButton.attr('title', 'Réessayez après ' + retryAt.toLocaleString());
            $('#translation-run-connection').text('Relance disponible après ' + retryAt.toLocaleTimeString());
            retryCooldownTimer = window.setTimeout(function () { renderRun(run); }, Math.max(1, retryAt.getTime() - Date.now() + 100));
        } else {
            retryButton.removeAttr('title');
        }
    }

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
        var isRunning = ['queued', 'running', 'retrying', 'waiting_for_quota', 'waiting_for_rate_limit', 'paused'].indexOf(run.status) !== -1;
        $('#translation-run-stop').toggleClass('d-none', !isRunning);
        var pct = Math.min(100, Math.max(0, Number(run.percentage || 0)));
        $('#translation-run-progress').css('width', pct + '%').attr('aria-valuenow', pct);
        $('#run-processed').text((run.processed || 0) + ' / ' + (run.total || 0));
        $('#run-success').text(run.success || 0);
        $('#run-skipped').text(run.skipped || 0);
        $('#run-failed').text(run.failed || 0);
        $('#translation-run-status').text({ queued: 'En attente de traitement', running: 'Traduction automatique séquentielle en cours', retrying: 'Nouvelle tentative automatique en cours', waiting_for_quota: 'Limite temporaire du service atteinte; reprise automatique', waiting_for_rate_limit: 'Limite temporaire du service atteinte; reprise automatique', paused: 'Traitement en pause', completed: 'Correction terminée', completed_with_errors: 'Terminée avec des erreurs', failed: 'Exécution interrompue' }[run.status] || run.status);
        $('#translation-run-panel').toggleClass('is-paused', run.status === 'paused' || run.status === 'waiting_for_quota' || run.status === 'waiting_for_rate_limit').toggleClass('is-error', run.status === 'failed' || run.status === 'completed_with_errors');
        $('#translation-run-connection').text(run.status === 'paused' ? 'Relance manuelle' : ((run.status === 'waiting_for_quota' || run.status === 'waiting_for_rate_limit') ? 'Reprise automatique programmée' : 'Suivi actif'));
        renderRetryCooldown(run);
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

    function showNoticeModal(message, title, iconClass, btnClass) {
        title = title || 'Information';
        iconClass = iconClass || 'la-info-circle text-primary';
        btnClass = btnClass || 'btn-primary';

        $('#translation-notice-title').text(title);
        $('#translation-notice-message').text(message);
        $('#notice-modal-icon').attr('class', 'las ' + iconClass);
        $('#translation-notice-cancel-btn').addClass('d-none');
        $('#translation-notice-confirm-btn').attr('class', 'btn btn-sm px-4 ' + btnClass).text('OK').off('click');
        $('#translation-notice-modal').modal('show');
    }

    function showConfirmModal(message, title, onConfirm) {
        title = title || 'Confirmation';
        $('#translation-notice-title').text(title);
        $('#translation-notice-message').text(message);
        $('#notice-modal-icon').attr('class', 'las la-question-circle text-warning');
        $('#translation-notice-cancel-btn').removeClass('d-none');
        $('#translation-notice-confirm-btn').attr('class', 'btn btn-sm btn-danger px-4').text('Confirmer').off('click').on('click', function () {
            if (typeof onConfirm === 'function') onConfirm();
        });
        $('#translation-notice-modal').modal('show');
    }

    var maxProcessable = 0;

    $('#translation-run-start').on('click', function () {
        request({ url: urls.preview, method: 'GET' }).done(function (data) {
            maxProcessable = parseInt(data.processable || 0, 10);
            $('#preview-products').text(maxProcessable);
            $('#preview-fields').text(data.estimated_fields || 0);
            $('#preview-limit-total-addon').text('/ ' + maxProcessable);
            var limitInput = $('#preview-limit-input');
            limitInput.attr('max', maxProcessable > 0 ? maxProcessable : 1);
            if (!limitInput.val() || parseInt(limitInput.val(), 10) > maxProcessable || parseInt(limitInput.val(), 10) < 1) {
                limitInput.val(maxProcessable > 0 ? maxProcessable : '');
            }
            $('#translation-run-preview-modal').modal('show');
        }).fail(function (xhr) {
            showNoticeModal((xhr.responseJSON && xhr.responseJSON.message) || 'Impossible de préparer la correction.', 'Erreur', 'la-exclamation-triangle text-danger', 'btn-danger');
        });
    });

    $('#translation-run-confirm').on('click', function () {
        var limitInput = $('#preview-limit-input');
        var limitVal = parseInt(limitInput.val(), 10);
        if (isNaN(limitVal) || limitVal < 1) {
            showNoticeModal('Le nombre de produits à traduire doit être supérieur ou égal à 1.', 'Attention', 'la-exclamation-circle text-warning', 'btn-warning');
            limitInput.focus();
            return;
        }
        if (maxProcessable > 0 && limitVal > maxProcessable) {
            showNoticeModal('Le nombre de produits à traduire ne peut pas dépasser le total des produits éligibles (' + maxProcessable + ').', 'Attention', 'la-exclamation-circle text-warning', 'btn-warning');
            limitInput.val(maxProcessable).focus();
            return;
        }

        var button = $(this).prop('disabled', true);
        request({ url: urls.start, method: 'POST', data: { limit: limitVal } }).done(function (data) {
            $('#translation-run-preview-modal').modal('hide');
            renderRun(data.run);
        }).fail(function (xhr) {
            if (xhr.status === 409 && xhr.responseJSON && xhr.responseJSON.run) {
                $('#translation-run-preview-modal').modal('hide');
                renderRun(xhr.responseJSON.run);
                return;
            }
            showNoticeModal((xhr.responseJSON && xhr.responseJSON.message) || 'Impossible de démarrer la correction.', 'Erreur de démarrage', 'la-exclamation-triangle text-danger', 'btn-danger');
        }).always(function () { button.prop('disabled', false); });
    });

    $('#translation-run-retry').on('click', function () {
        if (!activeRun) return;
        request({ url: urls.retry + '/' + activeRun + '/retry-failed', method: 'POST' }).done(function (data) { renderRun(data.run); }).fail(function (xhr) {
            if (xhr.responseJSON && xhr.responseJSON.run) renderRun(xhr.responseJSON.run);
            showNoticeModal((xhr.responseJSON && xhr.responseJSON.message) || 'Impossible de relancer les échecs.', 'Erreur de relance', 'la-exclamation-triangle text-danger', 'btn-danger');
        });
    });

    $('#translation-run-stop').on('click', function () {
        if (!activeRun) return;
        showConfirmModal('Voulez-vous vraiment arrêter l’exécution de la traduction ?', 'Arrêter la traduction', function () {
            var button = $('#translation-run-stop').prop('disabled', true);
            request({ url: urls.stop + '/' + activeRun + '/stop', method: 'POST' }).done(function (data) {
                renderRun(data.run);
            }).fail(function (xhr) {
                if (xhr.responseJSON && xhr.responseJSON.run) renderRun(xhr.responseJSON.run);
                showNoticeModal((xhr.responseJSON && xhr.responseJSON.message) || 'Impossible d’arrêter la traduction.', 'Erreur', 'la-exclamation-triangle text-danger', 'btn-danger');
            }).always(function () { button.prop('disabled', false); });
        });
    });

    root.on('click', '.js-repair-product', function () {
        var button = $(this), original = button.html();
        var keepDisabled = false;
        button.prop('disabled', true).html('<i class="las la-spinner la-spin"></i>');
        request({ url: button.data('url'), method: 'POST' })
            .done(function (data) {
                if (data.success) {
                    window.location.reload();
                } else {
                    button.prop('disabled', false).html(original);
                    var msg = data.message || 'Le produit n’a pas été corrigé.';
                    if (window.AIZ && window.AIZ.plugins && window.AIZ.plugins.notify) {
                        window.AIZ.plugins.notify('warning', msg);
                    } else {
                        showNoticeModal(msg, 'Information', 'la-exclamation-circle text-warning', 'btn-warning');
                    }
                }
            })
            .fail(function (xhr) {
                button.prop('disabled', false).html(original);
                if (xhr.status === 429) {
                    keepDisabled = true;
                    holdRepairButton(button, original, retryAfterSeconds(xhr));
                }
                var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Impossible de traduire ce produit pour le moment.';
                if (window.AIZ && window.AIZ.plugins && window.AIZ.plugins.notify) {
                    window.AIZ.plugins.notify('danger', msg);
                } else {
                    showNoticeModal(msg, 'Erreur de traduction', 'la-exclamation-triangle text-danger', 'btn-danger');
                }
            })
            .always(function () {
                if (!keepDisabled) button.prop('disabled', false).html(original);
            });
    });

    if (activeRun) { showRun(); poll(); }
}(jQuery));
