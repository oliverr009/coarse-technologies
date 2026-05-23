@extends('layouts.app', ['title' => 'Orders'])

@php
    $queryStatus = request('status', 'active');
    $queryType = request('type', 'all');
    $querySearch = request('search', '');

    $statusTone = [
        'held' => 'is-hold',
        'sent' => 'is-sent',
    ];

    $typeLabel = [
        'dine_in' => 'Dine In',
        'takeaway' => 'Takeout',
        'delivery' => 'Delivery',
    ];
@endphp

@section('content')
<style>
.orders-board{display:grid;grid-template-columns:minmax(0,1.7fr) 420px;gap:16px;min-height:calc(100vh - 180px)}
.orders-surface,.orders-preview{border:1px solid rgba(255,255,255,.08);border-radius:18px;background:linear-gradient(180deg,rgba(43,37,38,.96),rgba(31,26,27,.98));box-shadow:0 20px 48px rgba(0,0,0,.22)}
.orders-surface{display:flex;flex-direction:column;overflow:hidden}
.orders-preview{padding:18px;display:flex;flex-direction:column;gap:14px}
.orders-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;padding:18px 18px 12px;border-bottom:1px solid rgba(255,255,255,.08)}
.orders-kicker{font-size:11px;font-weight:800;letter-spacing:.16em;text-transform:uppercase;color:#f9b51c;margin-bottom:6px}
.orders-title{font-size:24px;font-weight:800;color:#f6f2f1;line-height:1.1}
.orders-copy{font-size:12px;color:#9d9092;max-width:520px;line-height:1.5;margin-top:6px}
.orders-statbar{display:flex;gap:8px;flex-wrap:wrap}
.orders-stat{min-width:88px;padding:10px 12px;border-radius:14px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.03)}
.orders-stat span{display:block;font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#8e8184;margin-bottom:4px}
.orders-stat strong{font-size:18px;color:#f6f2f1}
.orders-filters{display:grid;grid-template-columns:minmax(0,1.15fr) 140px repeat(4,auto) auto auto;gap:8px;padding:14px 18px;border-bottom:1px solid rgba(255,255,255,.08)}
.orders-search,.orders-select{height:42px;border-radius:12px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.04);color:#f6f2f1;padding:0 14px;font:inherit}
.orders-search:focus,.orders-select:focus{outline:none;border-color:#f9b51c;box-shadow:0 0 0 3px rgba(249,181,28,.1)}
.orders-pill{height:42px;padding:0 16px;border-radius:12px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.03);color:#c7bfc0;font-size:13px;font-weight:700;display:inline-flex;align-items:center;justify-content:center}
.orders-pill.active{background:rgba(249,181,28,.14);border-color:rgba(249,181,28,.26);color:#f9b51c}
.orders-submit,.orders-reset{height:42px;padding:0 16px;border-radius:12px;font-size:13px;font-weight:800;display:inline-flex;align-items:center;justify-content:center;text-decoration:none}
.orders-submit{border:none;background:linear-gradient(135deg,#f9b51c,#d79a39);color:#1f1a1b}
.orders-reset{border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.03);color:#c7bfc0}
.orders-list{display:flex;flex-direction:column;overflow:auto}
.order-row{display:grid;grid-template-columns:140px minmax(0,1.1fr) 120px 120px 84px;gap:14px;align-items:center;padding:16px 18px;border-bottom:1px solid rgba(255,255,255,.06);color:#f6f2f1;text-decoration:none;transition:background .16s ease,border-color .16s ease}
.order-row:hover{background:rgba(255,255,255,.035)}
.order-row.active{background:rgba(40,188,238,.12)}
.order-time strong,.order-main strong,.order-total strong{display:block;font-size:14px;font-weight:800;color:#f6f2f1}
.order-time span,.order-main span,.order-total span{display:block;font-size:11px;color:#9d9092;margin-top:3px}
.order-tags{display:flex;gap:6px;flex-wrap:wrap;margin-top:7px}
.order-tag{display:inline-flex;align-items:center;padding:4px 8px;border-radius:999px;font-size:10px;font-weight:800}
.tag-type{background:rgba(40,188,238,.12);color:#28bcee}
.tag-table{background:rgba(248,113,113,.1);color:#fca5a5}
.tag-customer{background:rgba(62,207,142,.1);color:#3ecf8e}
.order-status{justify-self:start;display:inline-flex;align-items:center;padding:5px 10px;border-radius:999px;font-size:11px;font-weight:800}
.order-status.is-hold{background:rgba(249,181,28,.14);color:#f9b51c}
.order-status.is-sent{background:rgba(40,188,238,.12);color:#28bcee}
.order-link{justify-self:end;color:#8e8184;font-size:18px}
.preview-card{padding:16px;border-radius:16px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.03)}
.preview-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:14px}
.preview-order{font-size:22px;font-weight:800;color:#f6f2f1;line-height:1.1}
.preview-sub{font-size:12px;color:#9d9092;margin-top:5px}
.preview-badges{display:flex;gap:6px;flex-wrap:wrap}
.preview-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px}
.preview-meta{padding:12px;border-radius:14px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06)}
.preview-meta span{display:block;font-size:10px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#8e8184;margin-bottom:5px}
.preview-meta strong{display:block;font-size:13px;color:#f6f2f1;line-height:1.35}
.preview-items{display:flex;flex-direction:column;gap:8px;max-height:320px;overflow:auto}
.preview-item{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.06)}
.preview-item:last-child{border-bottom:none}
.preview-item strong{display:block;font-size:13px;color:#f6f2f1}
.preview-item span{display:block;font-size:11px;color:#9d9092;margin-top:3px;line-height:1.4}
.preview-item em{font-style:normal;font-size:13px;font-weight:800;color:#f9b51c;white-space:nowrap}
.preview-note{padding:12px;border-radius:14px;background:rgba(255,255,255,.03);border:1px dashed rgba(255,255,255,.12);font-size:12px;color:#c7bfc0;line-height:1.55}
.preview-total{display:flex;align-items:center;justify-content:space-between;padding:16px;border-radius:16px;background:linear-gradient(135deg,rgba(249,181,28,.14),rgba(40,188,238,.08));border:1px solid rgba(249,181,28,.2)}
.preview-total span{font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#8e8184}
.preview-total strong{font-size:28px;color:#f6f2f1;letter-spacing:0}
.preview-actions{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.preview-action,.preview-link{display:inline-flex;align-items:center;justify-content:center;height:50px;border-radius:14px;font-size:14px;font-weight:800;text-decoration:none}
.preview-link{background:linear-gradient(135deg,#f9b51c,#d79a39);color:#1f1a1b}
.preview-action{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);color:#c7bfc0}
.preview-empty{flex:1;display:grid;place-items:center;text-align:center;color:#8e8184;padding:30px}
.preview-empty strong{display:block;font-size:18px;color:#f6f2f1;margin-top:10px}
.preview-empty span{display:block;font-size:12px;line-height:1.5;margin-top:6px}
.empty-orders{padding:48px 18px;text-align:center;color:#8e8184}
.empty-orders strong{display:block;font-size:18px;color:#f6f2f1;margin-bottom:8px}
html[data-theme=light] .orders-surface,html[data-theme=light] .orders-preview{border-color:rgba(27,33,83,.12);background:linear-gradient(180deg,rgba(255,255,255,.96),rgba(245,248,252,.98));box-shadow:0 18px 46px rgba(27,33,83,.09)}
html[data-theme=light] .orders-head,html[data-theme=light] .orders-filters,html[data-theme=light] .order-row,html[data-theme=light] .preview-item{border-color:rgba(27,33,83,.08)}
html[data-theme=light] .orders-title,html[data-theme=light] .orders-stat strong,html[data-theme=light] .order-time strong,html[data-theme=light] .order-main strong,html[data-theme=light] .order-total strong,html[data-theme=light] .preview-order,html[data-theme=light] .preview-meta strong,html[data-theme=light] .preview-item strong,html[data-theme=light] .preview-total strong,html[data-theme=light] .preview-empty strong,html[data-theme=light] .empty-orders strong{color:#172047}
html[data-theme=light] .orders-copy,html[data-theme=light] .order-time span,html[data-theme=light] .order-main span,html[data-theme=light] .order-total span,html[data-theme=light] .preview-sub,html[data-theme=light] .preview-item span,html[data-theme=light] .preview-note,html[data-theme=light] .preview-empty,html[data-theme=light] .empty-orders{color:#64748b}
html[data-theme=light] .orders-stat,html[data-theme=light] .preview-card,html[data-theme=light] .preview-meta,html[data-theme=light] .preview-note,html[data-theme=light] .preview-action,html[data-theme=light] .orders-reset,html[data-theme=light] .orders-pill{border-color:rgba(27,33,83,.10);background:rgba(27,33,83,.035);color:#475569}
html[data-theme=light] .orders-search,html[data-theme=light] .orders-select{border-color:rgba(27,33,83,.12);background:#fff;color:#172047}
html[data-theme=light] .orders-select option{background:#fff;color:#172047}
html[data-theme=light] .order-row{color:#172047}
html[data-theme=light] .order-row:hover{background:rgba(40,188,238,.07)}
html[data-theme=light] .order-row.active{background:rgba(40,188,238,.13)}
html[data-theme=light] .order-link,html[data-theme=light] .orders-stat span,html[data-theme=light] .preview-meta span,html[data-theme=light] .preview-total span{color:#748094}
html[data-theme=light] .preview-total{background:linear-gradient(135deg,rgba(249,181,28,.16),rgba(40,188,238,.10));border-color:rgba(249,181,28,.24)}
@media(max-width:1180px){.orders-board{grid-template-columns:1fr}.orders-preview{order:-1}}
@media(max-width:960px){.orders-filters{grid-template-columns:1fr 1fr}.order-row{grid-template-columns:1fr;gap:10px}.order-link{display:none}.preview-grid,.preview-actions{grid-template-columns:1fr}}
</style>

<div class="orders-board">
    <section class="orders-surface">
        <div class="orders-head">
            <div>
                <div class="orders-kicker">Order Control</div>
                <div class="orders-title">Open Bills & Pending Orders</div>
                <div class="orders-copy">Find live tables fast, preview what is already on the bill, then load the same order back into the sell screen without duplicating it.</div>
            </div>
            <div class="orders-statbar">
                <div class="orders-stat"><span>Active</span><strong>{{ $counts['all'] }}</strong></div>
                <div class="orders-stat"><span>Dine In</span><strong>{{ $counts['dine_in'] }}</strong></div>
                <div class="orders-stat"><span>Takeout</span><strong>{{ $counts['takeaway'] }}</strong></div>
                <div class="orders-stat"><span>Delivery</span><strong>{{ $counts['delivery'] }}</strong></div>
            </div>
        </div>

        <form class="orders-filters" method="get" action="{{ route('orders') }}">
            <input class="orders-search" type="search" name="search" value="{{ $querySearch }}" placeholder="Search order number, table, customer, or note">
            <select class="orders-select" name="status">
                <option value="active" {{ $queryStatus === 'active' ? 'selected' : '' }}>Active</option>
                <option value="sent" {{ $queryStatus === 'sent' ? 'selected' : '' }}>In Kitchen</option>
                <option value="held" {{ $queryStatus === 'held' ? 'selected' : '' }}>On Hold</option>
            </select>
            <input type="hidden" name="type" value="{{ $queryType }}">
            <a class="orders-pill {{ $queryType === 'all' ? 'active' : '' }}" href="{{ route('orders', ['type' => 'all', 'status' => $queryStatus !== 'active' ? $queryStatus : null, 'search' => $querySearch ?: null]) }}">All</a>
            <a class="orders-pill {{ $queryType === 'dine_in' ? 'active' : '' }}" href="{{ route('orders', ['type' => 'dine_in', 'status' => $queryStatus !== 'active' ? $queryStatus : null, 'search' => $querySearch ?: null]) }}">Dine In</a>
            <a class="orders-pill {{ $queryType === 'takeaway' ? 'active' : '' }}" href="{{ route('orders', ['type' => 'takeaway', 'status' => $queryStatus !== 'active' ? $queryStatus : null, 'search' => $querySearch ?: null]) }}">Takeout</a>
            <a class="orders-pill {{ $queryType === 'delivery' ? 'active' : '' }}" href="{{ route('orders', ['type' => 'delivery', 'status' => $queryStatus !== 'active' ? $queryStatus : null, 'search' => $querySearch ?: null]) }}">Delivery</a>
            <button class="orders-submit" type="submit">Apply</button>
            <a class="orders-reset" href="{{ route('orders') }}">Reset</a>
        </form>

        <div class="orders-list">
            @forelse($orders as $order)
                @php
                    $params = array_filter([
                        'preview' => $order->id,
                        'search' => $querySearch,
                        'status' => $queryStatus !== 'active' ? $queryStatus : null,
                        'type' => $queryType !== 'all' ? $queryType : null,
                    ], fn ($value) => filled($value));
                @endphp
                <a class="order-row {{ $selected && $selected->id === $order->id ? 'active' : '' }}" href="{{ route('orders', $params) }}">
                    <div class="order-time">
                        <strong>{{ $order->created_at?->isToday() ? 'Today' : $order->created_at?->format('d M') }}</strong>
                        <span>{{ $order->created_at?->format('g:ia') }}</span>
                    </div>
                    <div class="order-main">
                        <strong>{{ $order->order_number }}</strong>
                        <span>{{ $order->customer?->name ?: 'Walk-in customer' }}</span>
                        <div class="order-tags">
                            <span class="order-tag tag-type">{{ $typeLabel[$order->order_type] ?? ucfirst($order->order_type) }}</span>
                            @if($order->table?->name)
                                <span class="order-tag tag-table">{{ $order->table->name }}</span>
                            @endif
                            @if($order->covers)
                                <span class="order-tag tag-customer">{{ $order->covers }} guests</span>
                            @endif
                        </div>
                    </div>
                    <div class="order-total">
                        <strong>{{ number_format($order->subtotal, 2) }} KSh</strong>
                        <span>{{ $order->items->sum('quantity') }} items</span>
                    </div>
                    <span class="order-status {{ $statusTone[$order->status] ?? 'is-sent' }}">{{ $order->status === 'sent' ? 'Ongoing' : 'On Hold' }}</span>
                    <span class="order-link"><i class="ti ti-chevron-right"></i></span>
                </a>
            @empty
                <div class="empty-orders">
                    <strong>No active orders found</strong>
                    <div>Once a bill is held or sent to kitchen, it will appear here for quick recall.</div>
                </div>
            @endforelse
        </div>
    </section>

    <aside class="orders-preview">
        @if($selected)
            <div class="preview-card">
                <div class="preview-head">
                    <div>
                        <div class="preview-order">{{ $selected->order_number }}</div>
                        <div class="preview-sub">{{ $selected->customer?->name ?: 'Walk-in customer' }}</div>
                    </div>
                    <div class="preview-badges">
                        <span class="order-tag tag-type">{{ $typeLabel[$selected->order_type] ?? ucfirst($selected->order_type) }}</span>
                        @if($selected->table?->name)
                            <span class="order-tag tag-table">{{ $selected->table->name }}</span>
                        @endif
                    </div>
                </div>

                <div class="preview-grid">
                    <div class="preview-meta">
                        <span>Status</span>
                        <strong>{{ $selected->status === 'sent' ? 'In kitchen / pending' : 'Held and waiting' }}</strong>
                    </div>
                    <div class="preview-meta">
                        <span>Guests</span>
                        <strong>{{ $selected->covers ?: 1 }} covers</strong>
                    </div>
                    <div class="preview-meta">
                        <span>Opened</span>
                        <strong>{{ $selected->created_at?->format('D, d M · g:ia') }}</strong>
                    </div>
                    <div class="preview-meta">
                        <span>Table</span>
                        <strong>{{ $selected->table?->name ?: 'No table assigned' }}</strong>
                    </div>
                </div>

                <div class="preview-items">
                    @foreach($selected->items as $item)
                        <div class="preview-item">
                            <div>
                                <strong>{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }} × {{ $item->product_name }}</strong>
                                <span>
                                    {{ number_format($item->unit_price, 2) }} KSh each
                                    @if($item->notes)
                                        <br>{{ $item->notes }}
                                    @endif
                                </span>
                            </div>
                            <em>{{ number_format($item->line_total, 2) }} KSh</em>
                        </div>
                    @endforeach
                </div>

                @if($selected->notes)
                    <div class="preview-note">{{ $selected->notes }}</div>
                @endif
            </div>

            <div class="preview-total">
                <div>
                    <span>Bill Total</span>
                    <strong>{{ number_format($selected->subtotal, 2) }} KSh</strong>
                </div>
                <div style="text-align:right">
                    <span>Items</span>
                    <strong>{{ $selected->items->sum('quantity') }}</strong>
                </div>
            </div>

            <div class="preview-actions">
                <a class="preview-link" href="{{ route('pos.index', ['order' => $selected->id]) }}">Load Into Sell</a>
                <a class="preview-action" href="{{ route('pos.index') }}">Direct Sale</a>
            </div>
        @else
            <div class="preview-empty">
                <div>
                    <i class="ti ti-receipt-2" style="font-size:46px"></i>
                    <strong>Select an order</strong>
                    <span>Pick any active bill on the left to inspect it here before loading it back into the sell screen.</span>
                </div>
            </div>
        @endif
    </aside>
</div>
@endsection
