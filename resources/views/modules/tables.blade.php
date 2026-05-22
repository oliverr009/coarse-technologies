@extends('layouts.app', ['title' => 'Table Management'])

@php
    $statusMeta = [
        'available' => ['Available', 'b-green'],
        'occupied' => ['Occupied', 'b-gold'],
        'reserved' => ['Reserved', 'b-blue'],
        'needs_cleaning' => ['Needs cleaning', 'b-gray'],
    ];
    $totals = [
        'available' => $tables->where('status', 'available')->count(),
        'occupied' => $tables->where('status', 'occupied')->count(),
        'reserved' => $tables->where('status', 'reserved')->count(),
        'needs_cleaning' => $tables->where('status', 'needs_cleaning')->count(),
    ];
@endphp

@section('content')
<div class="floor-page">
    <div class="stats-row">
        <div class="stat-card"><div class="stat-label">Available</div><div class="stat-value sv-green">{{ $totals['available'] }}</div><div class="stat-change">Ready for seating</div></div>
        <div class="stat-card"><div class="stat-label">Occupied</div><div class="stat-value sv-gold">{{ $totals['occupied'] }}</div><div class="stat-change">Active dining tables</div></div>
        <div class="stat-card"><div class="stat-label">Reserved</div><div class="stat-value sv-blue">{{ $totals['reserved'] }}</div><div class="stat-change">Held for guests</div></div>
        <div class="stat-card"><div class="stat-label">Cleaning</div><div class="stat-value">{{ $totals['needs_cleaning'] }}</div><div class="stat-change">Reset before seating</div></div>
    </div>

    <div class="floor-grid">
        @foreach($tables as $table)
            @php
                $activeOrder = $table->orders->first();
                [$statusLabel, $badgeClass] = $statusMeta[$table->status] ?? [ucfirst($table->status), 'b-gray'];
            @endphp
            <div class="floor-table floor-{{ $table->status }}">
                <div class="floor-table-head">
                    <div>
                        <div class="floor-table-name">{{ $table->name }}</div>
                        <div class="floor-table-meta">{{ $table->capacity }} covers</div>
                    </div>
                    <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                </div>

                @if($activeOrder)
                    <div class="floor-bill">
                        <span>{{ $activeOrder->order_number }}</span>
                        <strong>KES {{ number_format($activeOrder->subtotal, 2) }}</strong>
                    </div>
                    <div class="floor-items">
                        @foreach($activeOrder->items->take(3) as $item)
                            <span>{{ number_format($item->quantity, 0) }} x {{ $item->product_name }}</span>
                        @endforeach
                        @if($activeOrder->items->count() > 3)
                            <span>+ {{ $activeOrder->items->count() - 3 }} more</span>
                        @endif
                    </div>
                @else
                    <div class="floor-empty">No active bill on this table.</div>
                @endif

                <form method="post" action="{{ route('actions.table') }}" class="floor-actions">
                    @csrf
                    <input type="hidden" name="table_id" value="{{ $table->id }}">
                    <button class="btn btn-ghost btn-sm" name="status" value="available">Available</button>
                    <button class="btn btn-ghost btn-sm" name="status" value="reserved">Reserve</button>
                    <button class="btn btn-ghost btn-sm" name="status" value="needs_cleaning">Clean</button>
                </form>
            </div>
        @endforeach
    </div>
</div>
@endsection
