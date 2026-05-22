@extends('layouts.app', ['title' => 'Dashboard'])

@section('content')
<div class="stats-row">
    <div class="stat-card"><div class="stat-label">Sales Today</div><div class="stat-value sv-blue">KES {{ number_format($metrics['sales_today'], 2) }}</div><div class="stat-change">Completed bills</div></div>
    <div class="stat-card"><div class="stat-label">Bills Posted</div><div class="stat-value sv-green">{{ $metrics['bills_today'] }}</div><div class="stat-change">Today</div></div>
    <div class="stat-card"><div class="stat-label">Ingredient Deductions</div><div class="stat-value sv-gold">{{ $metrics['ingredient_moves'] }}</div><div class="stat-change">Stock ledger rows</div></div>
    <div class="stat-card"><div class="stat-label">Expenses This Month</div><div class="stat-value sv-red">KES {{ number_format($metrics['expenses_month'], 2) }}</div><div class="stat-change">Approved expenses</div></div>
</div>
<div class="grid-65">
    <div class="card">
        <div class="sec-head"><span class="sec-title">Top Products</span><a class="btn btn-ghost btn-sm" href="{{ route('reports') }}">Reports</a></div>
        <div class="tbl-wrap"><table><thead><tr><th>Product</th><th>Qty</th><th>Amount</th></tr></thead><tbody>
            @forelse($topProducts as $row)<tr><td><strong>{{ $row->product_name }}</strong></td><td>{{ number_format($row->qty, 2) }}</td><td style="color:var(--blue);font-weight:700">KES {{ number_format($row->amount, 2) }}</td></tr>@empty
                <tr><td colspan="3">No sales yet.</td></tr>
            @endforelse
        </tbody></table></div>
    </div>
    <div class="card">
        <div class="sec-head"><span class="sec-title">Recent Stock Activity</span></div>
        @forelse($activity as $move)
            <div style="display:flex;justify-content:space-between;border-bottom:1px solid var(--border2);padding:9px 0">
                <span>{{ $move->product->name ?? 'Product' }} <span class="badge b-blue">{{ $move->movement_type }}</span></span>
                <strong>{{ number_format($move->quantity, 4) }}</strong>
            </div>
        @empty
            <p style="color:var(--text3)">No stock movements yet.</p>
        @endforelse
    </div>
</div>
@endsection

