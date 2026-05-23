document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-pos-root]');
    if (!root || root.dataset.posReady === '1') return;
    root.dataset.posReady = '1';

    const state = {
        items: [],
        category: 'all',
        search: '',
    };

    const taxRate = Number(root.dataset.taxRate || 0);
    const money = (value) => `KES ${Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 0 })}`;
    const searchInput = root.querySelector('[data-pos-search]');
    const categoryButtons = [...root.querySelectorAll('[data-pos-category]')];
    const productCards = [...root.querySelectorAll('[data-pos-product]')];
    const cartItems = root.querySelector('[data-cart-items]');
    const cartCount = root.querySelector('[data-cart-count]');
    const subtotalNode = root.querySelector('[data-cart-subtotal]');
    const vatNode = root.querySelector('[data-cart-vat]');
    const totalNode = root.querySelector('[data-cart-total]');
    const cartBadge = document.getElementById('cartBadge');
    const saleForm = root.querySelector('[data-sale-form]');
    const holdForm = root.querySelector('[data-hold-form]');
    const kitchenForm = root.querySelector('[data-kitchen-form]');
    const orderTypeField = root.querySelector('[data-order-type]');
    const customerField = root.querySelector('[data-customer-select]');
    const tableField = root.querySelector('[data-table-select]');
    const coversField = root.querySelector('[data-covers]');
    const notesField = root.querySelector('[data-notes-input]');

    const parseProduct = (button) => {
        try {
            return JSON.parse(button.dataset.posProduct || '{}');
        } catch (error) {
            return {};
        }
    };

    const totals = () => {
        const subtotal = state.items.reduce((sum, item) => sum + (item.qty * item.price), 0);
        const vat = subtotal * (taxRate / 100);
        const total = subtotal + vat;

        return { subtotal, vat, total };
    };

    const emptyState = () => `
        <div class="empty-state">
            <i class="ti ti-basket" aria-hidden="true"></i>
            <p>No items yet</p>
            <span>Tap a product to add</span>
        </div>
    `;

    const syncCommonFields = (form) => {
        form.querySelector('input[name="cart_json"]').value = JSON.stringify(state.items.map((item) => ({
            id: item.id,
            qty: item.qty,
            notes: item.notes || '',
        })));
        const orderType = orderTypeField?.value || 'dine_in';
        const tableValue = tableField?.value || '';
        const customerValue = customerField?.value || '';
        const notesValue = notesField?.value || '';
        form.querySelectorAll('input[name="order_type"]').forEach((input) => { input.value = orderType; });
        form.querySelectorAll('input[name="restaurant_table_id"]').forEach((input) => { input.value = tableValue; });
        form.querySelectorAll('input[name="customer_id"]').forEach((input) => { input.value = customerValue; });
        form.querySelectorAll('input[name="covers"]').forEach((input) => { input.value = coversField?.value || '1'; });
        form.querySelectorAll('input[name="notes"]').forEach((input) => { input.value = notesValue; });
    };

    const submitSale = (method) => {
        if (state.items.length === 0) return;
        syncCommonFields(saleForm);
        const { total } = totals();
        let payments = [];

        if (method === 'credit') {
            if (!customerField?.value) {
                window.alert('Choose a customer before posting a credit sale.');
                customerField?.focus();
                return;
            }
        } else {
            payments = [{ method, amount: Number(total.toFixed(2)), reference: null }];
        }

        saleForm.querySelector('input[name="payments_json"]').value = JSON.stringify(payments);
        saleForm.submit();
    };

    const submitOrderForm = (form) => {
        if (state.items.length === 0) return;
        syncCommonFields(form);
        form.submit();
    };

    const render = () => {
        cartItems.innerHTML = '';

        if (state.items.length === 0) {
            cartItems.innerHTML = emptyState();
        }

        state.items.forEach((item) => {
            const row = document.createElement('div');
            row.className = 'cart-item';
            row.innerHTML = `
                <span class="ci-name">${item.name}</span>
                <div class="ci-qty">
                    <button class="ci-btn" type="button" data-delta="-1">−</button>
                    <span class="ci-count">${item.qty}</span>
                    <button class="ci-btn" type="button" data-delta="1">+</button>
                </div>
                <span class="ci-price">${money(item.qty * item.price)}</span>
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
            cartItems.appendChild(row);
        });

        const { subtotal, vat, total } = totals();
        subtotalNode.textContent = money(subtotal);
        vatNode.textContent = money(vat);
        totalNode.textContent = money(total);

        const itemCount = state.items.reduce((sum, item) => sum + item.qty, 0);
        cartCount.textContent = `${itemCount} ${itemCount === 1 ? 'item' : 'items'}`;
        if (cartBadge) {
            cartBadge.dataset.count = String(itemCount);
            cartBadge.textContent = itemCount > 0 ? String(itemCount) : '';
        }
    };

    const matchesFilter = (product) => {
        const categoryMatch = state.category === 'all' || product.category === state.category;
        if (!categoryMatch) return false;
        if (!state.search) return true;

        const haystack = [product.name, product.category, product.subcategory, product.sku, product.barcode]
            .filter(Boolean)
            .join(' ')
            .toLowerCase();

        return haystack.includes(state.search);
    };

    const filterProducts = () => {
        productCards.forEach((card) => {
            const product = parseProduct(card);
            card.hidden = !matchesFilter(product);
        });
        categoryButtons.forEach((button) => {
            button.classList.toggle('active', button.dataset.posCategory === state.category);
        });
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

    productCards.forEach((button) => {
        button.addEventListener('click', () => {
            const product = parseProduct(button);
            const line = state.items.find((item) => item.id === product.id);
            if (line) {
                line.qty += 1;
            } else {
                state.items.push({ id: product.id, name: product.name, price: Number(product.price || 0), qty: 1, notes: '' });
            }
            render();
        });
    });

    root.querySelectorAll('[data-pay-method]').forEach((button) => {
        button.addEventListener('click', () => submitSale(button.dataset.payMethod));
    });

    root.querySelectorAll('[data-submit-order]').forEach((button) => {
        button.addEventListener('click', () => {
            if (button.dataset.submitOrder === 'hold') submitOrderForm(holdForm);
            if (button.dataset.submitOrder === 'kitchen') submitOrderForm(kitchenForm);
        });
    });

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
