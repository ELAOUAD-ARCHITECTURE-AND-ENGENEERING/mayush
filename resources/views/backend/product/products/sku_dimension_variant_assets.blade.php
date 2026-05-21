<style>
    .sku-dimension-variant-cell,
    .sku-dimension-custom-variant {
        align-items: flex-start;
        display: flex;
        gap: .5rem;
        justify-content: space-between;
        min-width: 12rem;
    }

    .sku-dimension-variant-actions {
        display: inline-flex;
        flex: 0 0 auto;
        gap: .35rem;
        margin-left: auto;
    }

    .sku-dimension-custom-row .sku-dimension-variant-select {
        min-width: 9rem;
    }

    @media (max-width: 767.98px) {
        .sku-dimension-variant-cell,
        .sku-dimension-custom-variant {
            min-width: 0;
        }
    }
</style>

<div class="alert alert-info mt-2 mb-0 sku-dimension-save-hint">
    {{ translate('Save the product to persist dimension variant changes.') }}
</div>

<template data-sku-dimension-row-template>
    <tr class="variant sku-dimension-custom-row">
        <td>
            <div class="sku-dimension-custom-variant">
                <select class="form-control sku-dimension-variant-select" required onchange="skuDimensionUpdateRow(this)">
                    <option value="">{{ translate('Choose variant') }}</option>
                </select>
                <div class="sku-dimension-variant-actions">
                    <button type="button" class="btn btn-icon btn-soft-danger btn-sm" onclick="skuDimensionRemoveRow(this)" aria-label="{{ translate('Remove dimension variant') }}" title="{{ translate('Remove dimension variant') }}">
                        <i class="las la-trash"></i>
                    </button>
                    <button type="button" class="btn btn-icon btn-primary btn-sm" onclick="skuDimensionAddRow(this)" aria-label="{{ translate('Add dimension variant') }}" title="{{ translate('Add dimension variant') }}">
                        <i class="las la-plus"></i>
                    </button>
                </div>
            </div>
            <input type="hidden" data-dimension-choice-input>
            <div class="invalid-feedback d-block sku-dimension-variant-error"></div>
        </td>
        <td>
            <input type="number" lang="en" value="{{ $unit_price }}" min="0" step="0.01" class="form-control" required data-dimension-stock-field="price" oninput="skuDimensionUpdateRow(this)">
        </td>
        <td>
            <input type="text" value="" class="form-control" data-dimension-stock-field="sku">
        </td>
        <td>
            <input type="number" lang="en" value="10" min="0" step="1" class="form-control" required data-dimension-stock-field="qty">
        </td>
        <td>
            <div class="row gutters-5">
                <div class="col">
                    <input type="number" lang="en" value="0" min="0" step="0.01" class="form-control" placeholder="{{ translate('L') }}" data-dimension-stock-field="length" oninput="skuDimensionUpdateRow(this)">
                </div>
                <div class="col">
                    <input type="number" lang="en" value="0" min="0" step="0.01" class="form-control" placeholder="{{ translate('W') }}" data-dimension-stock-field="width" oninput="skuDimensionUpdateRow(this)">
                </div>
                <div class="col">
                    <input type="number" lang="en" value="0" min="0" step="0.01" class="form-control" placeholder="{{ translate('H') }}" data-dimension-stock-field="height" oninput="skuDimensionUpdateRow(this)">
                </div>
            </div>
        </td>
        <td>
            <select class="form-control aiz-selectpicker" data-dimension-stock-field="unit">
                <option value="cm">cm</option>
                <option value="inch">inch</option>
            </select>
        </td>
        <td>
            <div class="input-group" data-toggle="aizuploader" data-type="image">
                <div class="input-group-prepend">
                    <div class="input-group-text bg-soft-secondary font-weight-medium">{{ translate('Browse') }}</div>
                </div>
                <div class="form-control file-amount text-truncate">{{ translate('Choose File') }}</div>
                <input type="hidden" class="selected-files" data-dimension-stock-field="img">
            </div>
            <div class="file-preview box sm"></div>
        </td>
    </tr>
</template>

