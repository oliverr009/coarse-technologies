@extends('layouts.app', ['title' => 'Kitchen Display'])

@php
    $columns = [
        'pending' => ['title' => 'Pending', 'copy' => 'Freshly fired items waiting for a station.', 'badge' => 'blue'],
        'preparing' => ['title' => 'Preparing', 'copy' => 'Items actively being worked by the kitchen or bar.', 'badge' => 'gold'],
        'ready' => ['title' => 'Ready', 'copy' => 'Items finished and ready for pickup or service.', 'badge' => 'green'],
    ];

    $typeLabel = [
        'dine_in' => 'Dine In',
        'takeaway' => 'Takeout',
        'delivery' => 'Delivery',
    ];
@endphp

@section('content')
<style>
.kds-hero{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:16px}
.kds-stat,.kds-column{border:1px solid var(--border);border-radius:20px;background:linear-gradient(180deg,rgba(43,37,38,.96),rgba(31,26,27,.98))}
.kds-stat{padding:16px 18px}
.kds-k{font-size:11px;color:var(--text3);text-transform:uppercase;letter-spacing:.08em}
.kds-v{margin-top:8px;font-size:26px;font-weight:800;color:var(--text);font-family:'Space Mono',monospace}
.kds-h{margin-top:6px;font-size:12px;color:var(--text2)}
.kds-board{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;align-items:start}
.kds-column{overflow:hidden}
.kds-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:16px 18px;border-bottom:1px solid var(--border)}
.kds-title{font-size:13px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
.kds-sub{font-size:12px;color:var(--text3);margin-top:4px;line-height:1.5}
.kds-pad{padding:16px 18px}
.kds-badge{display:inline-flex;align-items:center;padding:4px 8px;border-radius:999px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.06em}
.kds-badge.blue{background:rgba(40,188,238,.12);color:var(--blue)}
.kds-badge.gold{background:rgba(249,181,28,.14);color:var(--gold)}
.kds-badge.green{background:rgba(62,207,142,.12);color:var(--green)}
.kds-stack{display:flex;flex-direction:column;gap:12px;max-height:calc(100vh - 280px);overflow:auto}
.kds-ticket{padding:14px;border:1px solid var(--border);border-radius:16px;background:rgba(255,255,255,.025)}
.kds-ticket-top{display:flex;align-items:flex-start;justify-content:space-between;gap:10px}
.kds-ticket h4{margin:0;font-size:15px;color:var(--text)}
.kds-ticket p{margin:4px 0 0;font-size:11px;color:var(--text3)}
.kds-tags{display:flex;gap:6px;flex-wrap:wrap;margin-top:10px}
.kds-tag{display:inline-flex;align-items:center;padding:4px 8px;border-radius:999px;font-size:10px;font-weight:800}
.kds-tag.type{background:rgba(40,188,238,.12);color:var(--blue)}
.kds-tag.table{background:rgba(248,113,113,.1);color:#fca5a5}
.kds-tag.note{background:rgba(62,207,142,.12);color:var(--green)}
.kds-line-note{margin-top:10px;padding:10px 12px;border-radius:12px;background:rgba(255,255,255,.035);font-size:12px;color:var(--text2);line-height:1.5}
.kds-actions{display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-top:12px}
.kds-btn{height:38px;border-radius:12px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.03);color:#c7bfc0;font-size:12px;font-weight:800;cursor:pointer;transition:all .16s ease}
.kds-btn:hover{filter:brightness(1.05);transform:translateY(-1px)}
.kds-btn.pending{border-color:rgba(40,188,238,.2);color:var(--blue)}
.kds-btn.preparing{border-color:rgba(249,181,28,.22);color:var(--gold)}
.kds-btn.ready{border-color:rgba(62,207,142,.22);color:var(--green)}
.kds-empty{padding:34px 12px;text-align:center;color:var(--text3);font-size:12px}
@media(max-width:1180px){.kds-hero{grid-template-columns:repeat(2,1fr)}.kds-board{grid-template-columns:1fr}}
@media(max-width:760px){.kds-hero,.kds-actions{grid-template-columns:1fr}}
</style>

<div class="kds-hero">
    <div class="kds-stat">
        <div class="kds-k">Pending</div>
        <div class="kds-v">{{ $summary['pending'] }}</div>
        <div class="kds-h">New items waiting to be picked up by a station</div>
    </div>
    <div class="kds-stat">
        <div class="kds-k">Preparing</div>
        <div class="kds-v">{{ $summary['preparing'] }}</div>
        <div class="kds-h">Active kitchen and bar workload</div>
    </div>
    <div class="kds-stat">
        <div class="kds-k">Ready</div>
        <div class="kds-v">{{ $summary['ready'] }}</div>
        <div class="kds-h">Finished items waiting for pickup</div>
    </div>
    <div class="kds-stat">
        <div class="kds-k">Live Tables</div>
        <div class="kds-v">{{ $summary['tables'] }}</div>
        <div class="kds-h">Distinct tables represented on the kitchen board</div>
    </div>
</div>

<div class="kds-board">
    @foreach($columns as $status => $meta)
        <section class="kds-column">
            <div class="kds-head">
                <div>
                    <div class="kds-title">{{ $meta['title'] }}</div>
                    <div class="kds-sub">{{ $meta['copy'] }}</div>
                </div>
                <span class="kds-badge {{ $meta['badge'] }}">{{ $items->where('kitchen_status', $status)->count() }}</span>
            </div>
            <div class="kds-pad">
                <div class="kds-stack">
                    @forelse($items->where('kitchen_status', $status) as $item)
                        <article class="kds-ticket">
                            <div class="kds-ticket-top">
                                <div>
                                    <h4>{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }} × {{ $item->product_name }}</h4>
                                    <p>{{ $item->order->order_number ?? 'Order' }} · {{ $item->created_at?->format('d M · H:i') }}</p>
                                </div>
                                <span class="kds-badge {{ $meta['badge'] }}">{{ ucfirst($status) }}</span>
                            </div>

                            <div class="kds-tags">
                                <span class="kds-tag type">{{ $typeLabel[$item->order->order_type ?? 'dine_in'] ?? ucfirst($item->order->order_type ?? 'dine_in') }}</span>
                                <span class="kds-tag table">{{ $item->order->table->name ?? 'No table' }}</span>
                                @if($item->order->covers)
                                    <span class="kds-tag note">{{ $item->order->covers }} covers</span>
                                @endif
                            </div>

                            @if($item->notes)
                                <div class="kds-line-note">{{ $item->notes }}</div>
                            @endif

                            <form method="post" action="{{ route('actions.kds') }}" class="kds-actions">
                                @csrf
                                <input type="hidden" name="item_id" value="{{ $item->id }}">
                                <button class="kds-btn pending" type="submit" name="status" value="pending">Pending</button>
                                <button class="kds-btn preparing" type="submit" name="status" value="preparing">Preparing</button>
                                <button class="kds-btn ready" type="submit" name="status" value="ready">Ready</button>
                            </form>
                        </article>
                    @empty
                        <div class="kds-empty">No {{ strtolower($meta['title']) }} items right now.</div>
                    @endforelse
                </div>
            </div>
        </section>
    @endforeach
</div>
@endsection
