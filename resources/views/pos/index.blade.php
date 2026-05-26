@extends('layouts.pos-terminal', ['title' => 'POS - Sales Terminal'])

@php
    $categories = $products->pluck('category.name')->filter()->unique()->values();
    $productsByCategory = $products->groupBy(fn ($product) => $product->category?->name ?: 'Menu');
    $categoryEmoji = [
        'Mains' => '🥩',
        'Sides' => '🥗',
        'Drinks' => '🥤',
        'Desserts' => '🍰',
        'Breakfast' => '🍳',
        'Barista Corner' => '☕',
        'Tea & Chocolate' => '🍵',
        'Iced Tea' => '🧋',
        'Iced Coffee' => '🧊',
        'Snacks' => '🍪',
        'Salads' => '🥬',
        'Soups' => '🍲',
        'Burgers' => '🍔',
        'Sandwiches' => '🥪',
        'Pizza' => '🍕',
        'Kids Menu' => '🧒',
        'Lemonades & Juices' => '🍋',
        'Milkshakes' => '🥛',
        'Mocktails' => '🍹',
        'Cocktails' => '🍸',
        'Bar' => '🍷',
    ];
    $stationForCategory = function (?string $category) {
        $category = strtolower($category ?? '');

        if (preg_match('/drink|bar|coffee|tea|lemonade|juice|milkshake|mocktail|cocktail/', $category)) {
            return ['label' => 'BAR', 'class' => 'pb-bar'];
        }

        if (preg_match('/salad|dessert|ice cream|fruit/', $category)) {
            return ['label' => 'COLD', 'class' => 'pb-cold'];
        }

        return ['label' => 'KITCHEN', 'class' => 'pb-kitchen'];
    };
    $jsonFlags = JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG;
@endphp

