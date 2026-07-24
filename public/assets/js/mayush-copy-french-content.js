(function () {
    'use strict';

    function initMayushFrenchCopy() {
        var $ = window.jQuery || window.$;
        if (!$) return;

        window.MayushFrenchCopy = {
        frenchData: {},

        init: function () {
            this.bindEvents();
            this.captureFrenchState();
            this.updateButtonVisibility();
        },

        getActiveLang: function () {
            var urlParams = new URLSearchParams(window.location.search);
            return urlParams.get('lang') || $('#product-language-bar .nav-link.active').data('lang') || $('input[name="lang"]').val() || 'fr';
        },

        updateButtonVisibility: function () {
            var currentLang = this.getActiveLang();
            if (currentLang === 'ar') {
                $('#copy-french-btn-wrapper').show();
            } else {
                $('#copy-french-btn-wrapper').hide();
            }
        },

        bindEvents: function () {
            var self = this;

            // Track changes on French tab form inputs
            $(document).on('change input', '#aizSubmitForm :input', function () {
                if (self.getActiveLang() === 'fr') {
                    self.captureFrenchState();
                }
            });

            // Track language tab navigation
            $(document).on('click', '#product-language-bar .nav-link', function () {
                var lang = $(this).data('lang');
                if (self.getActiveLang() === 'fr') {
                    self.captureFrenchState();
                }
            });

            // Copy button click
            $(document).on('click', '#btn-copy-french-content', function (e) {
                e.preventDefault();
                self.handleCopyClick();
            });

            // Modal option buttons
            $(document).on('click', '#btn-fill-empty-only', function () {
                $('#modal-copy-french-confirm').modal('hide');
                self.executeCopy('fill_empty');
            });

            $(document).on('click', '#btn-replace-all', function () {
                $('#modal-copy-french-confirm').modal('hide');
                self.executeCopy('replace_all');
            });
        },

        captureFrenchState: function () {
            var data = {};
            var form = $('#aizSubmitForm');
            if (!form.length) return;

            // Translatable and general inputs
            data.name = form.find('input[name="name"]').val() || '';
            data.unit = form.find('input[name="unit"]').val() || '';
            
            // Description (rich text or standard textarea)
            var descEditor = form.find('textarea[name="description"]');
            if (descEditor.length) {
                if (descEditor.data('summernote') || descEditor.next('.note-editor').length) {
                    data.description = descEditor.summernote('code');
                } else {
                    data.description = descEditor.val() || '';
                }
            } else {
                data.description = '';
            }

            data.short_description = form.find('textarea[name="short_description"]').val() || form.find('input[name="short_description"]').val() || '';
            data.brand_id = form.find('select[name="brand_id"]').val() || '';
            data.weight = form.find('input[name="weight"]').val() || '';
            data.min_qty = form.find('input[name="min_qty"]').val() || '';
            data.barcode = form.find('input[name="barcode"]').val() || '';
            
            // Tags
            var tagsInput = form.find('input[name="tags[]"], input[name="tags"]');
            if (tagsInput.length) {
                data.tags = tagsInput.val() || '';
            }

            // Categories
            data.category_id = form.find('input[name="category_id"]:checked').val() || form.find('select[name="category_id"]').val() || '';
            var selectedCats = [];
            form.find('input[name="category_ids[]"]:checked').each(function () {
                selectedCats.push($(this).val());
            });
            data.category_ids = selectedCats;

            // Media
            data.thumbnail_img = form.find('input[name="thumbnail_img"]').val() || '';
            data.photos = form.find('input[name="photos"]').val() || '';
            data.video_provider = form.find('select[name="video_provider"]').val() || '';
            data.video_link = form.find('input[name="video_link"]').val() || '';
            data.pdf = form.find('input[name="pdf"]').val() || '';

            // Pricing & Stock
            data.unit_price = form.find('input[name="unit_price"]').val() || '';
            data.discount = form.find('input[name="discount"]').val() || '';
            data.discount_type = form.find('select[name="discount_type"]').val() || '';
            data.date_range = form.find('input[name="date_range"]').val() || '';
            data.current_stock = form.find('input[name="current_stock"]').val() || '';
            data.sku = form.find('input[name="sku"]').val() || '';

            // Colors & Attributes
            data.colors_active = form.find('input[name="colors_active"]').is(':checked') ? '1' : '0';
            data.colors = form.find('select[name="colors[]"]').val() || [];
            data.choice_attributes = form.find('select[name="choice_attributes[]"]').val() || [];

            // SEO
            data.meta_title = form.find('input[name="meta_title"]').val() || '';
            data.meta_description = form.find('textarea[name="meta_description"]').val() || '';
            data.meta_img = form.find('input[name="meta_img"]').val() || '';
            data.slug = form.find('input[name="slug"]').val() || '';

            // Shipping & Warranty
            data.cash_on_delivery = form.find('input[name="cash_on_delivery"]').is(':checked') ? '1' : '0';
            data.shipping_type = form.find('input[name="shipping_type"]:checked').val() || form.find('select[name="shipping_type"]').val() || 'free';
            data.flat_shipping_cost = form.find('input[name="flat_shipping_cost"]').val() || '';
            data.is_quantity_multiplied = form.find('input[name="is_quantity_multiplied"]').is(':checked') ? '1' : '0';
            data.refundable = form.find('input[name="refundable"]').is(':checked') ? '1' : '0';
            data.has_warranty = form.find('input[name="has_warranty"]').is(':checked') ? '1' : '0';
            data.warranty_id = form.find('select[name="warranty_id"]').val() || '';

            // Status
            data.featured = form.find('input[name="featured"]').is(':checked') ? '1' : '0';
            data.todays_deal = form.find('input[name="todays_deal"]').is(':checked') ? '1' : '0';

            // Frequently bought
            data.frequently_bought_selection_type = form.find('select[name="frequently_bought_selection_type"]').val() || '';
            data.fq_bought_product_ids = form.find('select[name="fq_bought_product_ids[]"]').val() || [];
            data.fq_bought_product_category_id = form.find('select[name="fq_bought_product_category_id"]').val() || '';

            // Variants table data
            var variantData = [];
            form.find('.sku_combination_table tr, table.variant-table tbody tr').each(function () {
                var row = $(this);
                var variant = {
                    price: row.find('input[name^="price_"]').val() || '',
                    sku: row.find('input[name^="sku_"]').val() || '',
                    qty: row.find('input[name^="qty_"]').val() || '',
                    img: row.find('input[name^="img_"]').val() || ''
                };
                variantData.push(variant);
            });
            data.variants = variantData;

            this.frenchData = data;
            try {
                sessionStorage.setItem('mayush_french_product_data', JSON.stringify(data));
            } catch (e) {}
        },

        getStoredFrenchData: function () {
            if (this.frenchData && Object.keys(this.frenchData).length > 0 && (this.frenchData.name || this.frenchData.description)) {
                return this.frenchData;
            }
            try {
                var stored = sessionStorage.getItem('mayush_french_product_data');
                if (stored) {
                    return JSON.parse(stored);
                }
            } catch (e) {}
            return this.frenchData || {};
        },

        hasFrenchContent: function () {
            var data = this.getStoredFrenchData();
            if (!data) return false;
            for (var key in data) {
                if (data.hasOwnProperty(key)) {
                    var val = data[key];
                    if (Array.isArray(val) && val.length > 0) return true;
                    if (typeof val === 'string' && val.trim() !== '') return true;
                }
            }
            return false;
        },

        hasExistingArabicContent: function () {
            var form = $('#aizSubmitForm');
            if (!form.length) return false;

            var name = form.find('input[name="name"]').val();
            var unit = form.find('input[name="unit"]').val();
            var desc = '';
            var descEditor = form.find('textarea[name="description"]');
            if (descEditor.length) {
                desc = descEditor.val() || '';
            }

            if ((name && name.trim() !== '') || (unit && unit.trim() !== '') || (desc && desc.trim() !== '' && desc !== '<p><br></p>')) {
                return true;
            }
            return false;
        },

        handleCopyClick: function () {
            if (!this.hasFrenchContent()) {
                this.notify('warning', typeof AIZ !== 'undefined' && AIZ.local ? (AIZ.local.no_french_data || 'Aucune donnée française disponible à copier.') : 'Aucune donnée française disponible à copier.');
                return;
            }

            if (this.hasExistingArabicContent()) {
                $('#modal-copy-french-confirm').modal('show');
            } else {
                this.executeCopy('fill_empty');
            }
        },

        executeCopy: function (mode) {
            var btn = $('#btn-copy-french-content');
            var originalHtml = btn.html();
            btn.prop('disabled', true).html('<i class="las la-spinner la-spin mr-1"></i> ' + (typeof AIZ !== 'undefined' && AIZ.local ? (AIZ.local.copying || 'Copie en cours...') : 'Copie en cours...'));

            var self = this;
            setTimeout(function () {
                try {
                    var french = self.getStoredFrenchData();
                    var form = $('#aizSubmitForm');

                    // Helper to set field value based on mode
                    var setValue = function (selector, val, isSelect, isCheckbox, isRadio, isRichText) {
                        var el = form.find(selector);
                        if (!el.length) return;

                        if (mode === 'fill_empty') {
                            var currentVal = el.val();
                            if (isRichText && (currentVal && currentVal.trim() !== '' && currentVal !== '<p><br></p>')) return;
                            if (!isRichText && currentVal && currentVal.trim() !== '') return;
                        }

                        if (isRichText) {
                            el.val(val);
                            if (el.data('summernote') || el.next('.note-editor').length) {
                                el.summernote('code', val);
                            }
                        } else if (isSelect) {
                            el.val(val);
                            if ($.fn.selectpicker) {
                                el.selectpicker('refresh');
                            }
                        } else if (isCheckbox) {
                            el.prop('checked', val === '1' || val === true).trigger('change');
                        } else if (isRadio) {
                            form.find(selector + '[value="' + val + '"]').prop('checked', true).trigger('change');
                        } else {
                            el.val(val).trigger('change');
                        }
                    };

                    // Copy translatable text fields
                    setValue('input[name="name"]', french.name || '');
                    setValue('input[name="unit"]', french.unit || '');
                    setValue('textarea[name="description"]', french.description || '', false, false, false, true);

                    // Copy brand and specs
                    if (french.brand_id) setValue('select[name="brand_id"]', french.brand_id, true);
                    if (french.weight) setValue('input[name="weight"]', french.weight);
                    if (french.min_qty) setValue('input[name="min_qty"]', french.min_qty);
                    if (french.barcode) setValue('input[name="barcode"]', french.barcode);

                    // Copy Tags
                    if (french.tags) {
                        var tagsEl = form.find('input[name="tags[]"], input[name="tags"]');
                        if (tagsEl.length) {
                            if (mode === 'replace_all' || !tagsEl.val()) {
                                tagsEl.val(french.tags);
                                if (tagsEl.data('tagsinput')) {
                                    tagsEl.tagsinput('removeAll');
                                    tagsEl.tagsinput('add', french.tags);
                                }
                            }
                        }
                    }

                    // Copy Categories
                    if (french.category_id) {
                        var catRadio = form.find('input[name="category_id"][value="' + french.category_id + '"]');
                        if (catRadio.length && (mode === 'replace_all' || !form.find('input[name="category_id"]:checked').length)) {
                            catRadio.prop('checked', true).trigger('change');
                        }
                    }
                    if (french.category_ids && french.category_ids.length > 0) {
                        if (mode === 'replace_all') {
                            form.find('input[name="category_ids[]"]').prop('checked', false);
                        }
                        french.category_ids.forEach(function (catId) {
                            form.find('input[name="category_ids[]"][value="' + catId + '"]').prop('checked', true).trigger('change');
                        });
                    }

                    // Copy Media
                    if (french.thumbnail_img) {
                        var thumbInput = form.find('input[name="thumbnail_img"]');
                        if (mode === 'replace_all' || !thumbInput.val()) {
                            thumbInput.val(french.thumbnail_img).trigger('change');
                        }
                    }
                    if (french.photos) {
                        var photosInput = form.find('input[name="photos"]');
                        if (mode === 'replace_all' || !photosInput.val()) {
                            photosInput.val(french.photos).trigger('change');
                        }
                    }
                    if (typeof AIZ !== 'undefined' && AIZ.uploader && typeof AIZ.uploader.previewGenerate === 'function') {
                        AIZ.uploader.previewGenerate();
                    }

                    if (french.video_provider) setValue('select[name="video_provider"]', french.video_provider, true);
                    if (french.video_link) setValue('input[name="video_link"]', french.video_link);

                    // Copy Pricing & Stock
                    if (french.unit_price) setValue('input[name="unit_price"]', french.unit_price);
                    if (french.discount) setValue('input[name="discount"]', french.discount);
                    if (french.discount_type) setValue('select[name="discount_type"]', french.discount_type, true);
                    if (french.date_range) setValue('input[name="date_range"]', french.date_range);
                    if (french.current_stock) setValue('input[name="current_stock"]', french.current_stock);
                    if (french.sku) setValue('input[name="sku"]', french.sku);

                    // Colors & Attributes
                    if (french.colors_active) setValue('input[name="colors_active"]', french.colors_active, false, true);
                    if (french.colors && french.colors.length > 0) setValue('select[name="colors[]"]', french.colors, true);
                    if (french.choice_attributes && french.choice_attributes.length > 0) setValue('select[name="choice_attributes[]"]', french.choice_attributes, true);

                    // SEO
                    if (french.meta_title) setValue('input[name="meta_title"]', french.meta_title);
                    if (french.meta_description) setValue('textarea[name="meta_description"]', french.meta_description);
                    if (french.meta_img) setValue('input[name="meta_img"]', french.meta_img);
                    if (french.slug) setValue('input[name="slug"]', french.slug);

                    // Shipping & Warranty & Status
                    if (french.cash_on_delivery) setValue('input[name="cash_on_delivery"]', french.cash_on_delivery, false, true);
                    if (french.refundable) setValue('input[name="refundable"]', french.refundable, false, true);
                    if (french.featured) setValue('input[name="featured"]', french.featured, false, true);
                    if (french.todays_deal) setValue('input[name="todays_deal"]', french.todays_deal, false, true);
                    if (french.has_warranty) setValue('input[name="has_warranty"]', french.has_warranty, false, true);
                    if (french.warranty_id) setValue('select[name="warranty_id"]', french.warranty_id, true);

                    btn.prop('disabled', false).html(originalHtml);
                    self.notify('success', 'Toutes les données françaises ont été copiées dans la version arabe. Vérifiez et traduisez le contenu nécessaire avant d’enregistrer.');
                } catch (err) {
                    btn.prop('disabled', false).html(originalHtml);
                    self.notify('danger', 'Une erreur est survenue lors de la copie des données françaises.');
                }
            }, 300);
        },

        notify: function (type, message) {
            if (typeof AIZ !== 'undefined' && AIZ.plugins && typeof AIZ.plugins.notify === 'function') {
                AIZ.plugins.notify(type, message);
            } else {
                alert(message);
            }
        }
    };

        $(document).ready(function () {
            window.MayushFrenchCopy.init();
        });
    }

    if (typeof window.jQuery !== 'undefined') {
        initMayushFrenchCopy();
    } else {
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof window.jQuery !== 'undefined') {
                initMayushFrenchCopy();
            } else {
                var checkJQuery = setInterval(function () {
                    if (typeof window.jQuery !== 'undefined') {
                        clearInterval(checkJQuery);
                        initMayushFrenchCopy();
                    }
                }, 50);
            }
        });
    }
})();
