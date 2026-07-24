(function () {
    'use strict';

    var boot = function () {
        var $ = window.jQuery || window.$;
        if (!$) return;

    var technicalNamePattern = /(^|_)(id|price|discount|stock|qty|quantity|sku|barcode|url|uri|slug|img|image|photo|thumbnail|pdf|color|width|height|length|weight|tax|token|method|provider|date|published|featured|deal|cash|refundable)(_|$)/i;
    var formMetadataNames = ['_token', '_method', 'lang', 'tab', 'id', 'added_by', 'type'];

    window.MayushFrenchCopy = {
        busy: false,
        frenchSnapshot: null,

        form: function () {
            return $('#aizSubmitForm, #choice_form').first();
        },

        language: function () {
            return this.form().find('input[name="lang"]').val() || new URLSearchParams(window.location.search).get('lang') || 'fr';
        },

        frenchLanguage: function () {
            return $('#product-language-bar .nav-link[data-lang="fr"]').attr('data-lang') || 'fr';
        },

        arabicLanguage: function () {
            return $('#copy-french-btn-wrapper').attr('data-arabic-language') || 'ar';
        },

        isFrench: function () {
            return this.language() === this.frenchLanguage();
        },

        isArabic: function () {
            return this.language() === this.arabicLanguage();
        },

        storageKey: function () {
            return 'mayush_product_form_fr:' + window.location.pathname.replace(/\/$/, '');
        },

        canonicalName: function (name) {
            return String(name || '').replace(/\[\]$/, '');
        },

        editorValue: function ($field) {
            if ($field.length && ($field.data('summernote') || $field.next('.note-editor').length) && typeof $field.summernote === 'function') {
                return $field.summernote('code');
            }
            return $field.val() || '';
        },

        tagValues: function ($field) {
            var input = $field[0];
            if (input && input._tagify && Array.isArray(input._tagify.value)) {
                return input._tagify.value.map(function (item) { return typeof item === 'string' ? item : item.value; }).filter(Boolean);
            }
            var raw = $field.val() || '';
            if (!raw) return [];
            try {
                var parsed = JSON.parse(raw);
                if (Array.isArray(parsed)) return parsed.map(function (item) { return item.value || item; }).filter(Boolean);
            } catch (e) {}
            return String(raw).split(',').map(function (item) { return item.trim(); }).filter(Boolean);
        },

        syncEditors: function () {
            this.form().find('textarea.aiz-text-editor').each(function () {
                var $field = $(this);
                if (($field.data('summernote') || $field.next('.note-editor').length) && typeof $field.summernote === 'function') {
                    $field.val($field.summernote('code'));
                }
            });
        },

        snapshot: function () {
            var self = this;
            var controls = [];
            this.syncEditors();
            this.form().find(':input').each(function () {
                var field = this;
                if (!field.name || formMetadataNames.indexOf(field.name) !== -1 || ['button', 'submit', 'reset', 'file'].indexOf(field.type) !== -1) return;

                var $field = $(field);
                var type = String(field.type || '').toLowerCase();
                var control = {
                    name: field.name,
                    key: self.canonicalName(field.name),
                    type: type,
                    value: type === 'checkbox' || type === 'radio' ? field.value : (field.tagName === 'TEXTAREA' ? self.editorValue($field) : $field.val()),
                    checked: type === 'checkbox' || type === 'radio' ? $field.prop('checked') : undefined,
                    values: field.tagName === 'SELECT' && field.multiple ? ($field.val() || []) : undefined,
                    tags: $field.hasClass('aiz-tag-input') ? self.tagValues($field) : undefined
                };
                controls.push(control);
            });
            return { controls: controls, capturedAt: new Date().toISOString() };
        },

        saveFrenchSnapshot: function () {
            if (!this.isFrench()) return;
            this.frenchSnapshot = this.snapshot();
            try { sessionStorage.setItem(this.storageKey(), JSON.stringify(this.frenchSnapshot)); } catch (e) {}
        },

        loadFrenchSnapshot: function () {
            if (this.frenchSnapshot) return this.frenchSnapshot;
            try {
                var stored = sessionStorage.getItem(this.storageKey());
                if (stored) this.frenchSnapshot = JSON.parse(stored);
            } catch (e) {}
            return this.frenchSnapshot || { controls: [] };
        },

        groups: function (snapshot) {
            var grouped = {};
            (snapshot.controls || []).forEach(function (control) {
                grouped[control.key] = grouped[control.key] || [];
                grouped[control.key].push(control);
            });
            return grouped;
        },

        targetControls: function (key) {
            var self = this;
            return this.form().find(':input').filter(function () {
                return self.canonicalName(this.name) === key;
            });
        },

        meaningful: function (control) {
            if (control.type === 'checkbox' || control.type === 'radio') return !!control.checked;
            if (Array.isArray(control.tags)) return control.tags.length > 0;
            if (Array.isArray(control.values)) return control.values.length > 0;
            return String(control.value || '').replace(/<p>(?:\s|&nbsp;|<br\s*\/?>)*<\/p>/gi, '').trim() !== '';
        },

        hasAnyArabicContent: function () {
            var snapshot = this.snapshot();
            return (snapshot.controls || []).some(this.meaningful);
        },

        hasArabicText: function (value) {
            return /[\u0600-\u06ff\u0750-\u077f\u08a0-\u08ff]/.test(String(value || ''));
        },

        hasFrenchText: function (value) {
            return /[A-Za-zÀ-ÿ]/.test(String(value || ''));
        },

        isDimensionChoiceOption: function (key) {
            var match = /^choice_options_(\d+)$/i.exec(String(key || ''));
            if (!match) return false;
            if (match[1] === '35') return true;

            var dimensionAttribute = this.form().find('input[name="choice_no[]"]').filter(function () {
                return String($(this).val()) === match[1];
            });
            if (!dimensionAttribute.length) return false;

            var choiceTitle = dimensionAttribute.closest('.form-group').find('input[name="choice[]"]').val() || '';
            return String(choiceTitle).trim().toLowerCase() === 'dimension';
        },

        isTranslatableControl: function (control) {
            var isChoiceOption = /^choice_options_/i.test(control.key);
            if (this.isDimensionChoiceOption(control.key)) return false;
            if (!isChoiceOption && ['text', 'textarea', 'search', 'email'].indexOf(control.type) === -1) return false;
            if (control.type === 'hidden') return false;
            if (technicalNamePattern.test(control.key) || /^(category|brand|shipping|type)$/i.test(control.key)) return false;
            var value = Array.isArray(control.value) ? control.value.join(' ') : String(control.value || '');
            if (!value.trim() || /^https?:\/\//i.test(value.trim()) || /^#[0-9a-f]{3,8}$/i.test(value.trim())) return false;
            return this.hasFrenchText(value) || this.hasArabicText(value) || /[\p{L}]/u.test(value);
        },

        translationFields: function (snapshot, onlyFrench) {
            var fields = {};
            var self = this;
            (snapshot.controls || []).forEach(function (control) {
                if (!self.isTranslatableControl(control)) return;
                var values = control.tags || (Array.isArray(control.values) ? control.values : [control.value]);
                values = values.filter(function (value) {
                    return String(value || '').trim() !== '' && (!onlyFrench || (!self.hasArabicText(value) && self.hasFrenchText(value)));
                });
                if (!values.length) return;
                if (control.tags || Array.isArray(control.values) || /\[\]$/.test(control.name)) {
                    fields[control.key] = (fields[control.key] || []).concat(values);
                } else {
                    fields[control.key] = values[0];
                }
            });
            return fields;
        },

        setEditorValue: function ($field, value) {
            $field.val(value);
            if (($field.data('summernote') || $field.next('.note-editor').length) && typeof $field.summernote === 'function') {
                $field.summernote('code', value);
                $field.next('.note-editor').find('.note-editable').attr('dir', 'rtl');
            }
            $field.trigger('input').trigger('change');
        },

        setTagValues: function ($field, values) {
            values = Array.isArray(values) ? values : [values];
            var input = $field[0];
            if (input && input._tagify) {
                input._tagify.removeAllTags();
                input._tagify.addTags(values);
            } else {
                $field.val(JSON.stringify(values)).trigger('input').trigger('change');
            }
        },

        addMissingOptions: function ($select, values) {
            values = Array.isArray(values) ? values : [values];
            values.forEach(function (value) {
                if ($select.find('option[value="' + String(value).replace(/"/g, '\\"') + '"]').length === 0) {
                    $('<option>').val(value).text(value).appendTo($select);
                }
            });
        },

        applyGroup: function (key, records, mode) {
            var self = this;
            var $targets = this.targetControls(key);
            if (!$targets.length) return;
            var meaningfulTarget = $targets.filter(function () { return self.meaningful({ type: this.type, value: $(this).val(), checked: $(this).prop('checked'), tags: $(this)._tagify ? self.tagValues($(this)) : undefined, values: this.multiple ? ($(this).val() || []) : undefined }); }).length > 0;
            if (mode === 'fill_empty' && meaningfulTarget) return;

            var first = records[0] || {};
            var values = records.map(function (record) { return record.value; });
            if (first.tags) {
                this.setTagValues($targets.first(), records.reduce(function (all, record) { return all.concat(record.tags || []); }, []));
                return;
            }
            if ($targets.first().is(':checkbox, :radio')) {
                if (mode === 'replace_all') $targets.prop('checked', false);
                records.forEach(function (record, index) {
                    if (record.checked && $targets.eq(index).length) $targets.eq(index).prop('checked', true).trigger('change');
                });
                return;
            }
            if ($targets.first().is('select')) {
                var selectValue = first.values || (first.value !== undefined ? first.value : values);
                $targets.each(function (index) {
                    var $select = $(this);
                    var value = Array.isArray(selectValue) && $select.prop('multiple') ? selectValue : (Array.isArray(selectValue) ? (selectValue[index] || '') : selectValue);
                    self.addMissingOptions($select, value);
                    $select.val(value).trigger('change');
                    if ($select.hasClass('aiz-selectpicker') && $.fn.selectpicker) $select.selectpicker('refresh');
                });
                return;
            }
            $targets.each(function (index) {
                var value = Array.isArray(values) && values.length > 1 ? values[index] : values[0];
                if (value === undefined) return;
                self.setEditorValue($(this), value);
            });
        },

        executeCopy: function (mode) {
            if (this.busy) return;
            var french = this.loadFrenchSnapshot();
            if (!french.controls.length) {
                this.notify('warning', 'Aucune donnée française disponible à copier.');
                return;
            }
            this.busy = true;
            var original = $('#btn-copy-french-content').html();
            $('#btn-copy-french-content').prop('disabled', true).html('<i class="las la-spinner la-spin mr-1"></i> Copie en cours...');
            try {
                var grouped = this.groups(french);
                Object.keys(grouped).forEach(function (key) { this.applyGroup(key, grouped[key], mode); }.bind(this));
                if (window.AIZ && AIZ.uploader && typeof AIZ.uploader.previewGenerate === 'function') AIZ.uploader.previewGenerate();
                this.notify('success', 'Toutes les données françaises ont été copiées dans la version arabe. Vérifiez et traduisez le contenu nécessaire avant d’enregistrer.');
            } catch (error) {
                this.notify('danger', 'Une erreur est survenue lors de la copie des données françaises.');
            }
            $('#btn-copy-french-content').prop('disabled', false).html(original);
            this.busy = false;
        },

        startTranslation: function (mode) {
            if (this.busy) return;
            var self = this;
            var fields = this.translationFields(this.snapshot(), mode === 'only_french');
            if (!Object.keys(fields).length) {
                this.notify('warning', 'Aucun contenu français à traduire.');
                return;
            }
            this.busy = true;
            var $buttons = $('#btn-copy-french-content, #btn-translate-arabic-content');
            var originalCopy = $('#btn-copy-french-content').html();
            var originalTranslate = $('#btn-translate-arabic-content').html();
            $buttons.prop('disabled', true);
            $('#btn-translate-arabic-content').html('<i class="las la-spinner la-spin mr-1"></i> Traduction en cours…');
            $.ajax({
                url: $('#btn-translate-arabic-content').data('translate-url'),
                method: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    source_language: 'fr',
                    target_language: 'ar',
                    product_id: this.form().find('input[name="id"]').val() || null,
                    fields: fields
                })
            }).done(function (response) {
                self.applyTranslatedFields(response.fields || {});
                self.notify(response.failed_fields && response.failed_fields.length ? 'warning' : 'success', response.message || 'Le contenu a été traduit en arabe avec Microsoft Azure Translator. Vérifiez la traduction avant d’enregistrer.');
            }).fail(function (xhr) {
                var response = xhr.responseJSON || {};
                self.notify('danger', response.message || 'La traduction n’a pas pu être effectuée. Les valeurs actuelles ont été conservées.');
            }).always(function () {
                $buttons.prop('disabled', false);
                $('#btn-copy-french-content').html(originalCopy);
                $('#btn-translate-arabic-content').html(originalTranslate);
                self.busy = false;
            });
        },

        applyTranslatedFields: function (fields) {
            var self = this;
            Object.keys(fields).forEach(function (key) {
                var value = fields[key];
                var $targets = self.targetControls(key);
                if (!$targets.length) return;
                if ($targets.first().hasClass('aiz-tag-input')) {
                    self.setTagValues($targets.first(), value);
                } else if ($targets.first().is('select')) {
                    $targets.each(function () {
                        var $select = $(this);
                        self.addMissingOptions($select, value);
                        $select.val(value).trigger('change');
                        if ($select.hasClass('aiz-selectpicker') && $.fn.selectpicker) $select.selectpicker('refresh');
                    });
                } else if (Array.isArray(value)) {
                    $targets.each(function (index) { if (value[index] !== undefined) self.setEditorValue($(this), value[index]); });
                } else {
                    self.setEditorValue($targets.first(), value);
                }
            });
            this.syncEditors();
        },

        notify: function (type, message) {
            if (window.AIZ && AIZ.plugins && typeof AIZ.plugins.notify === 'function') AIZ.plugins.notify(type, message);
            else window.alert(message);
        },

        applyRtl: function () {
            var rtl = this.isArabic();
            this.form().find('input[type="text"], input[type="search"], input[type="email"], textarea').each(function () {
                var $field = $(this);
                if (['number', 'url', 'hidden'].indexOf(this.type) !== -1 || technicalNamePattern.test(this.name || '')) return;
                $field.attr('dir', rtl ? 'rtl' : 'ltr');
                if ($field.next('.note-editor').length) $field.next('.note-editor').find('.note-editable').attr('dir', rtl ? 'rtl' : 'ltr');
            });
            this.form().find('.tagify').attr('dir', rtl ? 'rtl' : 'ltr');
        },

        bind: function () {
            var self = this;
            $(document).on('input change', '#aizSubmitForm :input, #choice_form :input', function () {
                if (self.isFrench()) self.saveFrenchSnapshot();
            });
            $(document).on('click', '#product-language-bar .nav-link', function () {
                if (self.isFrench()) self.saveFrenchSnapshot();
            });
            $(document).on('click', '#btn-copy-french-content', function (event) {
                event.preventDefault();
                if (self.hasAnyArabicContent()) $('#modal-copy-french-confirm').modal('show');
                else self.executeCopy('fill_empty');
            });
            $(document).on('click', '#btn-fill-empty-only', function () { $('#modal-copy-french-confirm').modal('hide'); self.executeCopy('fill_empty'); });
            $(document).on('click', '#btn-replace-all', function () { $('#modal-copy-french-confirm').modal('hide'); self.executeCopy('replace_all'); });
            $(document).on('click', '#btn-translate-arabic-content', function (event) {
                event.preventDefault();
                if (self.hasArabicText(JSON.stringify(self.translationFields(self.snapshot(), false)))) $('#modal-translate-arabic-confirm').modal('show');
                else self.startTranslation('only_french');
            });
            $(document).on('click', '#btn-translate-french-only', function () { $('#modal-translate-arabic-confirm').modal('hide'); self.startTranslation('only_french'); });
            $(document).on('click', '#btn-retranslate-all', function () { $('#modal-translate-arabic-confirm').modal('hide'); self.startTranslation('all'); });
        },

        init: function () {
            this.bind();
            if (this.isFrench()) this.saveFrenchSnapshot();
            this.applyRtl();
        }
    };

        $(function () { window.MayushFrenchCopy.init(); });
    };

    if (window.jQuery || window.$) boot();
    else if (document.readyState === 'complete') setTimeout(boot, 0);
    else window.addEventListener('load', boot);
})();
