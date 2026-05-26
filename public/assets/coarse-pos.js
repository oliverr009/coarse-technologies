document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('[data-theme-toggle]');
    const metaTheme = document.querySelector('meta[name="theme-color"]');

    const applyTheme = (theme) => {
        document.documentElement.dataset.theme = theme;
        document.body?.setAttribute('data-theme', theme);
        localStorage.setItem('coarse-theme', theme);
        localStorage.setItem('pos-theme', theme);
        if (metaTheme) metaTheme.setAttribute('content', theme === 'light' ? '#f5f8fc' : '#0e1230');
        if (toggle) {
            toggle.setAttribute('aria-label', theme === 'light' ? 'Switch to dark theme' : 'Switch to light theme');
            toggle.title = theme === 'light' ? 'Switch to dark theme' : 'Switch to light theme';
            const icon = toggle.querySelector('i');
            if (icon) icon.className = theme === 'light' ? 'ti ti-moon' : 'ti ti-sun';
        }
    };

    applyTheme(localStorage.getItem('pos-theme') || localStorage.getItem('coarse-theme') || document.documentElement.dataset.theme || 'light');

    toggle?.addEventListener('click', () => {
        applyTheme(document.documentElement.dataset.theme === 'light' ? 'dark' : 'light');
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-pos-root]');
    if (!root || root.dataset.posReady === '1') return;
    root.dataset.posReady = '1';

    const state = {
        items: [],
        category: 'all',
        search: '',
        orderId: '',
        orderNumber: 'ORD-DRAFT',
        orderType: 'dine_in',
        status: 'open',
        held: false,
        discountType: 'fixed',
        discountValue: 0,
        discountReason: '',
        managerPin: '',
        voidEvents: [],
        splitWays: 2,
        noteItemId: null,
        voidItemId: null,
    };

    const taxRate = Number(root.dataset.taxRate || 0);
    const serviceRate = Number(root.dataset.defaultServiceRate || 10);
    const waiterName = root.dataset.cashier || 'Cashier';
    const selectedOrderId = String(root.dataset.selectedOrder || '');
    const modalIds = ['mVoid', 'mNote', 'mDisc', 'mSplit'];
    const money = (value) => `KES ${Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 })}`;
    const recentKey = 'coarse-pos-recent-products';

    const orderTypeButtons = [...root.querySelectorAll('[data-order-type-btn]')];
    const categoryButtons = [...root.querySelectorAll('[data-pos-category]')];
    const productButtons = [...root.querySelectorAll('[data-pos-product]')];
    const productSections = [...root.querySelectorAll('[data-pos-section]')];
    const searchInput = root.querySelector('[data-pos-search]');
    const searchClear = root.querySelector('[data-pos-search-clear]');
    const emptyResults = root.querySelector('[data-pos-empty]');
    const recentCountNodes = [...root.querySelectorAll('[data-recent-count]')];
    const cartBox = root.querySelector('[data-cart-items]');
    const tableSelect = root.querySelector('[data-table-select]');
    const customerSelect = root.querySelector('[data-customer-select]');
    const waiterSelect = root.querySelector('[data-waiter-select]');
    const notesInput = root.querySelector('[data-notes-input]');
    const draftTotal = root.querySelector('[data-draft-total]');
    const subtotalNode = root.querySelector('[data-subtotal]');
    const discountNode = root.querySelector('[data-discount]');
    const discountRow = root.querySelector('[data-discount-row]');
    const serviceNode = root.querySelector('[data-service]');
    const taxNode = root.querySelector('[data-tax]');
    const totalNodes = [...root.querySelectorAll('[data-total]')];
    const orderNumberNode = root.querySelector('[data-order-number]');
    const billModeNode = root.querySelector('[data-bill-mode]');
    const billStatusNode = root.querySelector('[data-bill-status]');
    const orderButtonLabel = root.querySelector('[data-order-button-label]');
    const cartCountNode = root.querySelector('[data-cart-count]');
    const draftBillButton = root.querySelector('[data-draft-bill]');
    const openBillButtons = [...root.querySelectorAll('[data-open-order]')];
    const cartBadge = document.getElementById('cartBadge');
    const saleForm = root.querySelector('[data-sale-form]');
    const holdForm = root.querySelector('[data-hold-form]');
    const kitchenForm = root.querySelector('[data-kitchen-form]');

    const stationFor = (item) => {
        const category = String(item.category || '').toLowerCase();
        if (/(drink|bar|coffee|tea|lemonade|juice|milkshake|mocktail|cocktail)/.test(category)) return { label: 'BAR', className: 'cs-b' };
        if (/(salad|dessert)/.test(category)) return { label: 'COLD', className: 'cs-c' };
        return { label: 'KITCHEN', className: 'cs-k' };
    };

    const kdsClass = (status) => ({
        pending: 'kds-pending',
        cooking: 'kds-cooking',
        ready: 'kds-ready',
    }[status || 'pending'] || 'kds-pending');

    const parseJson = (text) => {
        try {
            return JSON.parse(text || '{}');
        } catch (_error) {
            return {};
        }
    };

    const recentIds = () => {
        try {
            return JSON.parse(localStorage.getItem(recentKey) || '[]').map(String);
        } catch (_error) {
            return [];
        }
    };

    const setRecentIds = (ids) => {
        const nextIds = ids.slice(0, 12).map(String);
        localStorage.setItem(recentKey, JSON.stringify(nextIds));
        recentCountNodes.forEach((node) => { node.textContent = String(nextIds.length); });
    };

    const rememberRecent = (productId) => {
        const id = String(productId);
        setRecentIds([id, ...recentIds().filter((itemId) => itemId !== id)]);
    };

    const toast = (message, tone = 'blue') => {
        const note = document.createElement('div');
        note.className = `pos-toast ${tone}`;
        note.textContent = message;
        root.appendChild(note);
        window.setTimeout(() => note.remove(), 1700);
    };

    const totals = () => {
        const subtotal = state.items.reduce((sum, item) => sum + (item.qty * item.price), 0);
        const rawDiscount = state.discountType === 'percent'
            ? subtotal * Math.min(state.discountValue, 100) / 100
            : state.discountValue;
        const discount = Math.min(subtotal, Math.max(0, rawDiscount));
        const service = Math.max(0, (subtotal - discount) * (serviceRate / 100));
        const taxable = Math.max(0, subtotal - discount + service);
        const tax = taxable * (taxRate / 100);
        const total = taxable + tax;

        return { subtotal, discount, service, tax, total };
    };

    const updateBadges = () => {
        billModeNode.textContent = {
            dine_in: 'Dine-in',
            takeaway: 'Takeaway',
            delivery: 'Delivery',
        }[state.orderType] || 'Dine-in';

        if (state.held) {
            billStatusNode.textContent = 'On Hold';
            billStatusNode.className = 'bdg bdg-orange';
        } else if (state.status === 'pending') {
            billStatusNode.textContent = 'Pending';
            billStatusNode.className = 'bdg bdg-green';
        } else {
            billStatusNode.textContent = 'Open';
            billStatusNode.className = 'bdg bdg-gold';
        }
    };

    const orderButtonText = () => {
        if (state.items.length === 0) return 'Print Kitchen Order';
        const stations = new Set(state.items.map((item) => stationFor(item).label));
        if (stations.size === 1 && stations.has('BAR')) return 'Print Bar Order';
        if (stations.size > 1) return 'Print Kitchen & Bar Order';
        return 'Print Kitchen Order';
    };

    const setActiveOrderTypeButton = () => {
        orderTypeButtons.forEach((button) => {
            button.classList.remove('active-dine', 'active-take', 'active-del');
            if (button.dataset.orderTypeBtn === state.orderType) {
                button.classList.add(
                    state.orderType === 'dine_in' ? 'active-dine' : state.orderType === 'takeaway' ? 'active-take' : 'active-del'
                );
            }
        });
    };

    const setActiveBillButton = (buttonToActivate = null) => {
        [draftBillButton, ...openBillButtons].forEach((button) => {
            button?.classList.toggle('active', button === buttonToActivate);
        });
    };

    const syncForms = () => {
        const { total } = totals();
        const cartJson = JSON.stringify(state.items.map((item) => ({
            id: item.id,
            qty: item.qty,
            notes: item.note || '',
        })));
        const baseFields = [
            ['cart_json', cartJson],
            ['order_type', state.orderType],
            ['restaurant_table_id', tableSelect?.value || ''],
            ['customer_id', customerSelect?.value || ''],
            ['covers', '1'],
            ['manager_pin', state.managerPin || ''],
            ['void_events_json', JSON.stringify(state.voidEvents || [])],
            ['notes', notesInput?.value || ''],
        ];

        [holdForm, kitchenForm].forEach((form) => {
            if (!form) return;
            baseFields.forEach(([name, value]) => {
                const input = form.querySelector(`input[name="${name}"]`);
                if (input) input.value = value;
            });
            const orderIdInput = form.querySelector('input[name="order_id"]');
            if (orderIdInput) orderIdInput.value = state.orderId;
        });

        if (saleForm) {
            baseFields.forEach(([name, value]) => {
                const input = saleForm.querySelector(`input[name="${name}"]`);
                if (input) input.value = value;
            });
            saleForm.querySelector('input[name="order_id"]').value = state.orderId;
            saleForm.querySelector('input[name="discount_type"]').value = state.discountType;
            saleForm.querySelector('input[name="discount_value"]').value = String(state.discountValue || 0);
            saleForm.querySelector('input[name="discount_reason"]').value = state.discountReason || '';
            saleForm.querySelector('input[name="service_charge_rate"]').value = String(serviceRate);
            saleForm.querySelector('input[name="payments_json"]').value = JSON.stringify([]);
            saleForm.dataset.total = String(total.toFixed(2));
        }
    };

    const renderCart = () => {
        cartBox.innerHTML = '';
        if (state.items.length === 0) {
            cartBox.innerHTML = `
                <div class="empty-cart">
                    <i class="ti ti-basket" aria-hidden="true"></i>
                    <p>No items yet</p>
                    <span>Tap a product to add it here.</span>
                </div>
            `;
        }

        state.items.forEach((item) => {
            const station = stationFor(item);
            const row = document.createElement('div');
            row.className = 'ci';
            row.innerHTML = `
                <div class="ci-row1">
                    <div class="ci-name">${item.name}</div>
                    <div class="ci-qty-wrap">
                        <button class="ci-btn" type="button" data-delta="-1">−</button>
                        <span class="ci-n">${item.qty}</span>
                        <button class="ci-btn" type="button" data-delta="1">+</button>
                    </div>
                    <div class="ci-price">${money(item.qty * item.price)}</div>
                    <button class="ci-void-btn" type="button" title="Void item"><i class="ti ti-ban"></i></button>
                </div>
                <div class="ci-row2">
                    <span class="ci-station ${station.className}">${station.label}</span>
                    <button class="ci-note-btn" type="button">${item.note ? item.note : 'Add item note'}</button>
                    <span class="ci-kds-dot ${kdsClass(item.kdsStatus)}"></span>
                </div>
            `;
            row.querySelectorAll('[data-delta]').forEach((button) => {
                button.addEventListener('click', () => {
                    item.qty += Number(button.dataset.delta);
                    if (item.qty <= 0) {
                        state.items = state.items.filter((line) => line.id !== item.id);
                    }
                    render();
                });
            });
            row.querySelector('.ci-note-btn').addEventListener('click', () => {
                state.noteItemId = item.id;
                root.querySelector('#noteLbl').textContent = `Note for ${item.name}`;
                root.querySelector('#noteText').value = item.note || '';
                openModal('mNote');
            });
            row.querySelector('.ci-void-btn').addEventListener('click', () => {
                state.voidItemId = item.id;
                root.querySelector('#voidItemLabel').textContent = item.name;
                root.querySelector('#voidReason').value = '';
                root.querySelector('#voidPin').value = '';
                openModal('mVoid');
            });
            cartBox.appendChild(row);
        });
    };

    const renderTotals = () => {
        const current = totals();
        subtotalNode.textContent = money(current.subtotal);
        discountNode.textContent = `- ${money(current.discount)}`;
        discountRow.hidden = current.discount <= 0;
        serviceNode.textContent = money(current.service);
        taxNode.textContent = money(current.tax);
        totalNodes.forEach((node) => { node.textContent = money(current.total); });
        draftTotal.textContent = money(current.total);
        if (cartCountNode) {
            cartCountNode.textContent = `${state.items.reduce((sum, item) => sum + item.qty, 0)} items`;
        }
        if (cartBadge) {
            const count = String(state.items.reduce((sum, item) => sum + item.qty, 0));
            cartBadge.dataset.count = count;
            cartBadge.textContent = count === '0' ? '' : count;
        }
    };

    const renderOrderMeta = () => {
        orderNumberNode.textContent = state.orderNumber;
        orderButtonLabel.textContent = orderButtonText();
        updateBadges();
        setActiveOrderTypeButton();
    };

    const render = () => {
        renderCart();
        renderTotals();
        renderOrderMeta();
        syncForms();
    };

    const filterProducts = () => {
        const recent = recentIds();
        let totalVisible = 0;
        productButtons.forEach((button) => {
            const product = parseJson(button.dataset.posProduct);
            const categoryMatch = state.category === 'all'
                || (state.category === 'recent' ? recent.includes(String(product.id)) : product.category === state.category);
            const searchMatch = !state.search || [product.name, product.category, product.subcategory, product.sku, product.barcode]
                .filter(Boolean)
                .join(' ')
                .toLowerCase()
                .includes(state.search);
            const visible = categoryMatch && searchMatch;
            button.hidden = !visible;
            if (visible) totalVisible += 1;
        });
        productSections.forEach((section) => {
            const visibleCount = [...section.querySelectorAll('[data-pos-product]')].filter((button) => !button.hidden).length;
            section.hidden = visibleCount === 0;
            const countNode = section.querySelector('.cat-count');
            if (countNode) countNode.textContent = String(visibleCount);
        });
        categoryButtons.forEach((button) => {
            button.classList.toggle('active', button.dataset.posCategory === state.category);
        });
        recentCountNodes.forEach((node) => { node.textContent = String(recent.length); });
        if (emptyResults) emptyResults.hidden = totalVisible > 0;
        if (searchClear) searchClear.hidden = state.search.length === 0;
    };

    const openModal = (id) => {
        root.querySelector(`#${id}`)?.classList.add('show');
    };

    const closeModal = (id) => {
        root.querySelector(`#${id}`)?.classList.remove('show');
    };

    const resetDraft = () => {
        state.items = [];
        state.orderId = '';
        state.orderNumber = 'ORD-DRAFT';
        state.orderType = 'dine_in';
        state.status = 'open';
        state.held = false;
        state.discountType = 'fixed';
        state.discountValue = 0;
        state.discountReason = '';
        state.managerPin = '';
        state.voidEvents = [];
        state.noteItemId = null;
        state.voidItemId = null;
        if (tableSelect) tableSelect.value = '';
        if (customerSelect) customerSelect.value = '';
        if (waiterSelect) waiterSelect.value = waiterName;
        if (notesInput) notesInput.value = '';
        setActiveBillButton(draftBillButton);
        render();
    };

    const submitSale = (method, splitLines = null) => {
        if (state.items.length === 0) return;
        if (method === 'credit' && !customerSelect?.value) {
            window.alert('Choose a customer before posting a credit sale.');
            customerSelect?.focus();
            return;
        }
        syncForms();
        const current = totals();
        const payments = splitLines || (method === 'credit' ? [] : [{ method, amount: Number(current.total.toFixed(2)), reference: null }]);
        saleForm.querySelector('input[name="payments_json"]').value = JSON.stringify(payments);
        saleForm.submit();
    };

    const printOrderTicket = () => {
        const stationLabel = orderButtonText().replace('Print ', '');
        const lines = state.items.map((item) => `<div style="display:flex;justify-content:space-between;padding:2px 0"><span>${item.qty} × ${item.name}${item.note ? `<br><small>${item.note}</small>` : ''}</span><strong>${stationFor(item).label}</strong></div>`).join('');
        const win = window.open('', '_blank', 'width=420,height=640');
        if (!win) return;
        win.document.write(`
            <html><head><title>${stationLabel}</title></head>
            <body style="font-family:Arial,sans-serif;padding:18px">
                <h2 style="margin:0 0 6px">COARSE POS</h2>
                <div style="font-size:12px;color:#555;margin-bottom:14px">${stationLabel}<br>${state.orderNumber}</div>
                ${lines}
                <hr>
                <div style="font-size:12px">Waiter: ${waiterSelect?.value || waiterName}</div>
                <div style="font-size:12px">Guest: ${customerSelect?.selectedOptions?.[0]?.textContent || 'Walk-in customer'}</div>
                <div style="font-size:12px">Table: ${tableSelect?.selectedOptions?.[0]?.textContent || 'No table'}</div>
            </body></html>
        `);
        win.document.close();
        win.focus();
        win.print();
        setTimeout(() => win.close(), 250);
    };

    orderTypeButtons.forEach((button) => {
        button.addEventListener('click', () => {
            state.orderType = button.dataset.orderTypeBtn;
            render();
        });
    });

    categoryButtons.forEach((button) => {
        button.addEventListener('click', () => {
            state.category = button.dataset.posCategory || 'all';
            filterProducts();
        });
    });

    searchInput?.addEventListener('input', () => {
        state.search = searchInput.value.trim().toLowerCase();
        filterProducts();
    });

    searchClear?.addEventListener('click', () => {
        searchInput.value = '';
        state.search = '';
        searchInput.focus();
        filterProducts();
    });

    productButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const product = parseJson(button.dataset.posProduct);
            const found = state.items.find((item) => item.id === product.id);
            if (found) {
                found.qty += 1;
            } else {
                state.items.push({
                    id: product.id,
                    name: product.name,
                    price: Number(product.price || 0),
                    qty: 1,
                    category: product.category,
                    subcategory: product.subcategory,
                    note: '',
                    kdsStatus: state.status === 'pending' ? 'cooking' : 'pending',
                });
            }
            rememberRecent(product.id);
            filterProducts();
            toast(`${product.name} added`, 'green');
            render();
        });
    });

    root.querySelectorAll('[data-pay-method]').forEach((button) => {
        button.addEventListener('click', () => {
            closeModal('mPayChooser');
            submitSale(button.dataset.payMethod);
        });
    });

    root.querySelector('[data-open-pay]')?.addEventListener('click', () => {
        if (state.items.length === 0) {
            toast('Add an item before payment', 'gold');
            return;
        }
        renderTotals();
        openModal('mPayChooser');
    });

    root.querySelectorAll('[data-submit-order]').forEach((button) => {
        button.addEventListener('click', () => {
            if (state.items.length === 0) return;
            if (button.dataset.submitOrder === 'hold') {
                state.held = true;
                state.status = 'open';
                syncForms();
                holdForm.submit();
                return;
            }
            if (button.dataset.submitOrder === 'kitchen') {
                state.status = 'pending';
                state.held = false;
                state.items.forEach((item) => { item.kdsStatus = 'cooking'; });
                printOrderTicket();
                syncForms();
                kitchenForm.submit();
            }
        });
    });

    const loadOrderPayload = (button) => {
        const payload = parseJson(button.dataset.openOrder);
        state.orderId = String(payload.id || '');
        state.orderNumber = payload.order_number || 'ORD-DRAFT';
        state.orderType = payload.order_type || 'dine_in';
        state.status = payload.status === 'sent' ? 'pending' : 'open';
        state.held = payload.status === 'held';
        state.discountType = 'fixed';
        state.discountValue = 0;
        state.discountReason = '';
        state.managerPin = '';
        state.voidEvents = [];
        state.items = (payload.items || []).map((item) => ({
            id: item.id,
            name: item.name,
            price: Number(item.price || 0),
            qty: Number(item.qty || 0),
            note: item.notes || '',
            category: item.category,
            subcategory: item.subcategory,
            kdsStatus: payload.status === 'sent' ? 'cooking' : 'pending',
        }));
        if (tableSelect) tableSelect.value = payload.restaurant_table_id || '';
        if (customerSelect) customerSelect.value = payload.customer_id || '';
        if (notesInput) notesInput.value = payload.notes || '';
        setActiveBillButton(button);
        render();
    };

    openBillButtons.forEach((button) => {
        button.addEventListener('click', () => {
            loadOrderPayload(button);
        });
    });

    draftBillButton?.addEventListener('click', () => {
        resetDraft();
    });
    root.querySelector('[data-new-bill]')?.addEventListener('click', () => {
        resetDraft();
    });

    root.querySelectorAll('[data-close-modal]').forEach((button) => {
        button.addEventListener('click', () => closeModal(button.dataset.closeModal));
    });
    modalIds.forEach((id) => {
        root.querySelector(`#${id}`)?.addEventListener('click', (event) => {
            if (event.target.id === id) closeModal(id);
        });
    });

    root.querySelectorAll('[data-note-chip]').forEach((chip) => {
        chip.addEventListener('click', () => {
            const noteText = root.querySelector('#noteText');
            noteText.value = noteText.value ? `${noteText.value}, ${chip.dataset.noteChip}` : chip.dataset.noteChip;
        });
    });

    root.querySelector('[data-save-note]')?.addEventListener('click', () => {
        const item = state.items.find((line) => String(line.id) === String(state.noteItemId));
        if (item) item.note = root.querySelector('#noteText').value.trim();
        closeModal('mNote');
        render();
    });

    root.querySelector('[data-confirm-void]')?.addEventListener('click', () => {
        const reason = root.querySelector('#voidReason').value;
        const pin = root.querySelector('#voidPin').value;
        const item = state.items.find((line) => String(line.id) === String(state.voidItemId));
        if (!reason || !pin || !item) {
            window.alert('Select a reason and enter the manager PIN or manager password.');
            return;
        }
        state.managerPin = pin;
        state.voidEvents.push({
            item_id: item.id,
            item_name: item.name,
            qty: item.qty,
            reason,
        });
        state.items = state.items.filter((line) => String(line.id) !== String(state.voidItemId));
        closeModal('mVoid');
        render();
    });

    root.querySelector('[data-open-discount]')?.addEventListener('click', () => {
        root.querySelector('#discPct').value = '';
        root.querySelector('#discFixed').value = '';
        root.querySelector('#discPin').value = '';
        root.querySelector('#discReason').value = state.discountReason || 'Loyalty discount';
        openModal('mDisc');
    });

    root.querySelector('[data-apply-discount]')?.addEventListener('click', () => {
        const pct = Number(root.querySelector('#discPct').value || 0);
        const fixed = Number(root.querySelector('#discFixed').value || 0);
        const discountValue = pct > 0 ? pct : fixed;
        const reason = root.querySelector('#discReason').value;
        const pin = root.querySelector('#discPin').value;
        if (discountValue > 0 && (!reason || !pin)) {
            window.alert('Discounts require a reason and manager PIN or manager password.');
            return;
        }
        if (pct > 0) {
            state.discountType = 'percent';
            state.discountValue = pct;
        } else {
            state.discountType = 'fixed';
            state.discountValue = fixed;
        }
        state.discountReason = discountValue > 0 ? reason : '';
        if (discountValue > 0) state.managerPin = pin;
        closeModal('mDisc');
        render();
    });

    const updateSplitPreview = () => {
        const total = totals().total;
        root.querySelector('#splitBillTotal').textContent = money(total);
        root.querySelector('#splitN').textContent = String(state.splitWays);
        root.querySelector('#splitPer').textContent = money(Math.ceil(total / state.splitWays));
    };

    root.querySelector('[data-open-split]')?.addEventListener('click', () => {
        if (state.items.length === 0) return;
        state.splitWays = 2;
        updateSplitPreview();
        openModal('mSplit');
    });

    root.querySelectorAll('[data-split-change]').forEach((button) => {
        button.addEventListener('click', () => {
            state.splitWays = Math.max(2, state.splitWays + Number(button.dataset.splitChange));
            updateSplitPreview();
        });
    });

    root.querySelector('[data-confirm-split]')?.addEventListener('click', () => {
        const total = totals().total;
        const each = Number((total / state.splitWays).toFixed(2));
        const payments = Array.from({ length: state.splitWays }, (_value, index) => ({
            method: 'cash',
            amount: index === state.splitWays - 1
                ? Number((total - (each * (state.splitWays - 1))).toFixed(2))
                : each,
            reference: null,
        }));
        closeModal('mSplit');
        submitSale('split', payments);
    });

    [tableSelect, customerSelect, waiterSelect, notesInput].forEach((field) => {
        field?.addEventListener('change', syncForms);
        field?.addEventListener('input', syncForms);
    });

    filterProducts();
    render();
    if (selectedOrderId) {
        const selectedButton = openBillButtons.find((button) => {
            const payload = parseJson(button.dataset.openOrder);
            return String(payload.id || '') === selectedOrderId;
        });
        if (selectedButton) loadOrderPayload(selectedButton);
    }
});

