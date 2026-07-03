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
        var quillInstances = {}; // Store Quill instances by block index

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
                var type = button.getAttribute("data-add-block");
                
                // Permission check for HTML block
                if (type === 'html' && window.mayushBlogConfig && window.mayushBlogConfig.canManageHtml !== true) {
                    alert('You do not have permission to add Advanced HTML blocks.');
                    return;
                }

                blocks.push(defaultBlock(type));
                render();
                sync("Block added");
                focusLastBlock();
            });
        });

        blocksRoot.addEventListener("input", function (e) {
            if (e.target && e.target.classList.contains('ql-editor')) return; // handled by quill
            readFromDom();
            sync("Autosaved");
        });

        blocksRoot.addEventListener("change", function () {
            readFromDom();
            sync("Autosaved");
        });

        blocksRoot.addEventListener("click", function (event) {
            var button = event.target.closest("[data-builder-action]");
            
            // Add FAQ item logic
            if (event.target.closest("[data-add-faq-item]")) {
                var blockEl = event.target.closest("[data-builder-block]");
                var index = Number(blockEl.getAttribute("data-index"));
                readFromDom();
                blocks[index].data.items = blocks[index].data.items || [];
                blocks[index].data.items.push({ question: "", answer: "" });
                render();
                sync("Autosaved");
                return;
            }

            // Remove FAQ item logic
            if (event.target.closest("[data-remove-faq-item]")) {
                var btn = event.target.closest("[data-remove-faq-item]");
                var blockEl = btn.closest("[data-builder-block]");
                var blockIndex = Number(blockEl.getAttribute("data-index"));
                var itemIndex = Number(btn.getAttribute("data-item-index"));
                readFromDom();
                blocks[blockIndex].data.items.splice(itemIndex, 1);
                render();
                sync("Autosaved");
                return;
            }

            if (!button) return;

            var blockEl = event.target.closest("[data-builder-block]");
            var index = Number(blockEl.getAttribute("data-index"));
            var action = button.getAttribute("data-builder-action");

            readFromDom();

            if (action === "remove") {
                blocks.splice(index, 1);
                delete quillInstances[index];
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
            var defaults = {
                heading: { type: "heading", data: { level: 2, text: "" } },
                image: { type: "image", data: { upload_id: "", alt: "", caption: "" } },
                quote: { type: "quote", data: { text: "", cite: "" } },
                list: { type: "list", data: { style: "unordered", items: [""] } },
                divider: { type: "divider", data: {} },
                rich_text: { type: "rich_text", data: { text: "" } },
                html: { type: "html", data: { code: "" } },
                gallery: { type: "gallery", data: { upload_ids: "" } },
                faq: { type: "faq", data: { items: [{ question: "", answer: "" }] } },
                product_recommendation: { type: "product_recommendation", data: { title: "Recommended Products", product_ids: "" } },
                shop_highlight: { type: "shop_highlight", data: { shop_id: "" } },
            };
            return defaults[type] || { type: "paragraph", data: { text: "" } };
        }

        function render() {
            // Destroy old quills
            quillInstances = {};

            blocksRoot.innerHTML = blocks.map(function (block, index) {
                return renderBlock(block, index);
            }).join("");

            empty.style.display = blocks.length ? "none" : "grid";

            if (window.AIZ && window.AIZ.uploader && window.AIZ.uploader.previewGenerate) {
                window.AIZ.uploader.previewGenerate();
            }

            // Initialize Quill instances
            setTimeout(function() {
                blocksRoot.querySelectorAll('.quill-editor').forEach(function(editorEl) {
                    var index = editorEl.getAttribute('data-index');
                    var quill = new Quill(editorEl, {
                        theme: 'snow',
                        modules: {
                            toolbar: [
                                [{ 'header': [2, 3, 4, false] }],
                                ['bold', 'italic', 'underline', 'strike'],
                                [{'list': 'ordered'}, {'list': 'bullet'}],
                                ['link', 'clean']
                            ]
                        }
                    });
                    
                    quill.on('text-change', function() {
                        readFromDom();
                        sync("Autosaved");
                    });

                    quillInstances[index] = quill;
                });
            }, 50);
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

            if (block.type === "rich_text") {
                return field("Rich Text", '<div class="quill-editor" data-index="' + index + '" style="height: 200px;">' + (data.text || "") + '</div>');
            }

            if (block.type === "html") {
                return field("Raw HTML", '<textarea data-field="code" class="form-control text-monospace" rows="6" placeholder="<div>Your HTML here</div>">' + escapeHtml(data.code || "") + '</textarea><small class="text-warning">Warning: HTML is rendered exactly as entered. Use with caution.</small>');
            }

            if (block.type === "gallery") {
                return field("Gallery Images", uploaderMultiple(index, data.upload_ids || ""));
            }

            if (block.type === "product_recommendation") {
                return field("Block Title", '<input type="text" data-field="title" value="' + escapeHtml(data.title || "") + '">') +
                       field("Product IDs (Comma separated)", '<input type="text" data-field="product_ids" placeholder="e.g. 12,45,88" value="' + escapeHtml(data.product_ids || "") + '">');
            }

            if (block.type === "shop_highlight") {
                return field("Shop ID", '<input type="text" data-field="shop_id" placeholder="Enter Shop ID" value="' + escapeHtml(data.shop_id || "") + '">');
            }

            if (block.type === "faq") {
                var itemsHtml = (data.items || []).map(function(item, i) {
                    return '<div class="faq-item p-2 border mb-2 bg-light"><div class="d-flex justify-content-between mb-2"><strong>Q' + (i+1) + '</strong><button type="button" class="btn btn-sm btn-icon btn-danger" data-remove-faq-item data-item-index="' + i + '"><i class="las la-times"></i></button></div><input type="text" data-faq-q class="form-control mb-2" placeholder="Question" value="' + escapeHtml(item.question || "") + '"><textarea data-faq-a class="form-control" rows="2" placeholder="Answer">' + escapeHtml(item.answer || "") + '</textarea></div>';
                }).join("");
                
                return field("FAQ Items", itemsHtml + '<button type="button" class="btn btn-sm btn-soft-primary mt-2" data-add-faq-item>+ Add Question</button>');
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

        function uploaderMultiple(index, value) {
            return [
                '<div class="input-group" data-toggle="aizuploader" data-type="image" data-multiple="true">',
                    '<div class="input-group-prepend">',
                        '<div class="input-group-text bg-soft-secondary font-weight-medium">Browse</div>',
                    '</div>',
                    '<div class="form-control file-amount">Choose Files</div>',
                    '<input type="hidden" class="selected-files" data-field="upload_ids" value="' + escapeHtml(value) + '">',
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

                if (block.type === 'faq') {
                    var items = [];
                    blockEl.querySelectorAll(".faq-item").forEach(function(itemEl) {
                        items.push({
                            question: itemEl.querySelector("[data-faq-q]").value,
                            answer: itemEl.querySelector("[data-faq-a]").value
                        });
                    });
                    data.items = items;
                } else if (block.type === 'rich_text') {
                    if (quillInstances[index]) {
                        data.text = quillInstances[index].root.innerHTML;
                    } else {
                        data.text = block.data.text;
                    }
                } else {
                    blockEl.querySelectorAll("[data-field]").forEach(function (fieldEl) {
                        var key = fieldEl.getAttribute("data-field");
                        data[key] = key === "items" ? fieldEl.value.split("\n") : fieldEl.value;
                    });
                }

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

                if (block.type === "rich_text" && data.text) {
                    return data.text; // it's HTML from Quill
                }

                if (block.type === "html" && data.code) {
                    return data.code; // raw HTML
                }

                if (block.type === "divider") {
                    return "<hr>";
                }

                // Complex blocks return empty string for raw HTML compilation 
                // because they need server side rendering with blade components later,
                // but we might want placeholders.
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
                divider: "Divider",
                rich_text: "Rich Text",
                html: "Advanced HTML",
                gallery: "Gallery",
                faq: "FAQ",
                product_recommendation: "Products",
                shop_highlight: "Shop"
            }[type] || "Block";
        }

        function blockIcon(type) {
            return {
                heading: "las la-heading",
                paragraph: "las la-align-left",
                image: "las la-image",
                quote: "las la-quote-left",
                list: "las la-list-ul",
                divider: "las la-minus",
                rich_text: "las la-file-signature",
                html: "las la-code",
                gallery: "las la-images",
                faq: "las la-question-circle",
                product_recommendation: "las la-box",
                shop_highlight: "las la-store"
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
