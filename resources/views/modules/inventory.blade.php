@extends('layouts.app', ['title' => 'Inventory'])
@section('content')
<style>
    .inv-shell{display:grid;grid-template-columns:minmax(0,1.6fr) minmax(320px,.9fr);gap:16px}
    .inv-hero{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;margin-bottom:16px}
    .inv-stat{padding:16px 18px;border:1px solid var(--border);border-radius:18px;background:linear-gradient(180deg,rgba(43,37,38,.96),rgba(31,26,27,.98))}
    .inv-stat .k{font-size:11px;color:var(--text3);text-transform:uppercase;letter-spacing:.08em}
    .inv-stat .v{margin-top:8px;font-size:28px;font-weight:800;color:var(--text);font-family:'Space Mono',monospace}
    .inv-stat .h{margin-top:6px;font-size:12px;color:var(--text2)}
    .inv-kpi-warn .v{color:var(--gold)}
    .inv-kpi-bad .v{color:var(--red)}
    .inv-kpi-good .v{color:var(--green)}
    .inv-card{border:1px solid var(--border);border-radius:20px;background:linear-gradient(180deg,rgba(43,37,38,.96),rgba(31,26,27,.98));overflow:hidden}
    .inv-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:16px 18px;border-bottom:1px solid var(--border)}
    .inv-title{font-size:13px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
    .inv-sub{font-size:12px;color:var(--text3);margin-top:4px}
    .inv-pad{padding:16px 18px}
    .inv-tools{display:grid;grid-template-columns:1fr 1fr;gap:14px}
    .inv-list{display:flex;flex-direction:column;gap:10px}
    .inv-row{display:grid;grid-template-columns:minmax(0,1.4fr) .75fr .7fr .8fr;gap:10px;align-items:center;padding:11px 12px;border:1px solid var(--border);border-radius:14px;background:rgba(255,255,255,.025)}
    .inv-row strong{display:block;font-size:13px;color:var(--text)}
    .inv-row span{display:block;font-size:11px;color:var(--text3);margin-top:3px}
    .inv-row .qty{font-family:'Space Mono',monospace;font-size:14px;color:var(--text)}
    .inv-row .val{font-family:'Space Mono',monospace;font-size:13px;color:var(--gold)}
    .inv-tag{display:inline-flex;align-items:center;padding:4px 8px;border-radius:999px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.06em}
    .inv-tag.ok{background:rgba(62,207,142,.12);color:var(--green)}
    .inv-tag.low{background:rgba(249,181,28,.14);color:var(--gold)}
    .inv-tag.neg{background:rgba(248,113,113,.12);color:var(--red)}
    .inv-mini{display:flex;flex-direction:column;gap:10px}
    .inv-mini-item{display:flex;justify-content:space-between;gap:10px;padding:11px 0;border-bottom:1px solid var(--border)}
    .inv-mini-item:last-child{border-bottom:none}
    .inv-mini-item strong{font-size:13px}
    .inv-mini-item span{display:block;font-size:11px;color:var(--text3);margin-top:3px}
    .inv-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .inv-full{grid-column:1 / -1}
    .inv-move-table td,.inv-move-table th{font-size:12px}
    .inv-note{margin-top:12px;padding:12px;border-radius:14px;background:rgba(40,188,238,.08);border:1px solid rgba(40,188,238,.16);font-size:12px;color:var(--text2);line-height:1.5}
    @media (max-width: 1180px){.inv-shell,.inv-tools,.inv-hero{grid-template-columns:1fr 1fr}}
    @media (max-width: 780px){.inv-shell,.inv-tools,.inv-hero,.inv-form-grid{grid-template-columns:1fr}.inv-row{grid-template-columns:1fr 1fr}.inv-row > :nth-child(3),.inv-row > :nth-child(4){margin-top:8px}}
</style>

<div class="inv-hero">
    <div class="inv-stat">
        <div class="k">Tracked SKUs</div>
        <div class="v">{{ $summary['products'] }}</div>
        <div class="h">{{ $summary['raw_materials'] }} raw and {{ $summary['menu_items'] }} menu-linked items</div>
    </div>
    <div class="inv-stat inv-kpi-warn">
        <div class="k">Low Stock</div>
        <div class="v">{{ $summary['low_stock'] }}</div>
        <div class="h">Below or at reorder point</div>
    </div>
    <div class="inv-stat inv-kpi-bad">
        <div class="k">Negative Stock</div>
        <div class="v">{{ $summary['negative_stock'] }}</div>
        <div class="h">Needs correction before the next service</div>
    </div>
    <div class="inv-stat inv-kpi-good">
        <div class="k">Stock Value</div>
        <div class="v">KES {{ number_format($summary['stock_value'], 0) }}</div>
        <div class="h">Cost basis from current on-hand</div>
    </div>
    <div class="inv-stat">
        <div class="k">Adjustments Logged</div>
        <div class="v">{{ $summary['adjustments'] }}</div>
        <div class="h">Cycle counts, corrections, and transfers</div>
    </div>
</div>

<div class="inv-shell">
    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="inv-card">
            <div class="inv-head">
                <div>
                    <div class="inv-title">Stock Register</div>
                    <div class="inv-sub">Live on-hand, reorder point, and stock value for every tracked product.</div>
                </div>
                <div class="badge b-blue">{{ $products->count() }} products</div>
            </div>
            <div class="inv-pad">
                <div class="inv-list">
                    @foreach($products as $product)
                        @php
                            $qty = (float) ($product->stock_qty ?? 0);
                            $reorder = (float) ($product->reorder_level ?? 0);
                            $state = $qty < 0 ? 'neg' : ($reorder > 0 && $qty <= $reorder ? 'low' : 'ok');
                        @endphp
                        <div class="inv-row">
                            <div>
                                <strong>{{ $product->name }}</strong>
                                <span>{{ $product->sku ?: 'No SKU' }} · {{ $product->category->name ?? 'Uncategorised' }} · {{ str_replace('_', ' ', $product->product_type) }}</span>
                            </div>
                            <div>
                                <span>On Hand</span>
                                <div class="qty">{{ number_format($qty, 2) }} {{ $product->unit }}</div>
                            </div>
                            <div>
                                <span>Reorder</span>
                                <div class="qty">{{ number_format($reorder, 2) }}</div>
                            </div>
                            <div>
                                <div class="val">KES {{ number_format($qty * (float) ($product->cost_price ?? 0), 2) }}</div>
                                <span class="inv-tag {{ $state }}">{{ $state === 'ok' ? 'Healthy' : ($state === 'low' ? 'Reorder' : 'Negative') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="inv-card">
            <div class="inv-head">
                <div>
                    <div class="inv-title">Movement Ledger</div>
                    <div class="inv-sub">Recent stock in, consumption, wastage, return restocks, and manual corrections.</div>
                </div>
            </div>
            <div class="inv-pad">
                <table class="inv-move-table">
                    <thead>
                        <tr>
                            <th>When</th>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movements as $movement)
                            <tr>
                                <td>{{ $movement->created_at?->format('d M H:i') }}</td>
                                <td>{{ $movement->product->name ?? '-' }}</td>
                                <td><span class="badge {{ $movement->quantity >= 0 ? 'b-blue' : 'b-red' }}">{{ $movement->movement_type }}</span></td>
                                <td style="font-family:'Space Mono',monospace;color:{{ $movement->quantity >= 0 ? 'var(--green)' : 'var(--red)' }}">{{ $movement->quantity >= 0 ? '+' : '' }}{{ number_format($movement->quantity, 4) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" style="color:var(--text3)">No stock movements yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="inv-card">
            <div class="inv-head">
                <div>
                    <div class="inv-title">Attention Queue</div>
                    <div class="inv-sub">What the storekeeper should look at first.</div>
                </div>
            </div>
            <div class="inv-pad">
                <div class="inv-mini">
                    @forelse($negativeStock as $product)
                        <div class="inv-mini-item">
                            <div>
                                <strong>{{ $product->name }}</strong>
                                <span>Negative balance · investigate sales, returns, and counts</span>
                            </div>
                            <div class="inv-tag neg">{{ number_format((float) $product->stock_qty, 2) }} {{ $product->unit }}</div>
                        </div>
                    @empty
                        @forelse($lowStock as $product)
                            <div class="inv-mini-item">
                                <div>
                                    <strong>{{ $product->name }}</strong>
                                    <span>Reorder at {{ number_format((float) $product->reorder_level, 2) }} {{ $product->unit }}</span>
                                </div>
                                <div class="inv-tag low">{{ number_format((float) $product->stock_qty, 2) }} {{ $product->unit }}</div>
                            </div>
                        @empty
                            <div style="color:var(--text3);font-size:12px">No low or negative stock items right now.</div>
                        @endforelse
                    @endforelse
                </div>
            </div>
        </div>

        <div class="inv-card">
            <div class="inv-head">
                <div>
                    <div class="inv-title">Cycle Count / Adjustment</div>
                    <div class="inv-sub">Count what is physically there and let the system post only the variance.</div>
                </div>
            </div>
            <div class="inv-pad">
                <form method="post" action="{{ route('actions.stock-adjustment') }}">
                    @csrf
                    <div class="inv-form-grid">
                        <div class="inv-full">
                            <div class="lbl">Product</div>
                            <select class="inp" name="product_id" required>
                                <option value="">Choose product</option>
                                @foreach($products->whereIn('product_type', ['raw_material', 'resale_item', 'semi_finished']) as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} · {{ number_format((float) ($product->stock_qty ?? 0), 2) }} {{ $product->unit }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <div class="lbl">Counted Quantity</div>
                            <input class="inp" type="number" step="0.0001" min="0" name="counted_qty" required>
                        </div>
                        <div>
                            <div class="lbl">Reason</div>
                            <select class="inp" name="reason" required>
                                <option value="cycle_count">Cycle Count</option>
                                <option value="variance">Variance Correction</option>
                                <option value="damage">Damage</option>
                                <option value="opening_balance">Opening Balance</option>
                                <option value="transfer_in">Transfer In</option>
                                <option value="transfer_out">Transfer Out</option>
                                <option value="production_correction">Production Correction</option>
                            </select>
                        </div>
                        <div class="inv-full">
                            <div class="lbl">Notes</div>
                            <textarea class="inp" name="notes" rows="3" placeholder="Why the count changed, who counted it, and anything the next shift should know."></textarea>
                        </div>
                    </div>
                    <button class="btn btn-primary">Post Stock Adjustment</button>
                </form>
                <div class="inv-note">
                    This posts only the difference between the current system balance and the physical count, so your movement ledger stays clean and auditable.
                </div>
            </div>
        </div>

        <div class="inv-card">
            <div class="inv-head">
                <div>
                    <div class="inv-title">Recent Adjustments</div>
                    <div class="inv-sub">Latest manual stock corrections and counts.</div>
                </div>
            </div>
            <div class="inv-pad">
                <div class="inv-mini">
                    @forelse($adjustments as $adjustment)
                        <div class="inv-mini-item">
                            <div>
                                <strong>{{ $adjustment->product->name ?? '-' }}</strong>
                                <span>{{ str_replace('_', ' ', $adjustment->reason) }} · {{ $adjustment->actor?->name ?? 'System' }} · {{ $adjustment->created_at?->format('d M H:i') }}</span>
                            </div>
                            <div class="inv-tag {{ $adjustment->variance_qty >= 0 ? 'ok' : 'neg' }}">{{ $adjustment->variance_qty >= 0 ? '+' : '' }}{{ number_format($adjustment->variance_qty, 2) }}</div>
                        </div>
                    @empty
                        <div style="color:var(--text3);font-size:12px">No stock adjustments recorded yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="inv-card">
            <div class="inv-head">
                <div>
                    <div class="inv-title">Quick Product Add</div>
                    <div class="inv-sub">Fast lane for adding new raw materials, resale items, or semi-finished stock.</div>
                </div>
            </div>
            <div class="inv-pad">
                <form method="post" action="{{ route('actions.product') }}">
                    @csrf
                    <div class="inv-form-grid">
                        <div class="inv-full"><div class="lbl">Product Name</div><input class="inp" name="name" required></div>
                        <div><div class="lbl">SKU</div><input class="inp" name="sku"></div>
                        <div><div class="lbl">Barcode</div><input class="inp" name="barcode"></div>
                        <div><div class="lbl">Type</div><select class="inp" name="product_type"><option value="raw_material">Raw Material</option><option value="resale_item">Resale Item</option><option value="semi_finished">Semi-Finished</option><option value="finished_product">Finished Product</option></select></div>
                        <div><div class="lbl">Unit</div><input class="inp" name="unit" value="pcs" required></div>
                        <div><div class="lbl">Reorder Level</div><input class="inp" type="number" step="0.01" name="reorder_level"></div>
                        <div><div class="lbl">Cost Price</div><input class="inp" type="number" step="0.01" name="cost_price"></div>
                        <div><div class="lbl">Selling Price</div><input class="inp" type="number" step="0.01" name="selling_price"></div>
                        <div><div class="lbl">Category</div><select class="inp" name="category_id"><option value="">Select category</option>@foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach</select></div>
                        <div><div class="lbl">New Category</div><input class="inp" name="new_category" placeholder="Create if needed"></div>
                        <div class="inv-full"><div class="lbl">Description</div><textarea class="inp" name="description" rows="3"></textarea></div>
                    </div>
                    <button class="btn btn-primary">Save Product</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