@section('content')
@include('pos.partials.sell-screen-v3-styles')
<div data-pos-root data-tax-rate="{{ $taxRate }}" data-default-service-rate="10" data-cashier="{{ auth()->user()->name ?? 'Cashier' }}" data-selected-order="{{ $selectedOrderId }}" style="display:contents">
    <nav>
        <div>
            <div class="nav-title">POS — Sales Terminal</div>
            <div class="nav-sub">Main Branch</div>
        </div>
        <div class="nav-spacer"></div>
        <div class="nav-pill"><i class="ti ti-tools-kitchen-2" style="font-size:12px" aria-hidden="true"></i> Restaurant</div>
        <button class="nav-icon" type="button" data-theme-toggle title="Toggle theme" aria-label="Toggle theme"><i class="ti ti-moon" aria-hidden="true"></i></button>
        <a class="nav-icon" href="{{ route('dashboard') }}" title="Dashboard" aria-label="Dashboard"><i class="ti ti-layout-dashboard" aria-hidden="true"></i></a>
        <a class="nav-icon" href="{{ route('orders') }}" title="Orders" aria-label="Orders"><i class="ti ti-receipt-2" aria-hidden="true"></i></a>
        <a class="nav-icon" href="{{ route('tables') }}" title="Tables" aria-label="Tables"><i class="ti ti-layout-grid" aria-hidden="true"></i></a>
        <div class="avatar">{{ strtoupper(substr(auth()->user()->name ?? 'C', 0, 1)) }}</div>
    </nav>

    <div class="bills-bar">
        <button class="bill-tab active" type="button" data-draft-bill>
            <span class="bt-status bts-open"></span>
            <span class="bt-name">Draft Bill</span>
            <span class="bt-amt" data-draft-total>KES 0</span>
        </button>
        @foreach($openOrders as $order)
            @php
                $orderPayload = [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'order_type' => $order->order_type,
                    'restaurant_table_id' => $order->restaurant_table_id,
                    'customer_id' => $order->customer_id,
                    'customer_name' => $order->customer?->name,
                    'table_name' => $order->table?->name,
                    'covers' => $order->covers,
                    'notes' => $order->notes,
                    'status' => $order->status,
                    'subtotal' => (float) $order->subtotal,
                    'items' => $order->items->map(function ($item) use ($products) {
                        $product = $products->firstWhere('id', $item->product_id);

                        return [
                            'id' => $item->product_id,
                            'name' => $item->product_name,
                            'price' => (float) $item->unit_price,
                            'qty' => (float) $item->quantity,
                            'notes' => $item->notes,
                            'category' => $product?->category?->name,
                            'subcategory' => $product?->subcategory,
                        ];
                    })->values(),
                ];
                $statusClass = $order->status === 'held' ? 'bts-hold' : 'bts-kitchen';
            @endphp
            <button class="bill-tab" type="button" data-open-order="{{ json_encode($orderPayload, $jsonFlags) }}">
                <span class="bt-status {{ $statusClass }}"></span>
                <span class="bt-name">{{ $order->table?->name ?? $order->order_number }}</span>
                <span class="bt-amt">KES {{ number_format($order->subtotal, 0) }}</span>
            </button>
        @endforeach
        <button class="new-bill-btn" type="button" data-new-bill aria-label="New bill">+</button>
    </div>

    <div class="shell">
        <div class="left">
            <div class="left-top">
                <div class="order-type-group">
                    <button class="ot active-dine" type="button" data-order-type-btn="dine_in"><i class="ti ti-tools-kitchen-2" aria-hidden="true"></i>Dine-in</button>
                    <button class="ot" type="button" data-order-type-btn="takeaway"><i class="ti ti-shopping-bag" aria-hidden="true"></i>Takeaway</button>
                    <button class="ot" type="button" data-order-type-btn="delivery"><i class="ti ti-bike" aria-hidden="true"></i>Delivery</button>
                </div>
                <div class="search-wrap">
                    <i class="ti ti-search search-icon" aria-hidden="true"></i>
                    <input class="search-inp" data-pos-search placeholder="Search product or scan barcode…">
                    <button class="search-clear" type="button" data-pos-search-clear aria-label="Clear search" hidden><i class="ti ti-x"></i></button>
                </div>
            </div>

            <div class="cat-tiles-bar">
                <button class="cat-tile active" type="button" data-pos-category="all">
                    All Items <span class="ct-count">{{ $products->count() }}</span>
                </button>
                <button class="cat-tile" type="button" data-pos-category="recent">
                    🕘 Recent <span class="ct-count" data-recent-count>0</span>
                </button>
                @foreach($categories as $category)
                    <button class="cat-tile" type="button" data-pos-category="{{ $category }}">
                        {{ $categoryEmoji[$category] ?? '🍽️' }} {{ $category }}
                        <span class="ct-count">{{ $productsByCategory->get($category)?->count() ?? 0 }}</span>
                    </button>
                @endforeach
            </div>

            <div class="product-area">
                @foreach($productsByCategory as $categoryName => $categoryProducts)
                    <section class="cat-section" data-pos-section="{{ $categoryName }}">
                        <div class="cat-header">
                            <span class="cat-title">{{ $categoryName }}</span>
                            <span class="cat-count">{{ $categoryProducts->count() }}</span>
                        </div>
                        <div class="prod-grid">
                            @foreach($categoryProducts as $product)
                                @php
                                    $productCategory = $product->category?->name;
                                    $station = $stationForCategory($productCategory);
                                    $productPayload = [
                                        'id' => $product->id,
                                        'name' => $product->name,
                                        'price' => (float) $product->selling_price,
                                        'category' => $productCategory,
                                        'subcategory' => $product->subcategory,
                                        'sku' => $product->sku,
                                        'barcode' => $product->barcode,
                                    ];
                                @endphp
                                <button class="prod" type="button" data-pos-product="{{ json_encode($productPayload, $jsonFlags) }}">
                                    <span class="prod-stock {{ $product->is_active ? 'ps-ok' : 'ps-low' }}">{{ $product->is_active ? 'READY' : 'LOW' }}</span>
                                    <span class="prod-station {{ $station['class'] }}">{{ $station['label'] }}</span>
                                    <span class="prod-emoji" aria-hidden="true">{{ $categoryEmoji[$productCategory] ?? '🍽️' }}</span>
                                    <div class="prod-name">{{ $product->name }}</div>
                                    <div class="prod-price">{{ number_format($product->selling_price, 0) }}</div>
                                </button>
                            @endforeach
                        </div>
                    </section>
                    @endforeach
                <div class="pos-empty-results" data-pos-empty hidden>
                    <i class="ti ti-search-off" aria-hidden="true"></i>
                    <strong>No matching items</strong>
                    <span>Try another search term or switch category.</span>
                </div>
            </div>
        </div>

        <div class="right">
            <div class="order-head">
                <div class="order-meta-row">
                    <div class="order-num" data-order-number>ORD-DRAFT</div>
                    <div class="order-badges">
                        <span class="bdg bdg-blue" data-bill-mode>Dine-in</span>
                        <span class="bdg bdg-gold" data-bill-status>Open</span>
                    </div>
                </div>
                <div class="selects-row">
                    <select class="ord-sel" data-waiter-select>
                        <option value="{{ auth()->user()->name ?? 'Cashier' }}">Waiter: {{ auth()->user()->name ?? 'Cashier' }}</option>
                    </select>
                    <button class="table-btn" type="button"><i class="ti ti-layout-grid" aria-hidden="true"></i> <span>No table</span></button>
                    <select class="ord-sel" data-customer-select hidden>
                        <option value="">Walk-in customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                    <select class="ord-sel" data-table-select hidden>
                        <option value="">No table</option>
                        @foreach($tables as $table)
                            <option value="{{ $table->id }}">{{ $table->name }} · {{ $table->status }}</option>
                        @endforeach
                    </select>
                </div>
                <textarea class="bill-note" rows="2" data-notes-input placeholder="Bill notes, allergies, service notes, delivery details" hidden></textarea>
            </div>

            <div class="cart" data-cart-items>
                <div class="empty-cart">
                    <i class="ti ti-basket" aria-hidden="true"></i>
                    <p>No items yet</p>
                    <span>Tap a product to add it here.</span>
                </div>
            </div>

            <div class="totals">
                <div class="tot-row"><span>Subtotal</span><span data-subtotal>KES 0</span></div>
                <div class="tot-row disc-row" data-discount-row hidden><span>Discount</span><span data-discount>KES 0</span></div>
                <div class="tot-row"><span>Service Charge (10%)</span><span data-service>KES 0</span></div>
                <div class="tot-row"><span>VAT ({{ number_format($taxRate, 0) }}%)</span><span data-tax>KES 0</span></div>
                <div class="tot-divider"></div>
                <div class="tot-total"><span>TOTAL</span><span class="tot-total-val" data-total>KES 0</span></div>
                <div class="tot-count" data-cart-count>0 items</div>
            </div>

            <div class="pay-area">
                <button class="pay-main-btn" type="button" data-open-pay><i class="ti ti-cash-register" style="font-size:18px" aria-hidden="true"></i> PAY</button>
                <div class="action-row">
                    <button class="act-btn hold-btn" type="button" data-submit-order="hold"><i class="ti ti-pause" aria-hidden="true"></i> Hold</button>
                    <button class="act-btn disc-btn" type="button" data-open-discount><i class="ti ti-discount" aria-hidden="true"></i> Disc</button>
                    <button class="act-btn split-btn" type="button" data-open-split><i class="ti ti-scissors" aria-hidden="true"></i> Split</button>
                </div>
                <button class="order-btn" type="button" data-submit-order="kitchen"><i class="ti ti-chef-hat" style="font-size:20px" aria-hidden="true"></i> <span data-order-button-label>ORDER</span></button>
            </div>

            <form method="post" action="{{ route('pos.sale') }}" data-sale-form hidden>
                @csrf
                <input type="hidden" name="cart_json">
                <input type="hidden" name="payments_json">
                <input type="hidden" name="order_id">
                <input type="hidden" name="order_type">
                <input type="hidden" name="restaurant_table_id">
                <input type="hidden" name="customer_id">
                <input type="hidden" name="discount_type" value="fixed">
                <input type="hidden" name="discount_value" value="0">
                <input type="hidden" name="discount_reason" value="">
                <input type="hidden" name="service_charge_rate" value="10">
                <input type="hidden" name="manager_pin" value="">
                <input type="hidden" name="void_events_json" value="[]">
                <input type="hidden" name="notes">
            </form>

            <form method="post" action="{{ route('pos.hold') }}" data-hold-form hidden>
                @csrf
                <input type="hidden" name="cart_json">
                <input type="hidden" name="order_id">
                <input type="hidden" name="order_type">
                <input type="hidden" name="restaurant_table_id">
                <input type="hidden" name="customer_id">
                <input type="hidden" name="covers" value="1">
                <input type="hidden" name="manager_pin" value="">
                <input type="hidden" name="void_events_json" value="[]">
                <input type="hidden" name="notes">
            </form>

            <form method="post" action="{{ route('pos.kitchen') }}" data-kitchen-form hidden>
                @csrf
                <input type="hidden" name="cart_json">
                <input type="hidden" name="order_id">
                <input type="hidden" name="order_type">
                <input type="hidden" name="restaurant_table_id">
                <input type="hidden" name="customer_id">
                <input type="hidden" name="covers" value="1">
                <input type="hidden" name="manager_pin" value="">
                <input type="hidden" name="void_events_json" value="[]">
                <input type="hidden" name="notes">
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="mVoid">
        <div class="modal">
            <div class="modal-head"><span class="modal-title" style="color:var(--red)"><i class="ti ti-ban"></i> Void Item</span><button class="modal-x" type="button" data-close-modal="mVoid">×</button></div>
            <div class="lbl">Item</div>
            <div id="voidItemLabel" style="font-size:13px;font-weight:600;margin-bottom:14px;color:var(--text)"></div>
            <div class="lbl">Reason (required)</div>
            <select class="field-inp" id="voidReason">
                <option value="">Select reason…</option>
                <option>Customer changed mind</option>
                <option>Wrong item entered</option>
                <option>Item unavailable</option>
                <option>Quality issue</option>
                <option>Manager override</option>
            </select>
            <div class="lbl">Manager PIN</div>
            <input class="field-inp" type="password" id="voidPin" placeholder="••••" maxlength="4">
            <div class="modal-footer">
                <button class="btn btn-g" type="button" data-close-modal="mVoid">Cancel</button>
                <button class="btn btn-r" type="button" data-confirm-void><i class="ti ti-ban"></i> Void</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="mPayChooser">
        <div class="modal" style="width:380px">
            <div class="modal-head"><span class="modal-title" style="color:var(--green)"><i class="ti ti-cash-register"></i> Choose Payment</span><button class="modal-x" type="button" data-close-modal="mPayChooser">×</button></div>
            <div style="background:var(--bg3);border-radius:10px;padding:14px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:center">
                <span style="font-size:13px;color:var(--text2);font-weight:600">Bill Total</span>
                <span style="font-family:'IBM Plex Mono',monospace;font-size:18px;font-weight:800;color:var(--blue)" data-total>KES 0</span>
            </div>
            <div class="pay-grid" style="margin-bottom:0">
                <button class="pay-grid-btn pgb-mpesa" type="button" data-pay-method="mpesa"><i class="ti ti-device-mobile"></i> M-Pesa</button>
                <button class="pay-grid-btn pgb-cash" type="button" data-pay-method="cash"><i class="ti ti-cash"></i> Cash</button>
                <button class="pay-grid-btn pgb-card" type="button" data-pay-method="card"><i class="ti ti-credit-card"></i> Card</button>
                <button class="pay-grid-btn pgb-credit" type="button" data-pay-method="credit"><i class="ti ti-user-dollar"></i> Credit</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="mNote">
        <div class="modal">
            <div class="modal-head"><span class="modal-title"><i class="ti ti-pencil" style="color:var(--blue)"></i> Item Note</span><button class="modal-x" type="button" data-close-modal="mNote">×</button></div>
            <div class="lbl" id="noteLbl">Note for item</div>
            <div class="chip-row">
                <span class="chip" data-note-chip="No onions">No onions</span>
                <span class="chip" data-note-chip="Extra sauce">Extra sauce</span>
                <span class="chip" data-note-chip="Well done">Well done</span>
                <span class="chip" data-note-chip="No chilli">No chilli</span>
                <span class="chip" data-note-chip="Gluten-free">Gluten-free</span>
                <span class="chip" data-note-chip="No salt">No salt</span>
            </div>
            <textarea class="field-inp" id="noteText" rows="3" placeholder="Custom note…" style="resize:none;margin-bottom:0"></textarea>
            <div class="modal-footer">
                <button class="btn btn-g" type="button" data-close-modal="mNote">Cancel</button>
                <button class="btn btn-p" type="button" data-save-note><i class="ti ti-check"></i> Save</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="mDisc">
        <div class="modal">
            <div class="modal-head"><span class="modal-title" style="color:var(--gold)"><i class="ti ti-discount"></i> Apply Discount</span><button class="modal-x" type="button" data-close-modal="mDisc">×</button></div>
            <div style="display:flex;gap:10px">
                <div style="flex:1"><div class="lbl">Discount %</div><input class="field-inp" type="number" id="discPct" placeholder="e.g. 10" min="0" max="100"></div>
                <div style="flex:1"><div class="lbl">Fixed (KES)</div><input class="field-inp" type="number" id="discFixed" placeholder="e.g. 200"></div>
            </div>
            <div class="lbl">Reason</div>
            <select class="field-inp" id="discReason">
                <option>Loyalty discount</option>
                <option>Staff meal</option>
                <option>Complaint resolution</option>
                <option>Special offer</option>
                <option>Manager override</option>
            </select>
            <div class="lbl">Manager PIN (required for >10%)</div>
            <input class="field-inp" type="password" id="discPin" placeholder="••••" maxlength="4">
            <div class="modal-footer">
                <button class="btn btn-g" type="button" data-close-modal="mDisc">Cancel</button>
                <button class="btn btn-gold" type="button" data-apply-discount><i class="ti ti-discount"></i> Apply</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="mSplit">
        <div class="modal">
            <div class="modal-head"><span class="modal-title" style="color:var(--blue)"><i class="ti ti-scissors"></i> Split Bill</span><button class="modal-x" type="button" data-close-modal="mSplit">×</button></div>
            <div style="background:var(--bg3);border-radius:9px;padding:12px;margin-bottom:14px;display:flex;justify-content:space-between">
                <span style="font-size:12px;color:var(--text2)">Bill Total</span>
                <span id="splitBillTotal" style="font-family:'IBM Plex Mono',monospace;font-size:14px;font-weight:700;color:var(--blue)">KES 0</span>
            </div>
            <div class="lbl">Number of ways</div>
            <div class="split-ways">
                <button class="btn btn-g" type="button" data-split-change="-1">−</button>
                <span class="split-n" id="splitN">2</span>
                <button class="btn btn-g" type="button" data-split-change="1">+</button>
                <span style="font-size:13px;color:var(--text2)">= <strong id="splitPer" style="color:var(--text)">KES 0</strong> each</span>
            </div>
            <div class="modal-footer">
                <button class="btn btn-g" type="button" data-close-modal="mSplit">Cancel</button>
                <button class="btn btn-p" type="button" data-confirm-split><i class="ti ti-scissors"></i> Split & Pay</button>
            </div>
        </div>
    </div>
</div>
@endsection
