(function ($) {
    "use strict";

    if (!$.fn.summernote) {
        return;
    }

    function selectionStyleButton(label, cssProperty, values) {
        return function (context) {
            var ui = $.summernote.ui;
            var items = values
                .map(function (value) {
                    return '<a class="dropdown-item" href="#" data-value="' + value + '">' + value + "</a>";
                })
                .join("");

            return ui
                .buttonGroup([
                    ui.button({
                        className: "dropdown-toggle",
                        contents: label + ' <span class="caret"></span>',
                        tooltip: label,
                        data: { toggle: "dropdown" },
                    }),
                    ui.dropdown({
                        items: items,
                        callback: function ($dropdown) {
                            $dropdown.find("a").on("click", function (event) {
                                event.preventDefault();
                                applySelectionStyle(context, cssProperty, $(this).data("value"));
                            });
                        },
                    }),
                ])
                .render();
        };
    }

    function applySelectionStyle(context, cssProperty, value) {
        var editable = context.layoutInfo.editable[0];
        var selection = window.getSelection();

        if (!selection || !selection.rangeCount) {
            return;
        }

        var range = selection.getRangeAt(0);

        if (range.collapsed || !editable.contains(range.commonAncestorContainer)) {
            return;
        }

        var span = document.createElement("span");
        span.style[cssProperty] = value;
        span.appendChild(range.extractContents());
        range.insertNode(span);
        context.invoke("editor.afterCommand");
    }

    function initEditors(context) {
        $(context)
            .find("textarea.aiz-banner-text-editor")
            .addBack("textarea.aiz-banner-text-editor")
            .each(function () {
                var $editor = $(this);

                if ($editor.next(".note-editor").length) {
                    return;
                }

                $editor.summernote({
                    toolbar: [
                        ["font", ["fontname", "fontsize", "bold", "italic", "underline", "strikethrough", "clear"]],
                        ["color", ["color"]],
                        ["para", ["paragraph"]],
                        ["spacing", ["bannerLineHeight", "bannerLetterSpacing"]],
                        ["view", ["undo", "redo"]],
                    ],
                    buttons: {
                        bannerLineHeight: selectionStyleButton("Line", "lineHeight", ["1", "1.2", "1.4", "1.6"]),
                        bannerLetterSpacing: selectionStyleButton("Space", "letterSpacing", ["normal", "0.02em", "0.05em", "0.1em"]),
                    },
                    disableDragAndDrop: true,
                    fontNames: ["Inter", "Public Sans", "Outfit", "Playfair Display", "Arial", "Helvetica", "system-ui", "serif"],
                    fontNamesIgnoreCheck: ["Inter", "Public Sans", "Outfit", "Playfair Display"],
                    height: $editor.data("min-height") || 105,
                    placeholder: $editor.attr("placeholder") || "",
                    callbacks: {
                        onChange: function (contents) {
                            $editor.val(contents);
                        },
                        onPaste: function () {
                            window.setTimeout(function () {
                                $editor.val($editor.summernote("code"));
                            }, 0);
                        },
                    },
                });
            });
    }

    function editorCode($field) {
        return $field.next(".note-editor").length ? $field.summernote("code") : $field.val();
    }

    function previewImage($item) {
        return $item.find(".file-preview img").first().attr("src");
    }

    function openPreview(button) {
        var $item = $(button).closest(".remove-parent");
        var $modal = $("#bannerTextPreviewModal");
        var $title = $item.find('textarea[name$="_titles[]"]').first();
        var $description = $item.find('textarea[name$="_descriptions[]"]').first();
        var cta = $item.find('input[name$="_cta_texts[]"]').first().val() || "";
        var image = previewImage($item);

        $modal.find(".js-banner-preview-title").html(editorCode($title) || "");
        $modal.find(".js-banner-preview-description").html(editorCode($description) || "");
        $modal.find(".js-banner-preview-cta").text(cta).toggle(!!cta);

        if (image) {
            $modal.find(".js-banner-preview-image").attr("src", image);
        }

        $modal.modal("show");
    }

    function historyUrl($modal, settingKey, lang) {
        var url = $modal.data("history-url").replace("__SETTING__", encodeURIComponent(settingKey));
        return url + "?lang=" + encodeURIComponent(lang || "");
    }

    function stripHtml(value) {
        return $("<div>").html(value || "").text().trim();
    }

    function renderHistory($modal, versions) {
        var $list = $modal.find(".js-banner-history-list").empty();
        var $status = $modal.find(".js-banner-history-status");

        if (!versions.length) {
            $status.text("No previous versions found.");
            return;
        }

        $status.empty();
        versions.forEach(function (version) {
            var preview = (version.value || [])
                .map(stripHtml)
                .filter(Boolean)
                .slice(0, 2)
                .join(" | ");
            var time = version.created_at ? new Date(version.created_at).toLocaleString() : "";
            var $item = $('<div class="list-group-item d-flex align-items-center justify-content-between"></div>');
            var $copy = $("<div></div>");

            $copy.append($("<div class=\"fw-600\"></div>").text(time));
            $copy.append($("<div class=\"small text-muted text-truncate\"></div>").text(preview || "Empty banner text"));
            $item.append($copy);
            $item.append(
                $('<button type="button" class="btn btn-sm btn-soft-primary js-banner-restore">Restore</button>')
                    .attr("data-version-id", version.id)
            );
            $list.append($item);
        });
    }

    function openHistory(button) {
        var $modal = $("#bannerTextHistoryModal");
        var settingKey = $(button).data("setting-key");
        var lang = $(button).data("lang");

        $modal.data("active-lang", lang);
        $modal.find(".js-banner-history-list").empty();
        $modal.find(".js-banner-history-status").text("Loading versions...");
        $modal.modal("show");

        $.getJSON(historyUrl($modal, settingKey, lang))
            .done(function (response) {
                renderHistory($modal, response.versions || []);
            })
            .fail(function () {
                $modal.find(".js-banner-history-status").text("Version history could not be loaded.");
            });
    }

    function restoreVersion(button) {
        var $modal = $("#bannerTextHistoryModal");
        var url = $modal.data("restore-url").replace("__VERSION__", $(button).data("version-id"));

        $(button).prop("disabled", true);
        $.post(url, { _token: AIZ.data.csrf })
            .done(function () {
                window.location.reload();
            })
            .fail(function () {
                $(button).prop("disabled", false);
                $modal.find(".js-banner-history-status").text("Version restore failed.");
            });
    }

    $(function () {
        initEditors(document);

        $(".banner-editor-preview").css({ height: "min(62vh, 560px)", minHeight: "320px" });
        $(".banner-editor-preview .js-banner-preview-image").css({ height: "100%", objectFit: "cover" });

        $(document).on("submit", "form", function () {
            $(this)
                .find("textarea.aiz-banner-text-editor")
                .each(function () {
                    var $editor = $(this);
                    $editor.val(editorCode($editor));
                });
        });
        $(document).on("click", ".js-banner-preview", function () {
            openPreview(this);
        });
        $(document).on("click", ".js-banner-history", function () {
            openHistory(this);
        });
        $(document).on("click", ".js-banner-restore", function () {
            restoreVersion(this);
        });

        new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (node.nodeType === 1) {
                        initEditors(node);
                    }
                });
            });
        }).observe(document.body, { childList: true, subtree: true });
    });
})(jQuery);
