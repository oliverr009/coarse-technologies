@extends('layouts.app', ['title' => 'Inventory & Menu Catalog'])

@php
    $typeLabels = [
        'raw_material' => 'Raw material',
        'finished_product' => 'Menu item',
        'resale_item' => 'Resale item',
        'semi_finished' => 'Semi-finished',
        'service' => 'Service',
    ];
@endphp

@section('content')
<div class="inventory-page">
    <div class="stats-row">
        <div class="stat-card"><div class="stat-label">Products</div><div class="stat-value sv-blue">{{ $summary['products'] }}</div><div class="stat-change">Full catalog</div></div>
        <div class="stat-card"><div class="stat-label">Menu Items</div><div class="stat-value sv-gold">{{ $summary['menu_items'] }}</div><div class="stat-change">Visible to POS</div></div>
        <div class="stat-card"><div class="stat-label">Raw Materials</div><div class="stat-value sv-green">{{ $summary['raw_materials'] }}</div><div class="stat-change">Recipe inputs</div></div>
        <div class="stat-card"><div class="stat-label">Low Stock</div><div class="stat-value sv-red">{{ $summary['low_stock'] }}</div><div class="stat-change">At or below reorder</div></div>
    </div>

    <div class="grid-65 inventory-workspace">
        <div class="card">
            <div class="sec-head">
                <span class="sec-title">Product Catalog</span>
                <span class="badge b-blue" data-inventory-count>{{ $products->count() }} items</span>
            </div>

            <div class="inventory-filters">
                <input class="inp" data-inventory-search placeholder="Search name, SKU, barcode, category, description">
                <select class="inp" data-inventory-type>
                    <option value="all">All product types</option>
                    @foreach($typeLabels as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <select class="inp" data-inventory-category>
                    <option value="all">All categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->name }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="tbl-wrap inventory-table">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Stock</th>
                            <th>Reorder</th>
                            <th>Cost</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($products as $product)
                        @php
                            $stock = (float) ($product->stock_qty ?? 0);
                            $reorder = (float) $product->reorder_level;
                            $isStocked = in_array($product->product_type, ['raw_material', 'resale_item', 'semi_finished'], true);
                            $isLow = $isStocked && $stock <= $reorder;
                            $searchText = strtolower(implode(' ', [
                                $product->name,
                                $product->sku,
                                $product->barcode,
                                $product->category?->name,
                                $product->subcategory,
                                $product->description,
                            ]));
                        @endphp
                        <tr data-inventory-row
                            data-type="{{ $product->product_type }}"
                            data-category="{{ $product->category?->name }}"
                            data-search="{{ $searchText }}">
                            <td>
                                <strong>{{ $product->name }}</strong>
                                <div class="muted-line">
                                    {{ $product->category?->name ?? 'Uncategorized' }}
                                    @if($product->subcategory) · {{ $product->subcategory }} @endif
                                    @if(!$product->is_active) · inactive @endif
                                </div>
                                @if($product->description)<div class="muted-line">{{ Str::limit($product->description, 80) }}</div>@endif
                            </td>
                            <td><span class="badge {{ $product->product_type === 'raw_material' ? 'b-green' : ($product->product_type === 'finished_product' ? 'b-gold' : 'b-blue') }}">{{ $typeLabels[$product->product_type] ?? $product->product_type }}</span></td>
                            <td>
                                <strong class="{{ $isLow ? 'sv-red' : 'sv-blue' }}">{{ number_format($stock, 4) }} {{ $product->unit }}</strong>
                                @if($isLow)<div class="muted-line low-stock-text">Low stock</div>@endif
                            </td>
                            <td>{{ number_format($reorder, 4) }}</td>
                            <td>KES {{ number_format($product->cost_price, 2) }}</td>
                            <td>KES {{ number_format($product->selling_price, 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card2 pos-empty-results" data-inventory-empty hidden>No catalog items match this filter.</div>
        </div>

        <div class="inventory-side">
            <div class="card">
                <div class="sec-head"><span class="sec-title">Add Product / Raw Material</span></div>
                <form method="post" action="{{ route('actions.product') }}" class="inventory-form">
                    @csrf
                    <div><div class="lbl">Name</div><input class="inp" name="name" required></div>
                    <div class="two-col">
                        <div><div class="lbl">SKU</div><input class="inp" name="sku"></div>
                        <div><div class="lbl">Barcode</div><input class="inp" name="barcode"></div>
                    </div>
                    <div class="two-col">
                        <div><div class="lbl">Category</div><select class="inp" name="category_id"><option value="">Select category</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></div>
                        <div><div class="lbl">New Category</div><input class="inp" name="new_category" placeholder="Optional"></div>
                    </div>
                    <div class="two-col">
                        <div><div class="lbl">Subcategory</div><input class="inp" name="subcategory"></div>
                        <div><div class="lbl">Type</div><select class="inp" name="product_type">@foreach($typeLabels as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
                    </div>
                    <div class="three-col">
                        <div><div class="lbl">Unit</div><input class="inp" name="unit" value="pcs" required></div>
                        <div><div class="lbl">Cost</div><input class="inp" name="cost_price" type="number" min="0" step="0.01" value="0"></div>
                        <div><div class="lbl">Price</div><input class="inp" name="selling_price" type="number" min="0" step="0.01" value="0"></div>
                    </div>
                    <div><div class="lbl">Reorder Level</div><input class="inp" name="reorder_level" type="number" min="0" step="0.0001" value="0"></div>
                    <div><div class="lbl">Description</div><textarea class="inp" name="description" rows="3"></textarea></div>
                    <label class="check-line"><input type="checkbox" name="is_active" value="1" checked> Active / visible where applicable</label>
                    <button class="btn btn-primary">Save Product</button>
                </form>
            </div>

            <div class="card">
                <div class="sec-head"><span class="sec-title">Recent Stock Movements</span></div>
                <div class="movement-list">
                    @forelse($movements as $movement)
                        <div class="movement-item">
                            <div>
                                <strong>{{ $movement->product->name ?? 'Unknown product' }}</strong>
                                <span>{{ $movement->movement_type }}</span>
                            </div>
                            <em>{{ number_format($movement->quantity, 4) }}</em>
                        </div>
                    @empty
                        <div class="floor-empty">No stock movements yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
