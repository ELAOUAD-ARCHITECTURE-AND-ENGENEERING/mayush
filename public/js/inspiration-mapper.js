'use strict';

class InspirationMapper {
    constructor(config) {
        this.config = config;
        this.items = config.existingItems.map((item) => ({ ...item, product: { ...item.product } }));
        this.mode = 'place';
        this.isPreview = false;
        this.pendingClick = null;
        this.reassignIndex = null;
        this.selectedIndex = -1;
        this.undoStack = [];
        this.redoStack = [];
        this.searchDebounce = null;
        this.searchRequestId = 0;
        this.searchSelectedIdx = 0;
        this.pendingRequests = 0;
        this.moveSaves = new Map();
        this.failedActions = new Map();
        this.failureSequence = 0;
        this.retryingFailures = false;
        this.lastFocusedElement = null;

        this.container = document.getElementById(config.containerId);
        this.image = document.getElementById(config.imageId);
        this.stage = document.getElementById('mapperStage');
        this.modal = document.getElementById('searchModal');
        this.searchInput = document.getElementById('searchInput');
        this.searchResults = document.getElementById('searchResults');
        this.itemsList = document.getElementById('itemsList');
        this.itemCount = document.getElementById('itemCount');
        this.warning = document.getElementById('unavailableWarning');
        this.saveIndicator = document.getElementById('saveIndicator');
        this.saveRetry = document.getElementById('saveRetry');
        this.liveRegion = document.getElementById('mapperLiveRegion');

        this.bindEvents();
        this.renderMarkers();
        this.renderItemList();
    }

    bindEvents() {
        this.container.addEventListener('click', (event) => this.handleImageClick(event));
        this.container.addEventListener('mousedown', (event) => {
            const marker = event.target.closest('.hotspot-marker');
            if (!marker || this.isPreview) return;
            const index = Number(marker.dataset.index);
            event.stopPropagation();
            if (this.mode === 'drag') this.handleMarkerDrag(event, index);
            else this.selectMarker(index);
        });
        this.container.addEventListener('touchstart', (event) => {
            const marker = event.target.closest('.hotspot-marker');
            if (!marker || this.mode !== 'drag' || this.isPreview) return;
            event.preventDefault();
            this.handleMarkerDrag(event.touches[0], Number(marker.dataset.index), true);
        }, { passive: false });

        this.searchInput.addEventListener('input', () => {
            clearTimeout(this.searchDebounce);
            this.searchDebounce = setTimeout(() => this.doSearch(), 300);
        });
        this.searchInput.addEventListener('keydown', (event) => this.handleSearchKeydown(event));
        this.modal.addEventListener('click', (event) => {
            if (event.target === this.modal) this.closeProductSearch();
        });
        this.modal.addEventListener('keydown', (event) => this.trapModalFocus(event));
        this.saveRetry.addEventListener('click', () => this.retryFailedActions());

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                this.closeProductSearch();
                this.closeContextMenu();
            }
            const key = event.key.toLowerCase();
            if ((event.ctrlKey || event.metaKey) && key === 'z') {
                event.preventDefault();
                event.shiftKey ? this.redo() : this.undo();
            }
            if (!this.modal.classList.contains('open') && this.selectedIndex >= 0
                && ['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(event.key)) {
                event.preventDefault();
                this.nudgeMarker(event.key);
            }
        });

        document.querySelectorAll('#previewControls [data-width]').forEach((button) => {
            button.addEventListener('click', () => this.setPreviewWidth(Number(button.dataset.width)));
        });
        if (window.ResizeObserver) {
            this.resizeObserver = new ResizeObserver(() => this.renderMarkers());
            this.resizeObserver.observe(this.container);
        }
        window.addEventListener('beforeunload', (event) => {
            if (this.hasPendingSaves()) {
                event.preventDefault();
                event.returnValue = 'Modifications non enregistrees';
            }
        });
    }

    handleImageClick(event) {
        if (this.mode !== 'place' || this.isPreview || event.target.closest('.hotspot-marker')) return;
        const rect = this.image.getBoundingClientRect();
        if (!rect.width || !rect.height) return;
        this.pendingClick = {
            x: this.clamp((event.clientX - rect.left) / rect.width),
            y: this.clamp((event.clientY - rect.top) / rect.height),
        };
        this.reassignIndex = null;
        this.openProductSearch();
    }

    renderMarkers(newIndex = -1) {
        this.closeContextMenu();
        this.container.querySelectorAll('.hotspot-marker, .drag-ghost').forEach((element) => element.remove());
        this.items.forEach((item, index) => {
            if (item.x == null || item.y == null) return;
            const marker = document.createElement('div');
            marker.className = `hotspot-marker${index === this.selectedIndex ? ' active' : ''}${index === newIndex ? ' placing' : ''}`;
            marker.dataset.index = String(index);
            marker.style.left = `${this.clamp(item.x) * 100}%`;
            marker.style.top = `${this.clamp(item.y) * 100}%`;
            marker.style.background = item.product?.available ? '#1f2a3a' : '#8a8f98';
            marker.tabIndex = 0;
            marker.setAttribute('role', 'button');
            marker.setAttribute('aria-label', `Point ${index + 1}: ${item.product?.name || ''}`);
            marker.appendChild(document.createTextNode(String(index + 1)));

            const tooltip = document.createElement('span');
            tooltip.className = 'marker-tooltip';
            const image = document.createElement('img');
            image.src = item.product?.image || '';
            image.alt = '';
            const label = document.createElement('span');
            label.textContent = item.product?.name || '';
            tooltip.append(image, label);
            marker.appendChild(tooltip);

            marker.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    this.openMarkerMenu(index, marker);
                }
            });
            this.container.appendChild(marker);
        });
    }

    renderItemList() {
        this.itemsList.replaceChildren();
        this.itemCount.textContent = String(this.items.length);
        const unavailableCount = this.items.filter((item) => !item.product?.available).length;
        this.warning.hidden = unavailableCount === 0;
        this.warning.textContent = unavailableCount
            ? `Warning: ${unavailableCount} ${this.config.translations.unavailable}`
            : '';

        this.items.forEach((item, index) => {
            const row = document.createElement('div');
            row.className = `item-list-row${item.product?.available ? '' : ' unavailable'}`;
            const number = document.createElement('span');
            number.className = 'number';
            number.textContent = String(index + 1);
            const image = document.createElement('img');
            image.src = item.product?.image || '';
            image.alt = '';
            const name = document.createElement('span');
            name.className = 'name';
            name.textContent = item.product?.name || 'Unknown';
            const price = document.createElement('span');
            price.className = 'price';
            price.textContent = item.product?.price || '';
            row.append(number, image, name, price);

            if (!item.product?.available) {
                const badge = document.createElement('span');
                badge.className = 'stock-badge out-of-stock';
                badge.textContent = 'Indisponible';
                row.appendChild(badge);
            }
            const replace = this.actionButton('las la-exchange-alt', this.config.translations.replace, () => this.startReassign(index));
            replace.classList.add('edit-only');
            const remove = this.actionButton('las la-trash', this.config.translations.delete, () => this.deleteItem(index));
            remove.classList.add('edit-only', 'btn-soft-danger');
            row.append(replace, remove);
            row.addEventListener('click', (event) => {
                if (!event.target.closest('button')) this.selectMarker(index);
            });
            this.itemsList.appendChild(row);
        });
    }

    actionButton(icon, title, handler) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-sm btn-soft-secondary btn-icon btn-circle';
        button.title = title;
        button.setAttribute('aria-label', title);
        button.innerHTML = `<i class="${icon}" aria-hidden="true"></i>`;
        button.addEventListener('click', (event) => { event.stopPropagation(); handler(); });
        return button;
    }

    selectMarker(index) {
        this.selectedIndex = this.selectedIndex === index ? -1 : index;
        this.renderMarkers();
        this.container.querySelector(`[data-index="${index}"]`)?.focus();
    }

    openMarkerMenu(index, marker) {
        this.closeContextMenu();
        this.selectedIndex = index;
        const menu = document.createElement('div');
        menu.className = 'marker-context-menu';
        menu.style.left = marker.style.left;
        menu.style.top = marker.style.top;
        menu.setAttribute('role', 'menu');
        const replace = document.createElement('button');
        replace.textContent = this.config.translations.replace;
        replace.addEventListener('click', () => this.startReassign(index));
        const remove = document.createElement('button');
        remove.textContent = this.config.translations.delete;
        remove.addEventListener('click', () => this.deleteItem(index));
        menu.append(replace, remove);
        this.container.appendChild(menu);
        replace.focus();
    }

    closeContextMenu() {
        this.container?.querySelector('.marker-context-menu')?.remove();
    }

    handleMarkerDrag(event, index, isTouch = false) {
        const item = this.items[index];
        if (!item) return;
        const origin = { x: item.x, y: item.y };
        const start = { x: event.clientX, y: event.clientY };
        let moved = false;
        let frame = null;
        let point = start;
        const source = this.container.querySelector(`[data-index="${index}"]`);
        const ghost = source?.cloneNode(true);
        if (ghost) {
            ghost.classList.add('drag-ghost');
            ghost.removeAttribute('tabindex');
            this.container.appendChild(ghost);
            source.style.visibility = 'hidden';
        }

        const onMove = (moveEvent) => {
            if (isTouch) moveEvent.preventDefault();
            point = isTouch ? moveEvent.touches[0] : moveEvent;
            if (!moved && Math.hypot(point.clientX - start.x, point.clientY - start.y) < 3) return;
            moved = true;
            if (frame) return;
            frame = requestAnimationFrame(() => {
                updatePosition();
                if (ghost) {
                    ghost.style.left = `${item.x * 100}%`;
                    ghost.style.top = `${item.y * 100}%`;
                }
                frame = null;
            });
        };
        const updatePosition = () => {
            const rect = this.image.getBoundingClientRect();
            if (!rect.width || !rect.height) return;
            item.x = this.clamp((point.clientX - rect.left) / rect.width);
            item.y = this.clamp((point.clientY - rect.top) / rect.height);
        };
        const onEnd = () => {
            document.removeEventListener(isTouch ? 'touchmove' : 'mousemove', onMove);
            document.removeEventListener(isTouch ? 'touchend' : 'mouseup', onEnd);
            if (frame) {
                cancelAnimationFrame(frame);
                frame = null;
                updatePosition();
            }
            ghost?.remove();
            if (source) source.style.visibility = '';
            if (!moved) return;
            this.renderMarkers();
            const finalMarker = this.container.querySelector(`[data-index="${index}"]`);
            finalMarker?.classList.add('snapping');
            setTimeout(() => finalMarker?.classList.remove('snapping'), 160);
            this.pushUndo({ type: 'move', item, oldX: origin.x, oldY: origin.y, newX: item.x, newY: item.y });
            this.saveHotspot(item, origin);
        };
        document.addEventListener(isTouch ? 'touchmove' : 'mousemove', onMove, { passive: false });
        document.addEventListener(isTouch ? 'touchend' : 'mouseup', onEnd);
    }

    nudgeMarker(key) {
        const item = this.items[this.selectedIndex];
        if (!item) return;
        const oldX = item.x;
        const oldY = item.y;
        if (key === 'ArrowLeft') item.x = this.clamp(item.x - .005);
        if (key === 'ArrowRight') item.x = this.clamp(item.x + .005);
        if (key === 'ArrowUp') item.y = this.clamp(item.y - .005);
        if (key === 'ArrowDown') item.y = this.clamp(item.y + .005);
        this.renderMarkers();
        this.pushUndo({ type: 'move', item, oldX, oldY, newX: item.x, newY: item.y });
        this.saveHotspot(item, { x: oldX, y: oldY });
    }

    openProductSearch() {
        this.lastFocusedElement = document.activeElement;
        this.modal.classList.add('open');
        this.searchInput.value = '';
        this.searchSelectedIdx = 0;
        this.renderRecents();
        setTimeout(() => this.searchInput.focus(), 0);
    }

    closeProductSearch() {
        if (!this.modal.classList.contains('open')) return;
        this.searchRequestId += 1;
        this.modal.classList.remove('open');
        this.pendingClick = null;
        this.reassignIndex = null;
        this.lastFocusedElement?.focus?.();
    }

    startReassign(index) {
        this.closeContextMenu();
        this.reassignIndex = index;
        this.pendingClick = null;
        this.openProductSearch();
    }

    renderRecents() {
        const recents = this.getRecents();
        this.searchResults.replaceChildren();
        if (recents.length) this.renderSearchProducts(recents, this.config.translations.recents);
    }

    async doSearch() {
        const query = this.searchInput.value.trim();
        if (query.length < 2) { this.renderRecents(); return; }
        const requestId = ++this.searchRequestId;
        this.searchResults.textContent = this.config.translations.loading;
        try {
            const response = await fetch(`${this.config.searchUrl}?q=${encodeURIComponent(query)}`, {
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': this.config.csrfToken },
            });
            if (!response.ok) throw new Error(`Search failed (${response.status})`);
            const products = (await response.json()).data || [];
            if (requestId !== this.searchRequestId) return;
            this.searchResults.replaceChildren();
            if (!products.length) {
                this.searchResults.textContent = this.config.translations.noResults;
                return;
            }
            this.searchSelectedIdx = 0;
            this.renderSearchProducts(products);
        } catch (error) {
            if (requestId !== this.searchRequestId) return;
            this.searchResults.textContent = this.config.translations.saveFailed;
        }
    }

    renderSearchProducts(products, heading = null) {
        if (heading) {
            const title = document.createElement('div');
            title.className = 'search-section-title';
            title.textContent = heading;
            this.searchResults.appendChild(title);
        }
        products.forEach((product) => {
            const row = document.createElement('div');
            row.className = 'search-result-item';
            row.tabIndex = -1;
            const image = document.createElement('img');
            image.src = product.image || '';
            image.alt = '';
            const info = document.createElement('div');
            const name = document.createElement('div');
            name.className = 'name';
            name.textContent = product.name;
            const price = document.createElement('div');
            price.className = 'price';
            price.textContent = product.price || '';
            info.append(name, price);
            const badge = document.createElement('span');
            badge.className = `stock-badge ${product.stock === 'in_stock' || product.stock_status === 'in_stock' ? 'in-stock' : 'out-of-stock'}`;
            badge.textContent = product.available ? 'En stock' : 'Indisponible';
            row.append(image, info, badge);
            row.addEventListener('click', () => this.selectProduct({ ...product }));
            this.searchResults.appendChild(row);
        });
        this.updateSearchSelection();
    }

    handleSearchKeydown(event) {
        const results = [...this.searchResults.querySelectorAll('.search-result-item')];
        if (event.key === 'Escape') { event.preventDefault(); this.closeProductSearch(); return; }
        if (!results.length) return;
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            this.searchSelectedIdx = Math.min(this.searchSelectedIdx + 1, results.length - 1);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            this.searchSelectedIdx = Math.max(this.searchSelectedIdx - 1, 0);
        } else if (event.key === 'Enter') {
            event.preventDefault();
            results[this.searchSelectedIdx]?.click();
        }
        this.updateSearchSelection();
    }

    updateSearchSelection() {
        const results = [...this.searchResults.querySelectorAll('.search-result-item')];
        results.forEach((element, index) => element.classList.toggle('selected', index === this.searchSelectedIdx));
        results[this.searchSelectedIdx]?.scrollIntoView({ block: 'nearest' });
    }

    trapModalFocus(event) {
        if (event.key !== 'Tab') return;
        const focusable = [...this.modal.querySelectorAll('input, button, [tabindex]:not([tabindex="-1"])')];
        if (!focusable.length) return;
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    }

    async selectProduct(product) {
        const reassignIndex = this.reassignIndex;
        const point = this.pendingClick;
        this.closeProductSearch();
        this.rememberRecent(product);
        if (reassignIndex != null) {
            await this.reassignProduct(reassignIndex, product);
        } else if (point) {
            await this.placeProduct(product, point);
        }
    }

    async placeProduct(product, point, recordHistory = true, failureKey = null) {
        const operationKey = failureKey || `place:${product.id}:${point.x}:${point.y}`;
        const retry = () => this.placeProduct(product, point, recordHistory, operationKey);
        const optimisticItem = {
            id: null,
            hotspot_id: null,
            display_order: point.display_order ?? this.items.length,
            x: point.x,
            y: point.y,
            product: { ...product, stock_status: product.stock || product.stock_status },
        };
        this.items.push(optimisticItem);
        this.renderMarkers(this.items.length - 1);
        this.renderItemList();
        try {
            const json = await this.request(this.config.storeUrl, {
                method: 'POST', body: JSON.stringify({
                    product_id: product.id,
                    x: point.x,
                    y: point.y,
                    ...(point.display_order == null ? {} : { display_order: point.display_order }),
                }),
            });
            Object.assign(optimisticItem, json.item, { product: { ...json.item.product } });
            if (recordHistory) this.pushUndo({ type: 'place', item: optimisticItem });
            this.renderMarkers(this.items.indexOf(optimisticItem));
            this.renderItemList();
            this.announce(`Point ${this.items.length} place`);
            return optimisticItem;
        } catch (error) {
            const index = this.items.indexOf(optimisticItem);
            if (index >= 0) this.items.splice(index, 1);
            this.renderMarkers();
            this.renderItemList();
            this.failSave(retry, error, operationKey);
            return null;
        }
    }

    async reassignProduct(index, product, recordHistory = true, failureKey = null) {
        const item = this.items[index];
        if (!item) return;
        const oldProduct = { ...item.product };
        const operationKey = failureKey || `reassign:${item.hotspot_id}`;
        const retry = () => this.reassignProduct(index, product, recordHistory, operationKey);
        item.product = { ...product, stock_status: product.stock || product.stock_status };
        this.renderMarkers();
        this.renderItemList();
        try {
            await this.request(this.updateUrl(item.hotspot_id), {
                method: 'PUT', body: JSON.stringify({ product_id: product.id }),
            });
            if (recordHistory) this.pushUndo({ type: 'reassign', item, oldProduct, newProduct: { ...item.product } });
        } catch (error) {
            item.product = oldProduct;
            this.renderMarkers();
            this.renderItemList();
            this.failSave(retry, error, operationKey);
        }
    }

    async deleteItem(index, recordHistory = true, failureKey = null) {
        const item = this.items[index];
        if (!item || (!window.confirm(this.config.translations.deleteConfirm) && recordHistory)) return;
        const operationKey = failureKey || `delete:${item.hotspot_id}`;
        const retry = () => this.deleteItem(this.items.indexOf(item), recordHistory, operationKey);
        this.items.splice(index, 1);
        this.selectedIndex = -1;
        this.renderMarkers();
        this.renderItemList();
        try {
            await this.request(this.destroyUrl(item.hotspot_id), { method: 'DELETE' });
            if (recordHistory) this.pushUndo({ type: 'delete', item, index });
            this.announce(`Point ${index + 1} supprime`);
        } catch (error) {
            this.items.splice(Math.min(index, this.items.length), 0, item);
            this.renderMarkers();
            this.renderItemList();
            this.failSave(retry, error, operationKey);
        }
    }

    saveHotspot(item, rollback) {
        const key = String(item.hotspot_id);
        let entry = this.moveSaves.get(key);
        if (!entry) {
            entry = { item, rollback, timer: null, version: 0, saving: false };
            this.moveSaves.set(key, entry);
        }
        entry.item = item;
        entry.version += 1;
        if (entry.timer) clearTimeout(entry.timer);
        if (!this.failedActions.size) this.setSaveState('saving');

        const schedule = () => {
            entry.timer = setTimeout(async () => {
                entry.timer = null;
                entry.saving = true;
                const version = entry.version;
                const target = { x: entry.item.x, y: entry.item.y };
                const retry = () => {
                    entry.item.x = target.x;
                    entry.item.y = target.y;
                    this.saveHotspot(entry.item, entry.rollback);
                    this.renderMarkers();
                };
                try {
                    await this.request(this.updateUrl(entry.item.hotspot_id), {
                        method: 'PUT', body: JSON.stringify(target),
                    });
                    entry.saving = false;
                    if (entry.version === version) {
                        this.moveSaves.delete(key);
                        if (!this.hasPendingSaves()) this.setSaveState('saved');
                    } else {
                        schedule();
                    }
                } catch (error) {
                    entry.saving = false;
                    if (entry.version !== version) {
                        schedule();
                        return;
                    }
                    this.moveSaves.delete(key);
                    entry.item.x = entry.rollback.x;
                    entry.item.y = entry.rollback.y;
                    this.renderMarkers();
                    this.failSave(retry, error, `move:${key}`);
                }
            }, 500);
        };

        if (!entry.saving) schedule();
    }

    pushUndo(action) {
        this.undoStack.push(action);
        if (this.undoStack.length > 50) this.undoStack.shift();
        this.redoStack = [];
    }

    async undo() {
        const action = this.undoStack.pop();
        if (!action) return;
        const failureKey = this.historyOperationKey(action, true);
        if (await this.applyHistory(action, true, failureKey)) {
            this.redoStack.push(action);
            this.showToast(`${this.config.translations.undone}: ${action.type}`);
        } else {
            this.undoStack.push(action);
            this.setHistoryRetry(action, true, failureKey);
        }
    }

    async redo() {
        const action = this.redoStack.pop();
        if (!action) return;
        const failureKey = this.historyOperationKey(action, false);
        if (await this.applyHistory(action, false, failureKey)) {
            this.undoStack.push(action);
            if (this.undoStack.length > 50) this.undoStack.shift();
            this.showToast(`${this.config.translations.redone}: ${action.type}`);
        } else {
            this.redoStack.push(action);
            this.setHistoryRetry(action, false, failureKey);
        }
    }

    historyOperationKey(action, inverse) {
        return `history:${inverse ? 'undo' : 'redo'}:${action.type}:${action.item?.hotspot_id || action.item?.id}`;
    }

    setHistoryRetry(action, inverse, failureKey) {
        const failure = this.failedActions.get(failureKey);
        if (!failure) return;
        failure.retry = () => this.retryHistoryAction(action, inverse, failureKey);
        this.failedActions.set(failureKey, failure);
    }

    async retryHistoryAction(action, inverse, failureKey) {
        const source = inverse ? this.undoStack : this.redoStack;
        const target = inverse ? this.redoStack : this.undoStack;
        const index = source.lastIndexOf(action);
        if (index < 0) return;
        source.splice(index, 1);

        if (await this.applyHistory(action, inverse, failureKey)) {
            target.push(action);
            if (!inverse && target.length > 50) target.shift();
            this.showToast(`${inverse ? this.config.translations.undone : this.config.translations.redone}: ${action.type}`);
        } else {
            source.push(action);
            this.setHistoryRetry(action, inverse, failureKey);
        }
    }

    async applyHistory(action, inverse, failureKey = null) {
        const operationKey = failureKey || this.historyOperationKey(action, inverse);
        const previousPosition = action.type === 'move'
            ? { x: action.item.x, y: action.item.y }
            : null;
        try {
            if (action.type === 'move') {
                action.item.x = inverse ? action.oldX : action.newX;
                action.item.y = inverse ? action.oldY : action.newY;
                await this.request(this.updateUrl(action.item.hotspot_id), {
                    method: 'PUT', body: JSON.stringify({ x: action.item.x, y: action.item.y }),
                });
            } else if (action.type === 'place') {
                if (inverse) {
                    await this.request(this.destroyUrl(action.item.hotspot_id), { method: 'DELETE' });
                    this.items.splice(this.items.indexOf(action.item), 1);
                } else {
                    const item = await this.placeProduct(action.item.product, action.item, false, operationKey);
                    if (!item) return false;
                    action.item = item;
                }
            } else if (action.type === 'delete') {
                if (inverse) {
                    const item = await this.placeProduct(action.item.product, action.item, false, operationKey);
                    if (!item) return false;
                    action.item = item;
                    this.items.splice(this.items.indexOf(item), 1);
                    this.items.splice(Math.min(action.index, this.items.length), 0, item);
                } else {
                    await this.request(this.destroyUrl(action.item.hotspot_id), { method: 'DELETE' });
                    this.items.splice(this.items.indexOf(action.item), 1);
                }
            } else if (action.type === 'reassign') {
                const product = inverse ? action.oldProduct : action.newProduct;
                await this.request(this.updateUrl(action.item.hotspot_id), {
                    method: 'PUT', body: JSON.stringify({ product_id: product.id }),
                });
                action.item.product = { ...product };
            }
            this.renderMarkers();
            this.renderItemList();
            return true;
        } catch (error) {
            if (previousPosition) {
                action.item.x = previousPosition.x;
                action.item.y = previousPosition.y;
                this.renderMarkers();
            }
            this.failSave(() => this.applyHistory(action, inverse, operationKey), error, operationKey);
            return false;
        }
    }

    switchMode(mode) {
        if (!['place', 'drag'].includes(mode) || this.isPreview) return;
        this.mode = mode;
        this.container.classList.remove('mode-place', 'mode-drag');
        this.container.classList.add(`mode-${mode}`);
        document.querySelectorAll('.mode-btn').forEach((button) => {
            button.classList.toggle('active', button.dataset.mode === mode);
        });
    }

    togglePreview() {
        this.isPreview = !this.isPreview;
        document.body.classList.toggle('preview-mode', this.isPreview);
        this.stage.classList.toggle('preview-frame', this.isPreview);
        this.stage.style.maxWidth = this.isPreview ? '390px' : '';
        this.container.style.cursor = this.isPreview ? 'default' : '';
        this.closeContextMenu();
        this.renderItemList();
    }

    setPreviewWidth(width) {
        if (![390, 768, 1440].includes(width)) return;
        this.stage.style.maxWidth = `${width}px`;
    }

    async request(url, options = {}) {
        this.pendingRequests += 1;
        if (!this.failedActions.size) this.setSaveState('saving');
        try {
            const response = await fetch(url, {
                ...options,
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.config.csrfToken,
                    ...(options.headers || {}),
                },
            });
            const json = await response.json().catch(() => ({}));
            if (!response.ok || json.success === false) {
                throw new Error(json.message || `Request failed (${response.status})`);
            }
            return json;
        } finally {
            this.pendingRequests -= 1;
            if (!this.hasPendingSaves()) this.setSaveState('saved');
        }
    }

    failSave(retry, error, key = null) {
        const operationKey = key || `failure:${++this.failureSequence}`;
        this.failedActions.set(operationKey, { retry, error });
        this.setSaveState('error');
        this.saveRetry.style.display = 'inline-block';
        this.showToast(error?.message || this.config.translations.saveFailed);
    }

    async retryFailedActions() {
        if (this.retryingFailures || !this.failedActions.size) return;
        this.retryingFailures = true;
        this.setSaveState('saving');
        this.saveRetry.style.display = 'none';
        const failures = [...this.failedActions.entries()];

        for (const [key, failure] of failures) {
            if (!this.failedActions.has(key)) continue;
            this.failedActions.delete(key);
            try {
                await failure.retry();
            } catch (error) {
                this.failSave(failure.retry, error, key);
            }
        }

        this.retryingFailures = false;
        if (this.failedActions.size) {
            this.setSaveState('error');
            this.saveRetry.style.display = 'inline-block';
        } else if (!this.hasPendingSaves()) {
            this.setSaveState('saved');
        }
    }

    setSaveState(state) {
        const text = this.config.translations[state] || this.config.translations.error;
        this.saveIndicator.textContent = text;
        this.saveIndicator.className = `save-indicator ${state} ml-3 edit-only`;
        if (state === 'saved') {
            this.saveRetry.style.display = 'none';
        }
    }

    hasPendingSaves() {
        return this.pendingRequests > 0
            || this.moveSaves.size > 0
            || this.failedActions.size > 0
            || this.retryingFailures;
    }

    getRecents() {
        try { return JSON.parse(sessionStorage.getItem(this.recentKey()) || '[]').slice(0, 5); }
        catch (_) { return []; }
    }

    rememberRecent(product) {
        const recents = [product, ...this.getRecents().filter((item) => item.id !== product.id)].slice(0, 5);
        try { sessionStorage.setItem(this.recentKey(), JSON.stringify(recents)); } catch (_) { /* unavailable storage */ }
    }

    recentKey() { return `inspiration-mapper-recents-${this.config.inspirationId}`; }
    updateUrl(id) { return this.config.updateUrlTemplate.replace('__HOTSPOT_ID__', id); }
    destroyUrl(id) { return this.config.destroyUrlTemplate.replace('__HOTSPOT_ID__', id); }
    clamp(value) { return Math.max(0, Math.min(1, Number(value))); }
    announce(message) { this.liveRegion.textContent = ''; setTimeout(() => { this.liveRegion.textContent = message; }, 0); }

    showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.setAttribute('role', 'status');
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2000);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (window.MAPPER_CONFIG) window.mapper = new InspirationMapper(window.MAPPER_CONFIG);
});
