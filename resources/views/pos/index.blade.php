@extends('layouts.app', ['title' => 'POS - Sales Terminal'])

@php
    $categories = $products->pluck('category.name')->filter()->unique()->values();
    $categoryIcons = [
        'Mains' => 'M',
        'Sides' => 'S',
        'Drinks' => 'D',
        'Desserts' => 'DS',
        'Breakfast' => 'B',
        'Barista Corner' => 'BC',
        'Tea & Chocolate' => 'TC',
        'Iced Tea' => 'IT',
        'Iced Coffee' => 'IC',
        'Snacks' => 'SN',
        'Salads' => 'SA',
        'Soups' => 'SO',
        'Burgers' => 'BG',
        'Sandwiches' => 'SW',
        'Pizza' => 'PZ',
        'Kids Menu' => 'K',
        'Lemonades & Juices' => 'LJ',
        'Milkshakes' => 'MS',
        'Mocktails' => 'MO',
        'Cocktails' => 'CO',
        'Bar' => 'BR',
    ];
    $jsonFlags = JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG;
@endphp

@section('content')
<div class="pos-advanced">
    <div class="pos-workspace">
        <div class="pos-toolbar card2">
            <div class="pos-brand-cluster">
                <div class="pos-brand-badge" aria-hidden="true">CP</div>
                <div>
                    <div class="pos-brand-kicker">COARSE POS</div>
                    <div class="sec-title">Sales Terminal</div>
                    <div class="pos-toolbar-copy">Restaurant order flow for dine-in, takeaway, delivery, held bills and split-ready settlement.</div>
                </div>
            </div>
            <div class="pos-toolbar-side">
                <div class="pos-toolbar-hint">Select service mode, add items fast, then settle from the live bill rail.</div>
                <div class="segmented">
                    <button class="active" type="button" data-order-type="dine_in">Dine-in</button>
                    <button type="button" data-order-type="takeaway">Takeaway</button>
                    <button type="button" data-order-type="delivery">Delivery</button>
                </div>
            </div>
        </div>

        <div class="pos-main-grid">
            <div class="pos-left">
                <div class="pos-search-row">
                    <input class="inp pos-search" data-pos-search autofocus placeholder="Search product or scan barcode...">
                    <button class="btn btn-ghost btn-sm" type="button" data-clear-search>Clear</button>
                </div>

                <div class="cat-tabs" aria-label="Menu categories">
                    <button type="button" class="cat-tab active" data-pos-category="all">All Items <small>{{ $products->count() }}</small></button>
                    @foreach($categories as $category)
                        @php $categoryCount = $products->filter(fn ($product) => $product->category?->name === $category)->count(); @endphp
                        <button type="button" class="cat-tab" data-pos-category="{{ $category }}">
                            <span>{{ $categoryIcons[$category] ?? substr($category, 0, 1) }}</span> {{ $category }} <small>{{ $categoryCount }}</small>
                        </button>
                    @endforeach
                </div>

                <div class="pos-filter-meta">
                    <span data-results-count>{{ $products->count() }} products</span>
                    <span data-active-filter>All Items</span>
                </div>

                <div class="product-grid">
                    @foreach($products as $product)
                        @php
                            $productPayload = [
                                'id' => $product->id,
                                'name' => $product->name,
                                'sku' => $product->sku,
                                'barcode' => $product->barcode,
                                'subcategory' => $product->subcategory,
                                'description' => $product->description,
                                'price' => (float) $product->selling_price,
                                'category' => $product->category?->name,
                            ];
                            $productJson = json_encode($productPayload, $jsonFlags);
                        @endphp
                        <button type="button" class="prod-card" data-category="{{ $product->category?->name }}" data-product="{{ $productJson }}">
                            <div class="prod-card-top">
                                <div>
                                    <div class="prod-name">{{ $product->name }}</div>
                                    @if($product->subcategory)<div class="prod-stock">{{ $product->subcategory }}</div>@endif
                                </div>
                                <span class="prod-add-hint" aria-hidden="true">+</span>
                            </div>
                            <div class="prod-price">KES {{ number_format($product->selling_price, 2) }}</div>
                            @if($product->description)<div class="prod-desc">{{ Str::limit($product->description, 82) }}</div>@endif
                        </button>
                    @endforeach
                </div>
                <div class="card2 pos-empty-results" data-empty-results hidden>No products match this search or category.</div>

                <div class="card2">
                    <div class="sec-head">
                        <span class="sec-title">Open Bills</span>
                        <span class="badge b-blue">{{ $openOrders->count() }} active</span>
                    </div>
                    <div class="open-bills">
                        @forelse($openOrders as $order)
                            @php
                                $orderPayload = [
                                    'id' => $order->id,
                                    'order_number' => $order->order_number,
                                    'order_type' => $order->order_type,
                                    'restaurant_table_id' => $order->restaurant_table_id,
                                    'customer_id' => $order->customer_id,
                                    'covers' => $order->covers,
                                    'notes' => $order->notes,
                                    'items' => $order->items->map(fn ($item) => [
                                        'id' => $item->product_id,
                                        'name' => $item->product_name,
                                        'price' => (float) $item->unit_price,
                                        'qty' => (float) $item->quantity,
                                        'notes' => $item->notes,
                                    ])->values(),
                                ];
                            @endphp
                            <button type="button" class="open-bill" data-open-order="{{ json_encode($orderPayload, $jsonFlags) }}">
                                <div>
                                    <strong>{{ $order->order_number }}</strong>
                                    <span>{{ str_replace('_', ' ', $order->order_type) }} @if($order->table) · {{ $order->table->name }} @endif</span>
                                </div>
                                <div>
                                    <span class="badge {{ $order->status === 'sent' ? 'b-gold' : 'b-gray' }}">{{ $order->status }}</span>
                                    <strong>KES {{ number_format($order->subtotal, 2) }}</strong>
                                </div>
                            </button>
                        @empty
                            <div style="font-size:12px;color:var(--text3)">No held or kitchen orders yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="pos-right advanced-cart">
                <div class="bill-rail-head">
                    <div>
                        <div class="sec-title">Current Bill</div>
                        <div class="bill-rail-copy">Live transaction rail for this order session.</div>
                    </div>
                    <button class="btn btn-ghost btn-sm" type="button" data-clear-cart>Clear Bill</button>
                </div>

                <div class="bill-summary-strip">
                    <span data-line-count>0 lines</span>
                    <span data-item-count>0 items</span>
                    <strong data-mini-total>KES 0.00</strong>
                </div>

                <div class="bill-context-grid">
                    <div class="bill-context-card">
                        <span>Mode</span>
                        <strong data-order-type-label>Dine-in</strong>
                    </div>
                    <div class="bill-context-card">
                        <span>Service</span>
                        <strong data-order-context>No table / counter sale</strong>
                    </div>
                    <div class="bill-context-card">
                        <span>Guest</span>
                        <strong data-cover-summary>1 cover</strong>
                    </div>
                    <div class="bill-context-card">
                        <span>Status</span>
                        <strong data-bill-status-summary>New bill</strong>
                    </div>
                </div>

                <div class="recalled-order" data-recalled-order hidden></div>

                <div class="bill-section">
                    <div class="bill-section-head">
                        <span class="sec-title">Order Details</span>
                        <span class="bill-inline-note">Attach table, customer and notes before posting.</span>
                    </div>
                    <div class="order-fields">
                        <select class="inp" data-table-select>
                            <option value="">No table / counter sale</option>
                            @foreach($tables as $table)
                                <option value="{{ $table->id }}">{{ $table->name }} · {{ $table->status }}</option>
                            @endforeach
                        </select>
                        <input class="inp" type="number" min="1" max="99" value="1" data-covers-input placeholder="Covers">
                        <select class="inp" data-customer-select>
                            <option value="">Walk-in customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                        <textarea class="inp" rows="2" data-notes-input placeholder="Bill notes, delivery address, allergies"></textarea>
                    </div>
                </div>

                <div class="bill-section bill-items-section">
                    <div class="bill-section-head">
                        <span class="sec-title">Bill Items</span>
                        <span class="bill-inline-note">Tap menu items to build the order.</span>
                    </div>
                    <div class="cart-items advanced-items" data-cart-items>
                        <div class="bill-empty-state">No items yet<br><span>Tap a menu item to add it</span></div>
                    </div>
                </div>

                <div class="bill-section">
                    <div class="bill-section-head">
                        <span class="sec-title">Charges</span>
                        <span class="bill-inline-note">Discounts and service charge apply here.</span>
                    </div>
                    <div class="charge-grid">
                        <select class="inp" data-discount-type>
                            <option value="fixed">Fixed discount</option>
                            <option value="percent">Percent discount</option>
                        </select>
                        <input class="inp" type="number" min="0" step="0.01" value="0" data-discount-value placeholder="Discount">
                        <input class="inp" type="number" min="0" max="100" step="0.01" value="0" data-service-rate placeholder="Service %">
                    </div>

                    <div class="ct-row"><span>Subtotal</span><span data-subtotal>KES 0.00</span></div>
                    <div class="ct-row"><span>Discount</span><span data-discount>-KES 0.00</span></div>
                    <div class="ct-row"><span>Service charge</span><span data-service>KES 0.00</span></div>
                    <div class="ct-row"><span>VAT</span><span data-tax>KES 0.00</span></div>
                    <div class="ct-total"><span>Total</span><span class="ct-total-val" data-total>KES 0.00</span></div>
                </div>

                <div class="payment-panel">
                    <div class="bill-section-head">
                        <span class="sec-title">Settlement</span>
                        <span class="bill-inline-note">Multiple payments and customer credit are supported.</span>
                    </div>
                    <div class="quick-pay">
                        <button type="button" class="btn btn-ghost btn-sm" data-pay-full="cash">Cash full</button>
                        <button type="button" class="btn btn-ghost btn-sm" data-pay-full="mpesa">M-Pesa full</button>
                        <button type="button" class="btn btn-ghost btn-sm" data-pay-full="card">Card full</button>
                        <button type="button" class="btn btn-ghost btn-sm" data-pay-credit>Customer credit</button>
                    </div>
                    <div class="pay-lines">
                        <input class="inp" type="number" min="0" step="0.01" value="0" data-cash-amount placeholder="Cash amount">
                        <input class="inp" type="number" min="0" step="0.01" value="0" data-mpesa-amount placeholder="M-Pesa amount">
                        <input class="inp" type="number" min="0" step="0.01" value="0" data-card-amount placeholder="Card amount">
                        <input class="inp" data-mpesa-ref placeholder="M-Pesa ref">
                        <input class="inp" data-card-ref placeholder="Card ref">
                    </div>
                    <div class="ct-row"><span>Paid</span><span data-paid>KES 0.00</span></div>
                    <div class="ct-row"><span>Change due</span><span data-change>KES 0.00</span></div>
                    <div class="ct-row"><span>Balance / Credit</span><span data-balance>KES 0.00</span></div>
                    <div class="checkout-status" data-checkout-status>Add products to start a bill.</div>
                </div>

                <div class="bill-footer-actions">
                    <form method="post" action="{{ route('pos.sale') }}" class="pay-btns" data-pos-form="sale">
                        @csrf
                        <input type="hidden" name="cart_json">
                        <input type="hidden" name="payments_json">
                        <input type="hidden" name="order_id">
                        <input type="hidden" name="order_type">
                        <input type="hidden" name="restaurant_table_id">
                        <input type="hidden" name="customer_id">
                        <input type="hidden" name="discount_type">
                        <input type="hidden" name="discount_value">
                        <input type="hidden" name="service_charge_rate">
                        <input type="hidden" name="notes">
                        <button class="pay-btn pb-cash" data-post-bill disabled>Post Bill & Print Receipt</button>
                    </form>

                    <div class="bill-actions">
                        <form method="post" action="{{ route('pos.kitchen') }}" data-pos-form="kitchen">
                            @csrf
                            <input type="hidden" name="cart_json">
                            <input type="hidden" name="order_type">
                            <input type="hidden" name="restaurant_table_id">
                            <input type="hidden" name="customer_id">
                            <input type="hidden" name="covers">
                            <input type="hidden" name="notes">
                            <button class="btn btn-gold">Send to Kitchen</button>
                        </form>
                        <form method="post" action="{{ route('pos.hold') }}" data-pos-form="hold">
                            @csrf
                            <input type="hidden" name="cart_json">
                            <input type="hidden" name="order_type">
                            <input type="hidden" name="restaurant_table_id">
                            <input type="hidden" name="customer_id">
                            <input type="hidden" name="covers">
                            <input type="hidden" name="notes">
                            <button class="btn btn-ghost">Hold Bill</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection