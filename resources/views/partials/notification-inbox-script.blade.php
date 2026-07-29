@once
    <script>
        (function () {
            function bindNotificationInboxes() {
                document.querySelectorAll('[data-notification-inbox]').forEach(function (inbox) {
                    var selectAll = inbox.querySelector('[data-notification-select-all]');
                    var selectedCount = inbox.querySelector('[data-notification-selection-count]');
                    var bulkAction = inbox.querySelector('[data-notification-bulk-action]');
                    var checkboxes = Array.prototype.slice.call(inbox.querySelectorAll('.check-one'));
                    var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                    function selected() {
                        return checkboxes.filter(function (checkbox) { return checkbox.checked; });
                    }

                    function updateSelection() {
                        var count = selected().length;
                        if (selectedCount) {
                            selectedCount.textContent = count + ' ' + (document.documentElement.lang === 'fr' ? 'sélectionnée(s)' : 'selected');
                        }
                        if (bulkAction) bulkAction.disabled = count === 0;
                        if (selectAll) selectAll.checked = checkboxes.length > 0 && count === checkboxes.length;
                    }

                    if (selectAll) {
                        selectAll.addEventListener('change', function () {
                            checkboxes.forEach(function (checkbox) { checkbox.checked = selectAll.checked; });
                            updateSelection();
                        });
                    }

                    checkboxes.forEach(function (checkbox) {
                        checkbox.addEventListener('change', updateSelection);
                    });

                    if (!bulkAction) return;

                    bulkAction.addEventListener('click', async function () {
                        var ids = selected().map(function (checkbox) { return checkbox.value; });
                        if (!ids.length || !inbox.dataset.bulkDeleteUrl || inbox.dataset.bulkDeleteUrl === '#') return;

                        bulkAction.disabled = true;
                        try {
                            var body = new URLSearchParams();
                            body.set('_token', csrf);
                            ids.forEach(function (id) { body.append('notification_ids[]', id); });

                            var response = await fetch(inbox.dataset.bulkDeleteUrl, {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: {
                                    'Accept': 'application/json, text/plain, */*',
                                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                                    'X-CSRF-TOKEN': csrf
                                },
                                body: body.toString()
                            });
                            var result = (await response.text()).trim();
                            if (!response.ok || result !== '1') throw new Error('Unable to archive notifications.');

                            if (window.AIZ?.plugins?.notify) {
                                window.AIZ.plugins.notify('success', @json(translate('Notifications archived.')));
                            }
                            window.setTimeout(function () { window.location.reload(); }, 250);
                        } catch (error) {
                            bulkAction.disabled = false;
                            if (window.AIZ?.plugins?.notify) {
                                window.AIZ.plugins.notify('danger', @json(translate('Unable to archive notifications.')));
                            }
                        }
                    });

                    updateSelection();
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bindNotificationInboxes, { once: true });
            } else {
                bindNotificationInboxes();
            }
        })();
    </script>
@endonce
