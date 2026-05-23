@extends('layouts.app', ['title' => 'Dashboard'])

@section('content')
<div class="stats-row">
    <div class="stat-card sc-blue">
        <div class="stat-label">Today's Sales</div>
        <div class="stat-value sv-blue">{{ number_format($metrics['sales_today'], 0) }}</div>
        <div class="stat-change up">Completed bills today</div>
    </div>
    <div class="stat-card sc-green">
        <div class="stat-label">Transactions</div>
        <div class="stat-value sv-green">{{ $metrics['bills_today'] }}</div>
        <div class="stat-change up">Posted sales today</div>
    </div>
    <div class="stat-card sc-gold">
        <div class="stat-label">Outstanding Credit</div>
        <div class="stat-value sv-gold">{{ number_format($outstandingCredit, 0) }}</div>
        <div class="stat-change dn">{{ $openCreditCount }} open balances</div>
    </div>
    <div class="stat-card sc-red">
        <div class="stat-label">Low Stock Items</div>
        <div class="stat-value sv-red">{{ $lowStockCount }}</div>
        <div class="stat-change">Needs reorder</div>
    </div>
</div>

<div class="grid-65" style="margin-bottom:16px">
    <div class="card">
        <div class="sec-head">
            <span class="sec-title">Sales This Week</span>
            <a class="sec-action" href="{{ route('reports') }}">Export →</a>
        </div>
        <div class="chart-bars" style="margin-bottom:24px">
            @foreach($weeklySales as $day)
                @php $height = max(12, round(($day['total'] / $weeklySalesMax) * 100)); @endphp
                <div class="c-bar" style="background:{{ $loop->last ? 'var(--gold)' : 'var(--blue)' }};opacity:{{ $loop->last ? '1' : number_format(0.45 + ($loop->iteration * 0.07), 2) }};height:{{ min(100, $height) }}%">
                    <span class="c-bar-label" @if($loop->last) style="color:var(--gold)" @endif>{{ $day['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="card">
        <div class="sec-head"><span class="sec-title">Recent Activity</span></div>
        <div class="activity">
            @forelse($activity as $move)
                <div class="act-item">
                    <div class="act-dot" style="background:{{ $move->movement_type === 'SALE_CONSUMPTION' ? 'var(--green)' : ($move->movement_type === 'PURCHASE_RECEIPT' ? 'var(--blue)' : 'var(--gold)') }}"></div>
                    <span class="act-text">{{ $move->product->name ?? 'Product' }} — {{ str_replace('_', ' ', strtolower($move->movement_type)) }}</span>
                    <span class="act-time">{{ $move->created_at?->diffForHumans() }}</span>
                </div>
            @empty
                <div class="act-item"><span class="act-text">No stock activity yet.</span></div>
            @endforelse
        </div>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="sec-head"><span class="sec-title">Top Selling Items</span><a class="sec-action" href="{{ route('reports') }}">View all →</a></div>
        <div class="tbl-wrap">
            <table>
                <thead><tr><th>Item</th><th>Qty Sold</th><th>Revenue</th><th>Trend</th></tr></thead>
                <tbody>
                    @forelse($topProducts as $row)
                        <tr>
                            <td>{{ $row->product_name }}</td>
                            <td>{{ number_format($row->qty, 0) }}</td>
                            <td style="font-family:'Space Mono',monospace;color:var(--blue)">{{ number_format($row->amount, 0) }}</td>
                            <td>
                                @if($row->qty >= 20)
                                    <span class="badge b-green">↑ Hot</span>
                                @elseif($row->qty >= 8)
                                    <span class="badge b-blue">Steady</span>
                                @else
                                    <span class="badge b-gray">Normal</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4">No sales yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="sec-head"><span class="sec-title">Payment Methods — Today</span></div>
        <div style="display:flex;flex-direction:column;gap:12px;margin-top:4px">
            @foreach($paymentMix as $row)
                <div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:6px">
                        <span style="font-size:12px;color:var(--text2)">{{ $row['label'] }}</span>
                        <span style="font-size:12px;font-family:'Space Mono',monospace;color:{{ $row['color'] }}">KES {{ number_format($row['amount'], 0) }} ({{ $row['percent'] }}%)</span>
                    </div>
                    <div class="stock-bar"><div class="stock-fill {{ $row['fill'] }}" style="width:{{ $row['percent'] }}%;{{ $row['fill_style'] ?? '' }}"></div></div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
