document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('.pos-advanced');
    if (!root || root.dataset.localPosReady === '1') return;
    root.dataset.localPosReady = '1';
    root.classList.add('local-pos-ready');

    const state = {
        items: [],
        category: 'all',
        search: '',
        orderId: '',
        orderNumber: '',
        creditMode: false,
    };

    const money = (value) => 'KES ' + Number(value || 0).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

    const numberValue = (selector) => Number(root.querySelector(selector)?.value || 0);
    const textValue = (selector) => root.querySelector(selector)?.value || '';

    const productCards = [...root.querySelectorAll('.prod-card[data-product]')];
    const categoryButtons = [...root.querySelectorAll('[data-pos-category]')];
    const searchInput = root.querySelector('[data-pos-search]');
    const clearSearch = root.querySelector('[data-clear-search]');
    const clearCart = root.querySelector('[data-clear-cart]');
    const cartBox = root.querySelector('[data-cart-items]');
    const emptyResults = root.querySelector('[data-empty-results]');
    const resultsCount = root.querySelector('[data-results-count]');
    const activeFilter = root.querySelector('[data-active-filter]');
    const postBillButton = root.querySelector('[data-post-bill]');
    const checkoutStatus = root.querySelector('[data-checkout-status]');
    const recalledOrder = root.querySelector('[data-recalled-order]');

    const productData = (card) => {
        try {
            const product = JSON.parse(card.dataset.product || '{}');
            if (!product.id || !product.name) {
                throw new Error('Missing product id/name');
            }

            return product;
        } catch (error) {
            const priceText = card.querySelector('.prod-price')?.textContent || '0';

            return {
                id: card.querySelector('.prod-name')?.textContent?.trim() || Math.random().toString(36).slice(2),
                name: card.querySelector('.prod-name')?.textContent?.trim() || 'Unknown item',
                category: card.dataset.category || '',
                subcategory: card.querySelector('.prod-stock')?.textContent?.trim() || '',
                description: card.querySelector('.prod-desc')?.textContent?.trim() || '',
                price: Number(priceText.replace(/[^0-9.]/g, '')) || 0,
            };
        }
    };

    const matches = (product) => {
        const categoryOk = state.category === 'all' || product.category === state.category;
        if (!categoryOk) return false;
        if (!state.search) return true;

        const haystack = [product.name, product.sku, product.barcode, product.category, product.subcategory, product.description]
            .filter(Boolean)
            .join(' ')
            .toLowerCase();

        return haystack.includes(state.search);
    };

    const filterProducts = () => {
        let visible = 0;

        productCards.forEach((card) => {
            const isVisible = matches(productData(card));
            card.hidden = !isVisible;
            if (isVisible) visible++;
        });

        categoryButtons.forEach((button) => {
            button.classList.toggle('active', button.dataset.posCategory === state.category);
        });

        if (emptyResults) emptyResults.hidden = visible > 0;
        if (resultsCount) resultsCount.textContent = visible + (visible === 1 ? ' product' : ' products');
        if (activeFilter) activeFilter.textContent = state.category === 'all' ? 'All Items' : state.category;
    };

    const totals = () => {
        const subtotal = state.items.reduce((sum, item) => sum + item.price * item.qty, 0);
        const discountValue = numberValue('[data-discount-value]');
        const discountType = textValue('[data-discount-type]');
        const discount = discountType === 'percent' ? Math.min(subtotal, subtotal * Math.min(discountValue, 100) / 100) : Math.min(subtotal, discountValue);
        const service = Math.max(0, (subtotal - discount) * numberValue('[data-service-rate]') / 100);
        const tax = 0;
        const total = Math.max(0, subtotal - discount + service + tax);
        const paid = numberValue('[data-cash-amount]') + numberValue('[data-mpesa-amount]') + numberValue('[data-card-amount]');
        const balance = Math.max(0, total - paid);
        const change = Math.max(0, paid - total);

        return { subtotal, discount, service, tax, total, paid, balance, change };
    };

    const selectedCustomer = () => textValue('[data-customer-select]');

    const billPostStatus = (current) => {
        if (state.items.length === 0) {
            return { ok: false, tone: 'idle', text: 'Add products to start a bill.' };
        }

        if (state.creditMode) {
            if (!selectedCustomer()) {
                return { ok: false, tone: 'warn', text: 'Choose a customer before posting a credit sale.' };
            }

            if (current.balance <= 0) {
                return { ok: true, tone: 'ready', text: current.change > 0 ? `Ready to post. Give ${money(current.change)} change.` : 'Ready to post and print receipt.' };
            }

            return { ok: true, tone: 'credit', text: `${money(current.balance)} will be posted to customer credit.` };
        }

        if (current.balance > 0) {
            return { ok: false, tone: 'warn', text: `${money(current.balance)} still unpaid. Add payment or use customer credit.` };
        }

        if (current.change > 0) {
            return { ok: true, tone: 'ready', text: `Ready to post. Give ${money(current.change)} change.` };
        }

        return { ok: true, tone: 'ready', text: 'Ready to post and print receipt.' };
    };

    const syncForms = () => {
        const payments = [];
        const cash = numberValue('[data-cash-amount]');
        const mpesa = numberValue('[data-mpesa-amount]');
        const card = numberValue('[data-card-amount]');
        if (cash > 0) payments.push({ method: 'cash', amount: cash, reference: null });
        if (mpesa > 0) payments.push({ method: 'mpesa', amount: mpesa, reference: textValue('[data-mpesa-ref]') });
        if (card > 0) payments.push({ method: 'card', amount: card, reference: textValue('[data-card-ref]') });

        root.querySelectorAll('input[name="cart_json"]').forEach((input) => { input.value = JSON.stringify(state.items); });
        root.querySelectorAll('input[name="payments_json"]').forEach((input) => { input.value = JSON.stringify(payments); });
        root.querySelectorAll('input[name="order_id"]').forEach((input) => { input.value = state.orderId || ''; });
        root.querySelectorAll('input[name="order_type"]').forEach((input) => { input.value = root.querySelector('.segmented button.active')?.dataset.orderType || 'dine_in'; });
        root.querySelectorAll('input[name="restaurant_table_id"]').forEach((input) => { input.value = textValue('[data-table-select]'); });
        root.querySelectorAll('input[name="customer_id"]').forEach((input) => { input.value = textValue('[data-customer-select]'); });
        root.querySelectorAll('input[name="covers"]').forEach((input) => { input.value = textValue('[data-covers-input]') || '1'; });
        root.querySelectorAll('input[name="discount_type"]').forEach((input) => { input.value = textValue('[data-discount-type]') || 'fixed'; });
        root.querySelectorAll('input[name="discount_value"]').forEach((input) => { input.value = textValue('[data-discount-value]') || '0'; });
        root.querySelectorAll('input[name="service_charge_rate"]').forEach((input) => { input.value = textValue('[data-service-rate]') || '0'; });
        root.querySelectorAll('input[name="notes"]').forEach((input) => { input.value = textValue('[data-notes-input]'); });
    };

    const blankCartMessage = () => '<div style="text-align:center;padding:34px 0;color:var(--text3);font-size:13px">No items yet<br><span style="font-size:11px">Tap a menu item to add it</span></div>';

    const render = () => {
        const current = totals();
        const postStatus = billPostStatus(current);
        cartBox.innerHTML = '';

        if (state.items.length === 0) {
            cartBox.innerHTML = blankCartMessage();
        }

        state.items.forEach((item) => {
            const row = document.createElement('div');
            row.className = 'cart-item cart-item-advanced';
            row.innerHTML = `
                <div class="cart-line-top">
                    <div>
                        <span class="ci-name"></span>
                        <div class="ci-meta"></div>
                    </div>
                    <button class="ci-remove" type="button" title="Remove item">x</button>
                </div>
                <div class="cart-line-actions">
                    <button class="ci-btn" type="button" data-delta="-1">-</button>
                    <span class="ci-count">${item.qty}</span>
                    <button class="ci-btn" type="button" data-delta="1">+</button>
                    <input class="inp item-note" placeholder="Item note">
                    <span class="ci-price">${money(item.price * item.qty)}</span>
                </div>
            `;
            row.querySelector('.ci-name').textContent = item.name;
            row.querySelector('.ci-meta').textContent = `${money(item.price)} each${item.subcategory ? ' · ' + item.subcategory : ''}`;
            row.querySelector('.item-note').value = item.notes || '';
            row.querySelectorAll('[data-delta]').forEach((button) => {
                button.addEventListener('click', () => {
                    item.qty += Number(button.dataset.delta);
                    if (item.qty <= 0) state.items = state.items.filter((line) => line.id !== item.id);
                    render();
                });
            });
            row.querySelector('.ci-remove').addEventListener('click', () => {
                state.items = state.items.filter((line) => line.id !== item.id);
                render();
            });
            row.querySelector('.item-note').addEventListener('input', (event) => {
                item.notes = event.target.value;
                syncForms();
            });
            cartBox.appendChild(row);
        });

        root.querySelector('[data-subtotal]').textContent = money(current.subtotal);
        root.querySelector('[data-discount]').textContent = '-' + money(current.discount);
        root.querySelector('[data-service]').textContent = money(current.service);
        root.querySelector('[data-tax]').textContent = money(current.tax);
        root.querySelector('[data-total]').textContent = money(current.total);
        root.querySelector('[data-paid]').textContent = money(current.paid);
        const changeDue = root.querySelector('[data-change]');
        if (changeDue) changeDue.textContent = money(current.change);
        root.querySelector('[data-balance]').textContent = money(current.balance);
        const lineCount = root.querySelector('[data-line-count]');
        if (lineCount) lineCount.textContent = state.items.length + ' lines';
        const itemCount = root.querySelector('[data-item-count]');
        const totalQty = state.items.reduce((sum, item) => sum + item.qty, 0);
        if (itemCount) itemCount.textContent = totalQty + (totalQty === 1 ? ' item' : ' items');
        const miniTotal = root.querySelector('[data-mini-total]');
        if (miniTotal) miniTotal.textContent = money(current.total);
        if (postBillButton) postBillButton.disabled = !postStatus.ok;
        if (checkoutStatus) {
            checkoutStatus.textContent = postStatus.text;
            checkoutStatus.dataset.tone = postStatus.tone;
        }
        if (recalledOrder) {
            recalledOrder.hidden = !state.orderId;
            recalledOrder.textContent = state.orderId ? `Recalled bill ${state.orderNumber || '#' + state.orderId}. Posting payment will close this open order.` : '';
        }
        syncForms();
    };

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

    clearSearch?.addEventListener('click', () => {
        state.search = '';
        if (searchInput) searchInput.value = '';
        filterProducts();
        searchInput?.focus();
    });

    productCards.forEach((card) => {
        card.addEventListener('click', () => {
            const product = productData(card);
            const found = state.items.find((item) => item.id === product.id);
            if (found) found.qty++;
            else state.items.push({ ...product, id: product.id || product.name, price: Number(product.price || 0), qty: 1, notes: '' });
            render();
        });
    });

    clearCart?.addEventListener('click', () => {
        state.items = [];
        state.orderId = '';
        state.orderNumber = '';
        state.creditMode = false;
        render();
    });

    root.querySelectorAll('[data-discount-type],[data-discount-value],[data-service-rate],[data-cash-amount],[data-mpesa-amount],[data-card-amount],[data-mpesa-ref],[data-card-ref],[data-table-select],[data-customer-select],[data-covers-input],[data-notes-input]').forEach((field) => {
        field.addEventListener('input', render);
        field.addEventListener('change', render);
    });

    root.querySelectorAll('.segmented button').forEach((button) => {
        button.addEventListener('click', () => {
            root.querySelectorAll('.segmented button').forEach((other) => other.classList.remove('active'));
            button.classList.add('active');
            render();
        });
    });

    root.querySelectorAll('[data-pay-full]').forEach((button) => {
        button.addEventListener('click', () => {
            const method = button.dataset.payFull;
            const current = totals();
            state.creditMode = false;
            const fields = {
                cash: root.querySelector('[data-cash-amount]'),
                mpesa: root.querySelector('[data-mpesa-amount]'),
                card: root.querySelector('[data-card-amount]'),
            };
            Object.values(fields).forEach((field) => {
                if (field) field.value = '0';
            });
            if (fields[method]) fields[method].value = current.total.toFixed(2);
            render();
        });
    });

    root.querySelector('[data-pay-credit]')?.addEventListener('click', () => {
        state.creditMode = true;
        render();
    });

    root.querySelectorAll('[data-cash-amount],[data-mpesa-amount],[data-card-amount]').forEach((field) => {
        field.addEventListener('input', () => {
            if (numberValue('[data-cash-amount]') + numberValue('[data-mpesa-amount]') + numberValue('[data-card-amount]') > 0) {
                state.creditMode = false;
                render();
            }
        });
    });

    root.querySelectorAll('[data-open-order]').forEach((button) => {
        button.addEventListener('click', () => {
            let order;
            try {
                order = JSON.parse(button.dataset.openOrder || '{}');
            } catch (error) {
                order = {};
            }

            state.orderId = order.id || '';
            state.orderNumber = order.order_number || '';
            state.creditMode = false;
            state.items = (order.items || []).map((item) => ({
                ...item,
                id: item.id || item.name,
                price: Number(item.price || 0),
                qty: Number(item.qty || 0),
                notes: item.notes || '',
            })).filter((item) => item.qty > 0);

            const table = root.querySelector('[data-table-select]');
            const customer = root.querySelector('[data-customer-select]');
            const covers = root.querySelector('[data-covers-input]');
            const notes = root.querySelector('[data-notes-input]');
            if (table) table.value = order.restaurant_table_id || '';
            if (customer) customer.value = order.customer_id || '';
            if (covers) covers.value = order.covers || 1;
            if (notes) notes.value = order.notes || '';

            root.querySelectorAll('.segmented button').forEach((item) => {
                item.classList.toggle('active', item.dataset.orderType === order.order_type);
            });

            render();
        });
    });

    root.querySelectorAll('[data-pos-form]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (state.items.length === 0) {
                event.preventDefault();
                cartBox.innerHTML = '<div style="text-align:center;padding:34px 0;color:var(--red);font-size:13px">Add at least one product before posting this bill.</div>';
                return;
            }

            if (form.dataset.posForm === 'sale') {
                const current = totals();
                const postStatus = billPostStatus(current);
                if (!postStatus.ok) {
                    event.preventDefault();
                    if (checkoutStatus) {
                        checkoutStatus.textContent = postStatus.text;
                        checkoutStatus.dataset.tone = postStatus.tone;
                    }
                    return;
                }
            }

            syncForms();
        });
    });

    root.querySelector('.segmented button')?.classList.add('active');
    filterProducts();
    render();
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
            if (show) visible++;
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
