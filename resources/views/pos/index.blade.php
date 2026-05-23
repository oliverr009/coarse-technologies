@extends('layouts.app', ['title' => 'POS - Sales Terminal'])

@php
    $categories = $products->pluck('category.name')->filter()->unique()->values();
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
    $jsonFlags = JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_HEX_TAG;
@endphp

@section('content')
<div data-pos-root data-tax-rate="{{ $taxRate }}">
    <div class="pos-layout">
        <div class="pos-left">
            <div class="inp-wrap">
                <i class="ti ti-scan inp-icon" aria-hidden="true"></i>
                <input class="inp inp-pad" placeholder="Search product or scan barcode…" data-pos-search aria-label="Search products">
            </div>

            <div class="pos-filter-tabs" role="tablist" aria-label="Product categories">
                <button class="pos-filter-tab active" type="button" data-pos-category="all">All Items</button>
                @foreach($categories as $category)
                    <button class="pos-filter-tab" type="button" data-pos-category="{{ $category }}">{{ $categoryEmoji[$category] ?? '🍽️' }} {{ $category }}</button>
                @endforeach
            </div>

            <div class="pos-product-grid">
                @foreach($products as $product)
                    @php
                        $productPayload = [
                            'id' => $product->id,
                            'name' => $product->name,
                            'price' => (float) $product->selling_price,
                            'category' => $product->category?->name,
                            'subcategory' => $product->subcategory,
                            'sku' => $product->sku,
                            'barcode' => $product->barcode,
                        ];
                    @endphp
                    <button class="pos-product-card" type="button" data-pos-product="{{ json_encode($productPayload, $jsonFlags) }}" data-pos-product-category="{{ $product->category?->name }}" aria-label="Add {{ $product->name }}, KES {{ number_format($product->selling_price, 2) }}">
                        <div class="pos-product-icon" aria-hidden="true">{{ $categoryEmoji[$product->category?->name] ?? '🍽️' }}</div>
                        <div class="pos-product-name">{{ $product->name }}</div>
                        <div class="pos-product-price">{{ number_format($product->selling_price, 0) }}</div>
                        <div class="pos-product-stock">{{ $product->subcategory ?: 'Available now' }}</div>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="pos-right">
            <div class="cart-title"><i class="ti ti-shopping-cart" style="color:var(--blue)" aria-hidden="true"></i> Current Order <span style="margin-left:auto;font-size:11px;color:var(--text3)" data-cart-count>0 items</span></div>

            <div class="cart-items" data-cart-items>
                <div class="empty-state">
                    <i class="ti ti-basket" aria-hidden="true"></i>
                    <p>No items yet</p>
                    <span>Tap a product to add</span>
                </div>
            </div>

            <div class="cart-divider"></div>

            <div class="cart-totals">
                <div class="ct-row"><span>Subtotal</span><span data-cart-subtotal style="font-family:'Space Mono',monospace">KES 0</span></div>
                <div class="ct-row"><span>VAT ({{ number_format($taxRate, 0) }}%)</span><span data-cart-vat style="font-family:'Space Mono',monospace">KES 0</span></div>
                <div class="ct-total"><span>TOTAL</span><span class="ct-total-val" data-cart-total>KES 0</span></div>
            </div>

            <div class="pos-side-tools">
                <div class="pos-meta-grid">
                    <select class="inp" data-order-type>
                        <option value="dine_in">Dine-in</option>
                        <option value="takeaway">Takeaway</option>
                        <option value="delivery">Delivery</option>
                    </select>
                    <input class="inp" type="number" min="1" max="99" value="1" data-covers placeholder="Covers">
                    <select class="inp" data-customer-select>
                        <option value="">Walk-in customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                    <select class="inp" data-table-select>
                        <option value="">No table</option>
                        @foreach($tables as $table)
                            <option value="{{ $table->id }}">{{ $table->name }} · {{ $table->status }}</option>
                        @endforeach
                    </select>
                </div>
                <textarea class="inp" rows="2" data-notes-input placeholder="Bill notes, allergies, delivery details"></textarea>
            </div>

            <div class="pay-btns">
                <button class="pay-btn pb-mpesa" type="button" data-pay-method="mpesa" aria-label="Pay via M-Pesa"><i class="ti ti-device-mobile" aria-hidden="true"></i> Pay via M-Pesa</button>
                <button class="pay-btn pb-cash" type="button" data-pay-method="cash" aria-label="Pay Cash"><i class="ti ti-cash" aria-hidden="true"></i> Pay Cash</button>
                <button class="pay-btn pb-credit" type="button" data-pay-method="credit" aria-label="Credit Account"><i class="ti ti-credit-card" aria-hidden="true"></i> Credit Account</button>
            </div>

            <div class="pos-quick-actions">
                <button class="btn btn-ghost btn-sm" type="button" data-submit-order="hold">Hold Bill</button>
                <button class="btn btn-ghost btn-sm" type="button" data-submit-order="kitchen">Send to Kitchen</button>
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
                <input type="hidden" name="service_charge_rate" value="0">
                <input type="hidden" name="notes">
            </form>

            <form method="post" action="{{ route('pos.hold') }}" data-hold-form hidden>
                @csrf
                <input type="hidden" name="cart_json">
                <input type="hidden" name="order_type">
                <input type="hidden" name="restaurant_table_id">
                <input type="hidden" name="customer_id">
                <input type="hidden" name="covers">
                <input type="hidden" name="notes">
            </form>

            <form method="post" action="{{ route('pos.kitchen') }}" data-kitchen-form hidden>
                @csrf
                <input type="hidden" name="cart_json">
                <input type="hidden" name="order_type">
                <input type="hidden" name="restaurant_table_id">
                <input type="hidden" name="customer_id">
                <input type="hidden" name="covers">
                <input type="hidden" name="notes">
            </form>
        </div>
    </div>
</div>
@endsection
