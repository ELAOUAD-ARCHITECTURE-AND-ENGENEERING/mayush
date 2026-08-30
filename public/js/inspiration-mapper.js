'use strict';

class InspirationMapper {
    constructor(config) {
        this.config = config;
        this.items = [...config.existingItems];
        this.mode = 'place'; // 'place' | 'drag'
        this.isPreview = false;
        this.pendingClick = null;
        this.dragState = null;
        this.undoStack = [];
        this.redoStack = [];
        this.saveTimeout = null;
        this.selectedIndex = -1;
        this.searchDebounce = null;
        this.searchSelectedIdx = 0;

        this.container = document.getElementById(config.containerId);
        this.image = document.getElementById(config.imageId);
        this.modal = document.getElementById('searchModal');
        this.searchInput = document.getElementById('searchInput');
        this.searchResults = document.getElementById('searchResults');
        this.itemsList = document.getElementById('itemsList');
        this.itemCount = document.getElementById('itemCount');
        this.saveIndicator = document.getElementById('saveIndicator');

        this.bindEvents();
        this.renderMarkers();
        this.renderItemList();
    }

    // --- Event Binding ---

    bindEvents() {
        // Image click (placement mode)
        this.container.addEventListener('click', (e) => {
            if (this.mode !== 'place' || this.isPreview) return;
            if (e.target.closest('.hotspot-marker')) return;
            const rect = this.image.getBoundingClientRect();
            const x = Math.max(0, Math.min(1, (e.clientX - rect.left) / rect.width));
            const y = Math.max(0, Math.min(1, (e.clientY - rect.top) / rect.height));
            this.pendingClick = { x, y };
            this.openSearchModal();
        });

        // Marker interactions
        this.container.addEventListener('mousedown', (e) => {
            const marker = e.target.closest('.hotspot-marker');
            if (!marker) return;
            const idx = parseInt(marker.dataset.index);
            if (this.mode === 'drag') {
                this.startDrag(e, idx);
            } else {
                e.stopPropagation();
                this.selectMarker(idx);
            }
        });

        // Touch support for drag
        this.container.addEventListener('touchstart', (e) => {
            const marker = e.target.closest('.hotspot-marker');
            if (!marker || this.mode !== 'drag') return;
            e.preventDefault();
            const idx = parseInt(marker.dataset.index);
            this.startDrag(e.touches[0], idx, true);
        }, { passive: false });

        // Search modal
        this.searchInput.addEventListener('input', () => {
            clearTimeout(this.searchDebounce);
            this.searchDebounce = setTimeout(() => this.doSearch(), 300);
        });
        this.searchInput.addEventListener('keydown', (e) => this.handleSearchKeydown(e));
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) this.closeSearchModal();
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') this.closeSearchModal();
            if (e.ctrlKey && e.key === 'z' && !e.shiftKey) { e.preventDefault(); this.undo(); }
            if (e.ctrlKey && e.key === 'z' && e.shiftKey) { e.preventDefault(); this.redo(); }
            if (e.ctrlKey && e.key === 'Z') { e.preventDefault(); this.redo(); }
            // Arrow nudge for selected marker
            if (this.selectedIndex >= 0 && ['ArrowUp','ArrowDown','ArrowLeft','ArrowRight'].includes(e.key)) {
                e.preventDefault();
                this.nudgeMarker(e.key);
            }
        });

        // Resize observer
        if (window.ResizeObserver) {
            new ResizeObserver(() => this.renderMarkers()).observe(this.container);
        }

        // Unsaved changes warning
        window.addEventListener('beforeunload', (e) => {
            if (this.saveTimeout) { e.preventDefault(); e.returnValue = ''; }
        });
    }

    // --- Marker Rendering ---

    renderMarkers() {
        this.container.querySelectorAll('.hotspot-marker').forEach(el => el.remove());
        this.items.forEach((item, idx) => {
            if (item.x == null || item.y == null) return;
            const marker = document.createElement('div');
            marker.className = 'hotspot-marker' + (idx === this.selectedIndex ? ' active' : '');
            marker.dataset.index = idx;
            marker.style.left = (item.x * 100) + '%';
            marker.style.top = (item.y * 100) + '%';
            marker.textContent = idx + 1;
            marker.tabIndex = 0;
            marker.setAttribute('role', 'button');
            marker.setAttribute('aria-label', `Point ${idx + 1}: ${item.product?.name || ''}`);

            if (!item.product?.available) {
                marker.style.background = '#999';
            }

            this.container.appendChild(marker);
        });
    }

    renderItemList() {
        this.itemsList.innerHTML = '';
        this.itemCount.textContent = this.items.length;
        this.items.forEach((item, idx) => {
            const row = document.createElement('div');
            row.className = 'item-list-row' + (!item.product?.available ? ' unavailable' : '');
            row.innerHTML = `
                <span class="number">${idx + 1}</span>
                <img src="${item.product?.image || ''}" alt="">
                <span class="name">${item.product?.name || 'Unknown'}</span>
                <span class="price">${item.product?.price || ''}</span>
                ${!item.product?.available ? '<span class="stock-badge out-of-stock">Indisponible</span>' : ''}
                <button class="btn btn-sm btn-soft-danger btn-icon btn-circle" onclick="mapper.deleteItem(${idx})" title="Delete">
                    <i class="las la-trash"></i>
                </button>
            `;
            row.addEventListener('click', (e) => {
                if (e.target.closest('button')) return;
                this.selectMarker(idx);
            });
            this.itemsList.appendChild(row);
        });
    }

    selectMarker(idx) {
        this.selectedIndex = this.selectedIndex === idx ? -1 : idx;
        this.renderMarkers();
    }

    // --- Drag & Drop ---

    startDrag(event, idx, isTouch = false) {
        const item = this.items[idx];
        if (!item) return;
        const oldX = item.x, oldY = item.y;
        const marker = this.container.querySelector(`[data-index="${idx}"]`);
        if (marker) marker.classList.add('dragging');

        let moved = false;
        let rafId = null;
        let lastClientX = event.clientX, lastClientY = event.clientY;

        const onMove = (e) => {
            const pt = isTouch ? e.touches[0] : e;
            const dx = Math.abs(pt.clientX - event.clientX);
            const dy = Math.abs(pt.clientY - event.clientY);
            if (!moved && dx < 3 && dy < 3) return;
            moved = true;
            lastClientX = pt.clientX;
            lastClientY = pt.clientY;
            if (!rafId) {
                rafId = requestAnimationFrame(() => {
                    const rect = this.image.getBoundingClientRect();
                    item.x = Math.max(0, Math.min(1, (lastClientX - rect.left) / rect.width));
                    item.y = Math.max(0, Math.min(1, (lastClientY - rect.top) / rect.height));
                    this.renderMarkers();
                    rafId = null;
                });
            }
        };

        const onEnd = () => {
            if (marker) marker.classList.remove('dragging');
            document.removeEventListener(isTouch ? 'touchmove' : 'mousemove', onMove);
            document.removeEventListener(isTouch ? 'touchend' : 'mouseup', onEnd);
            if (moved && item.hotspot_id) {
                this.pushUndo({ type: 'move', idx, oldX, oldY, newX: item.x, newY: item.y });
                this.saveHotspotPosition(item);
            }
        };

        document.addEventListener(isTouch ? 'touchmove' : 'mousemove', onMove, { passive: false });
        document.addEventListener(isTouch ? 'touchend' : 'mouseup', onEnd);
    }

    nudgeMarker(key) {
        const item = this.items[this.selectedIndex];
        if (!item) return;
        const step = 0.005;
        const oldX = item.x, oldY = item.y;
        if (key === 'ArrowLeft') item.x = Math.max(0, item.x - step);
        if (key === 'ArrowRight') item.x = Math.min(1, item.x + step);
        if (key === 'ArrowUp') item.y = Math.max(0, item.y - step);
        if (key === 'ArrowDown') item.y = Math.min(1, item.y + step);
        this.renderMarkers();
        this.pushUndo({ type: 'move', idx: this.selectedIndex, oldX, oldY, newX: item.x, newY: item.y });
        this.saveHotspotPosition(item);
    }

    // --- Product Search ---

    openSearchModal() {
        this.modal.classList.add('open');
        this.searchInput.value = '';
        this.searchResults.innerHTML = '';
        this.searchSelectedIdx = 0;
        setTimeout(() => this.searchInput.focus(), 100);
    }

    closeSearchModal() {
        this.modal.classList.remove('open');
        this.pendingClick = null;
    }

    async doSearch() {
        const q = this.searchInput.value.trim();
        if (q.length < 2) { this.searchResults.innerHTML = ''; return; }

        this.searchResults.innerHTML = `<div style="padding:16px;text-align:center;color:#999">${this.config.translations.loading}</div>`;

        try {
            const res = await fetch(`${this.config.searchUrl}?q=${encodeURIComponent(q)}`, {
                headers: { 'X-CSRF-TOKEN': this.config.csrfToken, 'Accept': 'application/json' },
            });
            const json = await res.json();
            const products = json.data || [];

            if (products.length === 0) {
                this.searchResults.innerHTML = `<div style="padding:16px;text-align:center;color:#999">${this.config.translations.noResults}</div>`;
                return;
            }

            this.searchSelectedIdx = 0;
            this.searchResults.innerHTML = products.map((p, i) => `
                <div class="search-result-item${i === 0 ? ' selected' : ''}" data-product-id="${p.id}" data-idx="${i}">
                    <img src="${p.image || ''}" alt="">
                    <div>
                        <div class="name">${p.name}</div>
                        <div class="price">${p.price}</div>
                    </div>
                    <span class="stock-badge ${p.stock === 'in_stock' ? 'in-stock' : 'out-of-stock'}">${p.available ? 'En stock' : 'Indisponible'}</span>
                </div>
            `).join('');

            this.searchResults.querySelectorAll('.search-result-item').forEach(el => {
                el.addEventListener('click', () => this.selectProduct(JSON.parse(JSON.stringify(products[parseInt(el.dataset.idx)])), el));
            });
        } catch (err) {
            this.searchResults.innerHTML = `<div style="padding:16px;text-align:center;color:#c00">Error</div>`;
        }
    }

    handleSearchKeydown(e) {
        const items = this.searchResults.querySelectorAll('.search-result-item');
        if (!items.length) return;
        if (e.key === 'ArrowDown') { e.preventDefault(); this.searchSelectedIdx = Math.min(this.searchSelectedIdx + 1, items.length - 1); }
        if (e.key === 'ArrowUp') { e.preventDefault(); this.searchSelectedIdx = Math.max(this.searchSelectedIdx - 1, 0); }
        items.forEach((el, i) => el.classList.toggle('selected', i === this.searchSelectedIdx));
        items[this.searchSelectedIdx]?.scrollIntoView({ block: 'nearest' });
        if (e.key === 'Enter') { e.preventDefault(); items[this.searchSelectedIdx]?.click(); }
    }

    async selectProduct(product, el) {
        if (!this.pendingClick) return;
        this.closeSearchModal();
        const { x, y } = this.pendingClick;

        this.setSaveState('saving');
        try {
            const res = await fetch(this.config.storeUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.config.csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ product_id: product.id, x, y }),
            });
            const json = await res.json();
            if (json.success) {
                const newItem = { ...json.item, product: json.item.product };
                this.items.push(newItem);
                this.pushUndo({ type: 'place', idx: this.items.length - 1 });
                this.renderMarkers();
                this.renderItemList();
                this.setSaveState('saved');
            }
        } catch (err) {
            this.setSaveState('error');
        }
    }

    // --- Delete ---

    async deleteItem(idx) {
        const item = this.items[idx];
        if (!item) return;

        this.setSaveState('saving');
        try {
            const url = this.config.destroyUrlTemplate.replace('__HOTSPOT_ID__', item.hotspot_id);
            const res = await fetch(url, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': this.config.csrfToken, 'Accept': 'application/json' },
            });
            const json = await res.json();
            if (json.success) {
                const removed = this.items.splice(idx, 1)[0];
                this.pushUndo({ type: 'delete', idx, item: removed });
                if (this.selectedIndex === idx) this.selectedIndex = -1;
                this.renderMarkers();
                this.renderItemList();
                this.setSaveState('saved');
            }
        } catch (err) {
            this.setSaveState('error');
        }
    }

    // --- Save hotspot position (move/nudge) ---

    saveHotspotPosition(item) {
        clearTimeout(this.saveTimeout);
        this.setSaveState('saving');
        this.saveTimeout = setTimeout(async () => {
            try {
                const url = this.config.updateUrlTemplate.replace('__HOTSPOT_ID__', item.hotspot_id);
                await fetch(url, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.config.csrfToken, 'Accept': 'application/json' },
                    body: JSON.stringify({ x: item.x, y: item.y }),
                });
                this.setSaveState('saved');
            } catch (err) {
                this.setSaveState('error');
            }
            this.saveTimeout = null;
        }, 500);
    }

    // --- Undo / Redo ---

    pushUndo(action) {
        this.undoStack.push(action);
        if (this.undoStack.length > 50) this.undoStack.shift();
        this.redoStack = [];
    }

    async undo() {
        const action = this.undoStack.pop();
        if (!action) return;
        this.redoStack.push(action);

        if (action.type === 'move') {
            this.items[action.idx].x = action.oldX;
            this.items[action.idx].y = action.oldY;
            this.saveHotspotPosition(this.items[action.idx]);
        } else if (action.type === 'place') {
            const item = this.items[action.idx];
            if (item?.hotspot_id) {
                const url = this.config.destroyUrlTemplate.replace('__HOTSPOT_ID__', item.hotspot_id);
                await fetch(url, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': this.config.csrfToken, 'Accept': 'application/json' } });
            }
            this.items.splice(action.idx, 1);
        } else if (action.type === 'delete') {
            // Re-create on server
            const res = await fetch(this.config.storeUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.config.csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ product_id: action.item.product.id, x: action.item.x, y: action.item.y }),
            });
            const json = await res.json();
            if (json.success) {
                this.items.splice(action.idx, 0, { ...json.item, product: json.item.product });
            }
        }

        this.renderMarkers();
        this.renderItemList();
        this.showToast(`${this.config.translations.undone}: ${action.type}`);
    }

    async redo() {
        const action = this.redoStack.pop();
        if (!action) return;
        this.undoStack.push(action);

        if (action.type === 'move') {
            this.items[action.idx].x = action.newX;
            this.items[action.idx].y = action.newY;
            this.saveHotspotPosition(this.items[action.idx]);
        }
        // Place and delete redo are more complex — simplified: just re-apply
        this.renderMarkers();
        this.renderItemList();
    }

    // --- Mode switching ---

    switchMode(mode) {
        this.mode = mode;
        this.container.className = `mapper-container mode-${mode}`;
        document.querySelectorAll('.mode-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.mode === mode);
        });
    }

    // --- Preview ---

    togglePreview() {
        this.isPreview = !this.isPreview;
        document.querySelectorAll('.mode-btn, #saveIndicator').forEach(el => {
            el.style.display = this.isPreview ? 'none' : '';
        });
        const itemActions = this.itemsList.querySelectorAll('button');
        itemActions.forEach(btn => btn.style.display = this.isPreview ? 'none' : '');
        this.container.style.cursor = this.isPreview ? 'default' : '';
    }

    // --- UI Helpers ---

    setSaveState(state) {
        const t = this.config.translations;
        if (state === 'saved') { this.saveIndicator.textContent = t.saved; this.saveIndicator.className = 'save-indicator saved'; }
        else if (state === 'saving') { this.saveIndicator.textContent = t.saving; this.saveIndicator.className = 'save-indicator saving'; }
        else { this.saveIndicator.textContent = t.error; this.saveIndicator.className = 'save-indicator error'; }
    }

    showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 2000);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (window.MAPPER_CONFIG) {
        window.mapper = new InspirationMapper(window.MAPPER_CONFIG);
    }
});