document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('.inventory-page');
    if (!root || root.dataset.inventoryReady === '1') return;
    root.dataset.inventoryReady = '1';

    const rows = [...root.querySelectorAll('[data-inventory-row]')];
    const search = root.querySelector('[data-inventory-search]');
    const type = root.querySelector('[data-inventory-type]');
    const category = root.querySelector('[data-inventory-category]');
    const count = root.querySelector('[data-inventory-count]');
    const empty = root.querySelector('[data-inventory-empty]');

    const filter = () => {
        const query = (search?.value || '').trim().toLowerCase();
        const selectedType = type?.value || 'all';
        const selectedCategory = category?.value || 'all';
        let visible = 0;

        rows.forEach((row) => {
            const typeOk = selectedType === 'all' || row.dataset.type === selectedType;
            const categoryOk = selectedCategory === 'all' || row.dataset.category === selectedCategory;
            const searchOk = !query || (row.dataset.search || '').includes(query);
            const show = typeOk && categoryOk && searchOk;
            row.hidden = !show;
            if (show) visible += 1;
        });

        if (count) count.textContent = visible + (visible === 1 ? ' item' : ' items');
        if (empty) empty.hidden = visible > 0;
    };

    [search, type, category].forEach((control) => {
        control?.addEventListener('input', filter);
        control?.addEventListener('change', filter);
    });

    filter();
});
