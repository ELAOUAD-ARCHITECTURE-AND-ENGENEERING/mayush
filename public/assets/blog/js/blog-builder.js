(function () {
    "use strict";

    var builders = document.querySelectorAll("[data-blog-builder]");

    builders.forEach(function (builder) {
        var input = builder.querySelector("[data-blog-builder-input]");
        var description = builder.querySelector("[data-blog-builder-description]");
        var blocksRoot = builder.querySelector("[data-blog-builder-blocks]");
        var empty = builder.querySelector("[data-blog-builder-empty]");
        var status = builder.querySelector("[data-blog-builder-status]");
        var storageKey = "mayush.blog.builder." + window.location.pathname;
        var draggedIndex = null;
        var blocks = loadBlocks();

        if (!blocks.length && description && description.value.trim() !== "") {
            blocks = [{
                type: "paragraph",
                data: { text: stripHtml(description.value).trim() }
            }];
        }

        render();
        sync("Draft ready");

        builder.querySelectorAll("[data-add-block]").forEach(function (button) {
            button.addEventListener("click", function () {
                blocks.push(defaultBlock(button.getAttribute("data-add-block")));
                render();
                sync("Block added");
                focusLastBlock();
            });
        });

        blocksRoot.addEventListener("input", function () {
            readFromDom();
            sync("Autosaved");
        });

        blocksRoot.addEventListener("change", function () {
            readFromDom();
            sync("Autosaved");
        });

        blocksRoot.addEventListener("click", function (event) {
            var button = event.target.closest("[data-builder-action]");
            if (!button) {
                return;
            }

            var blockEl = event.target.closest("[data-builder-block]");
            var index = Number(blockEl.getAttribute("data-index"));
            var action = button.getAttribute("data-builder-action");

            readFromDom();

            if (action === "remove") {
                blocks.splice(index, 1);
            }

            if (action === "up" && index > 0) {
                swap(index, index - 1);
            }

            if (action === "down" && index < blocks.length - 1) {
                swap(index, index + 1);
            }

            render();
            sync("Autosaved");
        });

        blocksRoot.addEventListener("dragstart", function (event) {
            var blockEl = event.target.closest("[data-builder-block]");
            if (!blockEl) {
                return;
            }

            draggedIndex = Number(blockEl.getAttribute("data-index"));
            blockEl.classList.add("is-dragging");
            event.dataTransfer.effectAllowed = "move";
        });

        blocksRoot.addEventListener("dragover", function (event) {
            if (draggedIndex === null) {
                return;
            }

            event.preventDefault();
        });

        blocksRoot.addEventListener("drop", function (event) {
            var target = event.target.closest("[data-builder-block]");
            if (!target || draggedIndex === null) {
                return;
            }

            event.preventDefault();
            readFromDom();
            var targetIndex = Number(target.getAttribute("data-index"));
            var moved = blocks.splice(draggedIndex, 1)[0];
            blocks.splice(targetIndex, 0, moved);
            draggedIndex = null;
            render();
            sync("Reordered");
        });

        blocksRoot.addEventListener("dragend", function () {
            draggedIndex = null;
            blocksRoot.querySelectorAll(".is-dragging").forEach(function (blockEl) {
                blockEl.classList.remove("is-dragging");
            });
        });

        builder.closest("form").addEventListener("submit", function () {
            readFromDom();
            sync("Saving");
        });

        function loadBlocks() {
            var saved = [];

            try {
                saved = JSON.parse(input.value || "[]");
            } catch (error) {
                saved = [];
            }

            if (!saved.length) {
                try {
                    saved = JSON.parse(window.localStorage.getItem(storageKey) || "[]");
                } catch (error) {
                    saved = [];
                }
            }

            return Array.isArray(saved) ? saved : [];
        }

        function defaultBlock(type) {
            if (type === "heading") {
                return { type: "heading", data: { level: 2, text: "" } };
            }

            if (type === "image") {
                return { type: "image", data: { upload_id: "", alt: "", caption: "" } };
            }

            if (type === "quote") {
                return { type: "quote", data: { text: "", cite: "" } };
            }

            if (type === "list") {
                return { type: "list", data: { style: "unordered", items: [""] } };
            }

            if (type === "divider") {
                return { type: "divider", data: {} };
            }

            return { type: "paragraph", data: { text: "" } };
        }

        function render() {
            blocksRoot.innerHTML = blocks.map(function (block, index) {
                return renderBlock(block, index);
            }).join("");

            empty.style.display = blocks.length ? "none" : "grid";

            if (window.AIZ && window.AIZ.uploader && window.AIZ.uploader.previewGenerate) {
                window.AIZ.uploader.previewGenerate();
            }
        }

        function renderBlock(block, index) {
            var title = blockTitle(block.type);
            var icon = blockIcon(block.type);

            return [
                '<article class="blog-builder__block" data-builder-block data-index="' + index + '" draggable="true">',
                    '<div class="blog-builder__block-head">',
                        '<div class="blog-builder__block-title">',
                            '<i class="' + icon + '" aria-hidden="true"></i>',
                            '<span>' + title + '</span>',
                        '</div>',
                        '<div class="blog-builder__block-actions">',
                            iconButton("up", "Move block up", "las la-arrow-up"),
                            iconButton("down", "Move block down", "las la-arrow-down"),
                            iconButton("remove", "Remove block", "las la-trash", "is-danger"),
                        '</div>',
                    '</div>',
                    '<div class="blog-builder__block-body">',
                        renderBlockFields(block, index),
                    '</div>',
                '</article>'
            ].join("");
        }

        function renderBlockFields(block, index) {
            var data = block.data || {};

            if (block.type === "heading") {
                return field("Heading level", '<select data-field="level"><option value="2"' + selected(data.level, 2) + '>H2</option><option value="3"' + selected(data.level, 3) + '>H3</option><option value="4"' + selected(data.level, 4) + '>H4</option></select>') +
                    field("Heading text", '<input type="text" data-field="text" value="' + escapeHtml(data.text || "") + '">');
            }

            if (block.type === "image") {
                return field("Image", uploader(index, data.upload_id || "")) +
                    field("Alt text", '<input type="text" data-field="alt" value="' + escapeHtml(data.alt || "") + '">') +
                    field("Caption", '<input type="text" data-field="caption" value="' + escapeHtml(data.caption || "") + '">');
            }

            if (block.type === "quote") {
                return field("Quote", '<textarea data-field="text">' + escapeHtml(data.text || "") + '</textarea>') +
                    field("Citation", '<input type="text" data-field="cite" value="' + escapeHtml(data.cite || "") + '">');
            }

            if (block.type === "list") {
                return field("List style", '<select data-field="style"><option value="unordered"' + selected(data.style, "unordered") + '>Bulleted</option><option value="ordered"' + selected(data.style, "ordered") + '>Numbered</option></select>') +
                    field("Items", '<textarea data-field="items">' + escapeHtml((data.items || []).join("\n")) + '</textarea>');
            }

            if (block.type === "divider") {
                return '<div class="text-muted fs-13">Section divider</div>';
            }

            return field("Paragraph", '<textarea data-field="text">' + escapeHtml(data.text || "") + '</textarea>');
        }

        function uploader(index, value) {
            return [
                '<div class="input-group" data-toggle="aizuploader" data-type="image">',
                    '<div class="input-group-prepend">',
                        '<div class="input-group-text bg-soft-secondary font-weight-medium">Browse</div>',
                    '</div>',
                    '<div class="form-control file-amount">Choose File</div>',
                    '<input type="hidden" class="selected-files" data-field="upload_id" value="' + escapeHtml(value) + '">',
                '</div>',
                '<div class="file-preview box sm"></div>'
            ].join("");
        }

        function field(label, control) {
            return '<div class="blog-builder__field"><label>' + label + '</label>' + control + '</div>';
        }

        function iconButton(action, label, icon, extraClass) {
            return '<button type="button" class="blog-builder__icon-btn ' + (extraClass || "") + '" data-builder-action="' + action + '" aria-label="' + label + '" title="' + label + '"><i class="' + icon + '" aria-hidden="true"></i></button>';
        }

        function readFromDom() {
            blocks = Array.prototype.map.call(blocksRoot.querySelectorAll("[data-builder-block]"), function (blockEl) {
                var index = Number(blockEl.getAttribute("data-index"));
                var block = blocks[index] || defaultBlock("paragraph");
                var data = {};

                blockEl.querySelectorAll("[data-field]").forEach(function (fieldEl) {
                    var key = fieldEl.getAttribute("data-field");
                    data[key] = key === "items" ? fieldEl.value.split("\n") : fieldEl.value;
                });

                return { type: block.type, data: data };
            });
        }

        function sync(message) {
            input.value = JSON.stringify(blocks);
            description.value = compileHtml(blocks);
            window.localStorage.setItem(storageKey, input.value);

            if (status && message) {
                status.textContent = message;
            }
        }

        function compileHtml(sourceBlocks) {
            return sourceBlocks.map(function (block) {
                var data = block.data || {};

                if (block.type === "heading" && data.text) {
                    var level = ["2", "3", "4"].indexOf(String(data.level)) >= 0 ? data.level : "2";
                    return "<h" + level + ">" + escapeHtml(data.text) + "</h" + level + ">";
                }

                if (block.type === "paragraph" && data.text) {
                    return "<p>" + escapeHtml(data.text).replace(/\n/g, "<br>") + "</p>";
                }

                if (block.type === "quote" && data.text) {
                    return "<blockquote><p>" + escapeHtml(data.text) + "</p></blockquote>";
                }

                if (block.type === "list" && data.items && data.items.length) {
                    var tag = data.style === "ordered" ? "ol" : "ul";
                    var items = data.items.filter(Boolean).map(function (item) {
                        return "<li>" + escapeHtml(item) + "</li>";
                    }).join("");
                    return items ? "<" + tag + ">" + items + "</" + tag + ">" : "";
                }

                if (block.type === "divider") {
                    return "<hr>";
                }

                return "";
            }).filter(Boolean).join("\n");
        }

        function swap(from, to) {
            var tmp = blocks[from];
            blocks[from] = blocks[to];
            blocks[to] = tmp;
        }

        function focusLastBlock() {
            var last = blocksRoot.querySelector("[data-builder-block]:last-child input, [data-builder-block]:last-child textarea, [data-builder-block]:last-child select");
            if (last) {
                last.focus();
            }
        }

        function blockTitle(type) {
            return {
                heading: "Heading",
                paragraph: "Paragraph",
                image: "Image",
                quote: "Quote",
                list: "List",
                divider: "Divider"
            }[type] || "Block";
        }

        function blockIcon(type) {
            return {
                heading: "las la-heading",
                paragraph: "las la-align-left",
                image: "las la-image",
                quote: "las la-quote-left",
                list: "las la-list-ul",
                divider: "las la-minus"
            }[type] || "las la-square";
        }

        function selected(value, expected) {
            return String(value) === String(expected) ? " selected" : "";
        }

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function stripHtml(value) {
            var tmp = document.createElement("div");
            tmp.innerHTML = value;
            return tmp.textContent || tmp.innerText || "";
        }
    });
})();