<script>
    (function ($) {
        var tableSelector = '.sku-dimension-table[data-dimension-choice-input-name]';
        var invalidFormatMessage = @json(translate('Use a dimension such as 10x20x30 cm, 1-100cm, or +1000cm.'));
        var duplicateMessage = @json(translate('This dimension variant already exists.'));
        var invalidRowsMessage = @json(translate('Fix the dimension variant rows before saving.'));

        function normalizeVariant(value) {
            return String(value || '').trim().replace(/\s+/g, '').toLowerCase();
        }

        function stockFieldSuffix(value) {
            return String(value || '').trim().replace(/\s+/g, '').replace(/\./g, '_');
        }

        function validVariant(value) {
            var number = '\\d+(?:\\.\\d+)?';
            var unit = '(?:cm|mm|m|in|inch|inches)';

            return new RegExp('^' + number + '\\s*x\\s*' + number + '\\s*x\\s*' + number + '\\s*' + unit + '$', 'i').test(value)
                || new RegExp('^' + number + '\\s*-\\s*' + number + '\\s*' + unit + '$', 'i').test(value)
                || new RegExp('^\\+?' + number + '\\s*' + unit + '$', 'i').test(value);
        }

        function getRowAttributes(row) {
            var variantElement = row.find('.sku-dimension-variant-label');
            var value;
            var isCustom = false;

            if (variantElement.length > 0) {
                value = variantElement.text();
            } else {
                value = row.find('.sku-dimension-variant-select').val() || '';
                isCustom = true;
            }

            var key = normalizeVariant(value);
            var suffix = stockFieldSuffix(value);

            var price, length, width, height;

            if (isCustom) {
                price = row.find('[data-dimension-stock-field="price"]').val() || '';
                length = row.find('[data-dimension-stock-field="length"]').val() || '';
                width = row.find('[data-dimension-stock-field="width"]').val() || '';
                height = row.find('[data-dimension-stock-field="height"]').val() || '';
            } else {
                price = row.find('input[name^="price_' + suffix + '"]').val() || '';
                length = row.find('input[name^="length_' + suffix + '"]').val() || '';
                width = row.find('input[name^="width_' + suffix + '"]').val() || '';
                height = row.find('input[name^="height_' + suffix + '"]').val() || '';
            }

            return {
                key: key,
                price: parseFloat(price) || 0,
                length: parseFloat(length) || 0,
                width: parseFloat(width) || 0,
                height: parseFloat(height) || 0
            };
        }

        function hasDuplicate(table, row, value) {
            var key = normalizeVariant(value);
            var rowAttrs = getRowAttributes(row);
            var duplicate = false;

            table.find('tbody tr.variant').not(row).each(function () {
                var otherRow = $(this);
                var otherAttrs = getRowAttributes(otherRow);

                if (otherAttrs.key === key &&
                    otherAttrs.price === rowAttrs.price &&
                    otherAttrs.length === rowAttrs.length &&
                    otherAttrs.width === rowAttrs.width &&
                    otherAttrs.height === rowAttrs.height) {
                    duplicate = true;
                    return false; // break loop
                }
            });

            return duplicate;
        }

        function clearRowNames(row) {
            row.find('[data-dimension-choice-input]').removeAttr('name').val('');
            row.find('[data-dimension-stock-field]').removeAttr('name');
        }

        function setRowError(row, message) {
            row.find('.sku-dimension-variant-select').toggleClass('is-invalid', message !== '');
            row.find('.sku-dimension-variant-error').text(message);
        }

        function updateRow(row) {
            var table = row.closest(tableSelector);
            var value = row.find('.sku-dimension-variant-select').val() || '';
            var suffix = stockFieldSuffix(value);

            clearRowNames(row);
            setRowError(row, '');

            if (value === '') {
                return false;
            }

            if (hasDuplicate(table, row, value)) {
                setRowError(row, duplicateMessage);
                return false;
            }

            row.find('[data-dimension-choice-input]')
                .attr('name', table.data('dimension-choice-input-name'))
                .val(value);
            row.find('[data-dimension-stock-field]').each(function () {
                $(this).attr('name', $(this).data('dimension-stock-field') + '_' + suffix + '[]');
            });

            return true;
        }

        function populateVariantSelect(selectElement) {
            var table = selectElement.closest(tableSelector);
            var choiceInputName = table.attr('data-dimension-choice-input-name');
            var selectPicker = $('select[name="' + choiceInputName + '"]');

            if (selectPicker.length === 0) {
                return;
            }

            var currentValue = selectElement.val();

            // Clear all options except the placeholder
            selectElement.find('option').not('[value=""]').remove();

            // Append all currently selected options from the main select picker
            selectPicker.find('option:selected').each(function () {
                var optionValue = $(this).val();
                var optionText = $(this).text();
                selectElement.append($('<option>', {
                    value: optionValue,
                    text: optionText
                }));
            });

            // Restore the previously selected value
            if (currentValue) {
                selectElement.val(currentValue);
            }
        }

        $(document).on('focus', '.sku-dimension-variant-select', function() {
            populateVariantSelect($(this));
        });

        function addRow(button) {
            var template = $('[data-sku-dimension-row-template]').first();
            if (template.length === 0) {
                return;
            }

            var newRow;
            if (template[0].content) {
                // Native HTML5 template support
                newRow = $(template[0].content).clone().children().first();
                if (!newRow || newRow.length === 0) {
                    newRow = $(document.importNode(template[0].content, true)).children().first();
                }
            } else {
                // Fallback for older environments
                newRow = $(template.html());
            }

            if (!newRow || newRow.length === 0) {
                return;
            }

            var selectElement = newRow.find('.sku-dimension-variant-select');
            button.closest('tr').after(newRow);
            populateVariantSelect(selectElement);

            if (window.AIZ && window.AIZ.plugins && typeof window.AIZ.plugins.bootstrapSelect === 'function') {
                window.AIZ.plugins.bootstrapSelect('refresh');
            }
        }

        window.skuDimensionAddRow = function (btnElement) {
            addRow($(btnElement));
        };

        window.skuDimensionRemoveRow = function (btnElement) {
            var button = $(btnElement);
            var row = button.closest('tr');
            var variant = button.data('variant');
            var form = row.closest('form');

            if (variant) {
                $('<input>', {
                    type: 'hidden',
                    name: 'removed_sku_variants[]',
                    value: variant
                }).appendTo(form);
            }

            row.remove();
        };

        window.skuDimensionUpdateRow = function (inputElement) {
            var input = $(inputElement);
            var row = input.closest('.sku-dimension-custom-row');
            var table = row.closest(tableSelector);
            updateRow(row);

            // Re-validate other custom rows to automatically clear duplicate states if they were fixed
            table.find('.sku-dimension-custom-row').not(row).each(function () {
                updateRow($(this));
            });
        };

        $(document)
            .off('submit.skuDimensionVariant', 'form')
            .on('submit.skuDimensionVariant', 'form', function (event) {
                var form = $(this);
                var invalidRows = false;

                form.find(tableSelector + ' .sku-dimension-custom-row').each(function () {
                    if (!updateRow($(this))) {
                        invalidRows = true;
                    }
                });

                if (invalidRows) {
                    event.preventDefault();
                    AIZ.plugins.notify('warning', invalidRowsMessage);
                }
            });
    })(jQuery);
</script>
