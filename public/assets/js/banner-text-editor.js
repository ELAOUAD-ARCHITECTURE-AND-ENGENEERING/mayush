(function ($) {
    "use strict";

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

        $modal.find(".js-banner-preview-title").text(stripHtml($title.val()));
        $modal.find(".js-banner-preview-description").text(stripHtml($description.val()));
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
        $(".banner-editor-preview").css({ height: "min(62vh, 560px)", minHeight: "320px" });
        $(".banner-editor-preview .js-banner-preview-image").css({ height: "100%", objectFit: "cover" });

        $(document).on("click", ".js-banner-preview", function () {
            openPreview(this);
        });
        $(document).on("click", ".js-banner-history", function () {
            openHistory(this);
        });
        $(document).on("click", ".js-banner-restore", function () {
            restoreVersion(this);
        });
    });
})(jQuery);
