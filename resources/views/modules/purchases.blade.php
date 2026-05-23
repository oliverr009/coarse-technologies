@extends('layouts.app', ['title' => 'Purchases & Stock In'])

@section('content')
<div class="purchase-page">
    <div class="stats-row">
        <div class="stat-card"><div class="stat-label">Purchases</div><div class="stat-value sv-blue">{{ $summary['purchases'] }}</div><div class="stat-change">Posted stock-in docs</div></div>
        <div class="stat-card"><div class="stat-label">Stock-In Value</div><div class="stat-value sv-gold">KES {{ number_format($summary['value'], 0) }}</div><div class="stat-change">Total purchase value</div></div>
        <div class="stat-card"><div class="stat-label">Lines</div><div class="stat-value sv-green">{{ $summary['items'] }}</div><div class="stat-change">Purchased items</div></div>
        <div class="stat-card"><div class="stat-label">Suppliers</div><div class="stat-value">{{ $summary['suppliers'] }}</div><div class="stat-change">Vendor accounts</div></div>
    </div>

    <div class="grid-65 purchase-workspace">
        <div class="card">
            <div class="sec-head">
                <span class="sec-title">Post Purchase / Stock In</span>
                <span class="badge b-blue" data-purchase-total>KES 0.00</span>
            </div>

            <form method="post" action="{{ route('actions.purchase') }}" data-purchase-form>
                @csrf
                <div class="purchase-header">
                    <div>
                        <div class="lbl">Supplier</div>
                        <select class="inp" name="supplier_id">
                            <option value="">Select supplier or add new</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <div class="lbl">New Supplier</div>
                        <input class="inp" name="supplier_name" placeholder="Optional">
                    </div>
                    <div>
                        <div class="lbl">Phone</div>
                        <input class="inp" name="supplier_phone" placeholder="Optional">
                    </div>
                    <div>
                        <div class="lbl">Email</div>
                        <input class="inp" name="supplier_email" type="email" placeholder="Optional">
                    </div>
                    <div class="purchase-notes">
                        <div class="lbl">Notes</div>
                        <input class="inp" name="notes" placeholder="Invoice number, delivery note, remarks">
                    </div>
                </div>

                <div class="purchase-lines">
                    <div class="purchase-line purchase-line-head">
                        <span>Product</span>
                        <span>Qty</span>
                        <span>Unit Cost</span>
                        <span>Line Total</span>
                    </div>
                    @for($i = 0; $i < 8; $i++)
                        <div class="purchase-line" data-purchase-line>
                            <select class="inp" name="product_id[]">
                                <option value="">Select product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" data-cost="{{ $product->cost_price }}" data-unit="{{ $product->unit }}">{{ $product->name }} · {{ $product->unit }}</option>
                                @endforeach
                            </select>
                            <input class="inp" name="quantity[]" type="number" min="0" step="0.0001" placeholder="0">
                            <input class="inp" name="unit_cost[]" type="number" min="0" step="0.01" placeholder="0.00">
                            <strong data-line-total>KES 0.00</strong>
                        </div>
                    @endfor
                </div>

                <div class="purchase-footer">
                    <div class="checkout-status" data-purchase-status>Add at least one stock-in line.</div>
                    <button class="btn btn-primary" data-purchase-submit disabled>Post Purchase</button>
                </div>
            </form>
        </div>

        <div class="purchase-side">
            <div class="card">
                <div class="sec-head"><span class="sec-title">Recent Purchase Movements</span></div>
                <div class="movement-list">
                    @forelse($movements as $movement)
                        <div class="movement-item">
                            <div>
                                <strong>{{ $movement->product->name ?? 'Unknown product' }}</strong>
                                <span>{{ $movement->created_at->format('d M H:i') }} · after {{ number_format($movement->after_stock, 4) }}</span>
                            </div>
                            <em>+{{ number_format($movement->quantity, 4) }}</em>
                        </div>
                    @empty
                        <div class="floor-empty">No purchase stock movements yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="sec-head"><span class="sec-title">Recent Purchases</span></div>
        <div class="tbl-wrap">
            <table>
                <thead><tr><th>No</th><th>Supplier</th><th>Items</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                @forelse($purchases as $purchase)
                    <tr>
                        <td><strong>{{ $purchase->purchase_number }}</strong></td>
                        <td>{{ $purchase->supplier->name ?? 'Unassigned' }}</td>
                        <td>
                            @foreach($purchase->items->take(2) as $item)
                                <div class="muted-line">{{ number_format($item->quantity, 4) }} x {{ $item->product->name ?? 'Item' }}</div>
                            @endforeach
                            @if($purchase->items->count() > 2)<div class="muted-line">+ {{ $purchase->items->count() - 2 }} more</div>@endif
                        </td>
                        <td>KES {{ number_format($purchase->total_amount, 2) }}</td>
                        <td><span class="badge b-green">{{ $purchase->status }}</span></td>
                        <td>{{ $purchase->created_at->format('d M H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6">No purchases posted yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
