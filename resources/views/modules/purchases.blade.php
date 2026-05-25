@extends('layouts.app', ['title' => 'Purchases'])
@section('content')
<style>
    .pur-hero{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:12px;margin-bottom:16px}
    .pur-stat,.pur-card{border:1px solid var(--border);border-radius:20px;background:linear-gradient(180deg,rgba(43,37,38,.96),rgba(31,26,27,.98))}
    .pur-stat{padding:16px 18px}
    .pur-k{font-size:11px;color:var(--text3);text-transform:uppercase;letter-spacing:.08em}
    .pur-v{margin-top:8px;font-size:26px;font-weight:800;color:var(--text);font-family:'Space Mono',monospace}
    .pur-h{margin-top:6px;font-size:12px;color:var(--text2)}
    .pur-shell{display:grid;grid-template-columns:minmax(0,1.45fr) minmax(320px,.95fr);gap:16px}
    .pur-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:16px 18px;border-bottom:1px solid var(--border)}
    .pur-title{font-size:13px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
    .pur-sub{font-size:12px;color:var(--text3);margin-top:4px}
    .pur-pad{padding:16px 18px}
    .pur-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .pur-full{grid-column:1 / -1}
    .pur-lines{display:flex;flex-direction:column;gap:10px}
    .pur-line{display:grid;grid-template-columns:minmax(0,1.35fr) .6fr .6fr;gap:10px;align-items:end}
    .pur-mini{display:flex;flex-direction:column;gap:10px}
    .pur-mini-item{display:flex;justify-content:space-between;gap:10px;padding:11px 0;border-bottom:1px solid var(--border)}
    .pur-mini-item:last-child{border-bottom:none}
    .pur-mini-item strong{font-size:13px}
    .pur-mini-item span{display:block;font-size:11px;color:var(--text3);margin-top:3px}
    .pur-badge{display:inline-flex;align-items:center;padding:4px 8px;border-radius:999px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.06em}
    .pur-badge.blue{background:rgba(40,188,238,.12);color:var(--blue)}
    .pur-badge.gold{background:rgba(249,181,28,.14);color:var(--gold)}
    .pur-badge.green{background:rgba(62,207,142,.12);color:var(--green)}
    .pur-table td,.pur-table th{font-size:12px}
    .pur-note{margin-top:12px;padding:12px;border-radius:14px;background:rgba(249,181,28,.08);border:1px solid rgba(249,181,28,.16);font-size:12px;color:var(--text2);line-height:1.5}
    @media (max-width: 1180px){.pur-hero{grid-template-columns:repeat(3,1fr)}.pur-shell{grid-template-columns:1fr}}
    @media (max-width: 760px){.pur-hero,.pur-grid,.pur-line{grid-template-columns:1fr}}
</style>

<div class="pur-hero">
    <div class="pur-stat">
        <div class="pur-k">GRNs Posted</div>
        <div class="pur-v">{{ $summary['purchases'] }}</div>
        <div class="pur-h">Total receiving records</div>
    </div>
    <div class="pur-stat">
        <div class="pur-k">Receiving Value</div>
        <div class="pur-v">KES {{ number_format($summary['value'], 0) }}</div>
        <div class="pur-h">All posted purchases</div>
    </div>
    <div class="pur-stat">
        <div class="pur-k">This Month</div>
        <div class="pur-v">KES {{ number_format($summary['month_value'], 0) }}</div>
        <div class="pur-h">Current month stock-in</div>
    </div>
    <div class="pur-stat">
        <div class="pur-k">Suppliers</div>
        <div class="pur-v">{{ $summary['suppliers'] }}</div>
        <div class="pur-h">Known vendors on file</div>
    </div>
    <div class="pur-stat">
        <div class="pur-k">Items Received</div>
        <div class="pur-v">{{ $summary['items'] }}</div>
        <div class="pur-h">Posted purchase lines</div>
    </div>
    <div class="pur-stat">
        <div class="pur-k">Average GRN</div>
        <div class="pur-v">KES {{ number_format($summary['avg_receipt'], 0) }}</div>
        <div class="pur-h">Average receiving ticket</div>
    </div>
</div>

<div class="pur-shell">
    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="pur-card">
            <div class="pur-head">
                <div>
                    <div class="pur-title">Receive Stock</div>
                    <div class="pur-sub">Post incoming supplier stock and move it straight into inventory.</div>
                </div>
                <span class="pur-badge gold">Stock In</span>
            </div>
            <div class="pur-pad">
                <form method="post" action="{{ route('actions.purchase') }}">
                    @csrf
                    <div class="pur-grid">
                        <div>
                            <div class="lbl">Existing Supplier</div>
                            <select class="inp" name="supplier_id">
                                <option value="">Choose supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <div class="lbl">Or New Supplier Name</div>
                            <input class="inp" name="supplier_name" placeholder="Type supplier name">
                        </div>
                        <div>
                            <div class="lbl">Supplier Phone</div>
                            <input class="inp" name="supplier_phone">
                        </div>
                        <div>
                            <div class="lbl">Supplier Email</div>
                            <input class="inp" type="email" name="supplier_email">
                        </div>
                        <div class="pur-full">
                            <div class="lbl">Notes</div>
                            <textarea class="inp" name="notes" rows="3" placeholder="Invoice number, delivery note, or receiving remarks."></textarea>
                        </div>
                    </div>

                    <div class="sec-head" style="margin:18px 0 12px"><span class="sec-title">Lines</span></div>
                    <div class="pur-lines">
                        @for($i = 0; $i < 5; $i++)
                            <div class="pur-line">
                                <div>
                                    <div class="lbl">Product</div>
                                    <select class="inp" name="product_id[]">
                                        <option value="">Choose product</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }} · {{ $product->unit }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <div class="lbl">Qty</div>
                                    <input class="inp" type="number" step="0.0001" min="0" name="quantity[]">
                                </div>
                                <div>
                                    <div class="lbl">Unit Cost</div>
                                    <input class="inp" type="number" step="0.01" min="0" name="unit_cost[]">
                                </div>
                            </div>
                        @endfor
                    </div>

                    <button class="btn btn-primary" style="margin-top:14px">Post Purchase</button>
                </form>
                <div class="pur-note">
                    Every posted purchase creates stock movement rows immediately, so receiving history and inventory balances stay in sync.
                </div>
            </div>
        </div>

        <div class="pur-card">
            <div class="pur-head">
                <div>
                    <div class="pur-title">Recent Purchase Register</div>
                    <div class="pur-sub">Latest goods received notes and the lines that came with them.</div>
                </div>
            </div>
            <div class="pur-pad">
                <table class="pur-table">
                    <thead>
                        <tr>
                            <th>Purchase</th>
                            <th>Supplier</th>
                            <th>Value</th>
                            <th>Lines</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases->take(18) as $purchase)
                            <tr>
                                <td>
                                    <strong>{{ $purchase->purchase_number }}</strong>
                                    <div style="color:var(--text3);font-size:11px">{{ $purchase->created_at?->format('d M Y H:i') }}</div>
                                </td>
                                <td>{{ $purchase->supplier?->name ?? 'Walk-in supplier' }}</td>
                                <td>KES {{ number_format($purchase->total_amount, 2) }}</td>
                                <td>{{ $purchase->items->count() }}</td>
                            </tr>
                            @if($purchase->items->isNotEmpty())
                                <tr>
                                    <td colspan="4" style="color:var(--text3)">
                                        @foreach($purchase->items as $item)
                                            <div>{{ number_format($item->quantity, 2) }} × {{ $item->product->name ?? '-' }} @ KES {{ number_format($item->unit_cost, 2) }}</div>
                                        @endforeach
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr><td colspan="4" style="color:var(--text3)">No purchases posted yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="pur-card">
            <div class="pur-head">
                <div>
                    <div class="pur-title">Supplier Quick Add</div>
                    <div class="pur-sub">Keep supplier contacts clean before the next ordering cycle.</div>
                </div>
            </div>
            <div class="pur-pad">
                <form method="post" action="{{ route('actions.supplier') }}">
                    @csrf
                    <div class="pur-grid">
                        <div class="pur-full"><div class="lbl">Supplier Name</div><input class="inp" name="name" required></div>
                        <div><div class="lbl">Phone</div><input class="inp" name="phone"></div>
                        <div><div class="lbl">Email</div><input class="inp" type="email" name="email"></div>
                    </div>
                    <button class="btn btn-primary">Save Supplier</button>
                </form>
            </div>
        </div>

        <div class="pur-card">
            <div class="pur-head">
                <div>
                    <div class="pur-title">Top Suppliers</div>
                    <div class="pur-sub">Who is carrying most of your receiving value right now.</div>
                </div>
            </div>
            <div class="pur-pad">
                <div class="pur-mini">
                    @forelse($topSuppliers as $entry)
                        <div class="pur-mini-item">
                            <div>
                                <strong>{{ $entry->supplier->name }}</strong>
                                <span>{{ $entry->purchase_count }} purchases · last {{ \Illuminate\Support\Carbon::parse($entry->last_purchase_at)->format('d M Y') }}</span>
                            </div>
                            <div class="pur-badge blue">KES {{ number_format($entry->total_amount, 0) }}</div>
                        </div>
                    @empty
                        <div style="color:var(--text3);font-size:12px">No supplier activity yet.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="pur-card">
            <div class="pur-head">
                <div>
                    <div class="pur-title">Receiving Ledger</div>
                    <div class="pur-sub">Latest stock movements created by purchases.</div>
                </div>
            </div>
            <div class="pur-pad">
                <div class="pur-mini">
                    @forelse($movements as $movement)
                        <div class="pur-mini-item">
                            <div>
                                <strong>{{ $movement->product->name ?? '-' }}</strong>
                                <span>{{ $movement->movement_type }} · {{ $movement->created_at?->format('d M H:i') }}</span>
                            </div>
                            <div class="pur-badge green">+{{ number_format($movement->quantity, 2) }}</div>
                        </div>
                    @empty
                        <div style="color:var(--text3);font-size:12px">No receiving movements yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
