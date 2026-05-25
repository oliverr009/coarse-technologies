@extends('layouts.app', ['title' => 'Reports'])
@section('content')
<div class="stats-row">
    <div class="stat-card"><div class="stat-label">Operating Expenses</div><div class="stat-value sv-gold">KES {{ number_format($expenses,2) }}</div><div class="stat-change">All time</div></div>
    <div class="stat-card"><div class="stat-label">Ledger Rows</div><div class="stat-value sv-blue">{{ $movements->count() }}</div><div class="stat-change">Latest stock activity</div></div>
    <div class="stat-card"><div class="stat-label">POS Audit Logs</div><div class="stat-value sv-red">{{ $auditLogs->count() }}</div><div class="stat-change">Latest controlled actions</div></div>
    <div class="stat-card"><div class="stat-label">Sale Adjustments</div><div class="stat-value sv-green">{{ $saleAdjustments->count() }}</div><div class="stat-change">Latest refunds and voids</div></div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="sec-head"><span class="sec-title">Ingredient Consumption</span></div>
        <table><thead><tr><th>Product</th><th>Qty</th><th>Cost</th></tr></thead><tbody>@foreach($consumption as $c)<tr><td>{{ $c->name }}</td><td>{{ number_format($c->qty,4) }} {{ $c->unit }}</td><td>KES {{ number_format($c->cost,2) }}</td></tr>@endforeach</tbody></table>
    </div>
    <div class="card">
        <div class="sec-head"><span class="sec-title">Stock Movement Audit</span></div>
        <table><thead><tr><th>Product</th><th>Type</th><th>Qty</th></tr></thead><tbody>@foreach($movements as $m)<tr><td>{{ $m->product->name ?? '-' }}</td><td><span class="badge b-blue">{{ $m->movement_type }}</span></td><td>{{ number_format($m->quantity,4) }}</td></tr>@endforeach</tbody></table>
    </div>
</div>

<div class="card" style="margin-top:16px">
    <div class="sec-head"><span class="sec-title">Manager Approval Audit</span></div>
    <table>
        <thead>
            <tr>
                <th>When</th>
                <th>Action</th>
                <th>Actor</th>
                <th>Approver</th>
                <th>Reference</th>
                <th>Reason</th>
            </tr>
        </thead>
        <tbody>
            @forelse($auditLogs as $log)
                <tr>
                    <td>{{ $log->created_at?->format('d M H:i') }}</td>
                    <td><span class="badge {{ $log->action_type === 'item_voided' ? 'b-red' : 'b-gold' }}">{{ str_replace('_', ' ', $log->action_type) }}</span></td>
                    <td>{{ $log->actor?->name ?? 'System' }}</td>
                    <td>{{ $log->approver?->name ?? (($log->context['approval_source'] ?? null) === 'settings_override_pin' ? 'Override PIN' : '-') }}</td>
                    <td>{{ $log->sale?->sale_number ?? $log->order?->order_number ?? ($log->context['reference'] ?? '-') }}</td>
                    <td>{{ $log->context['reason'] ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="color:var(--text3)">No approval audit entries yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="card" style="margin-top:16px">
    <div class="sec-head"><span class="sec-title">Refunds & Voids</span></div>
    <table>
        <thead>
            <tr>
                <th>When</th>
                <th>Sale</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Actor</th>
                <th>Approver</th>
                <th>Reason</th>
            </tr>
        </thead>
        <tbody>
            @forelse($saleAdjustments as $adjustment)
                <tr>
                    <td>{{ $adjustment->created_at?->format('d M H:i') }}</td>
                    <td>{{ $adjustment->sale?->sale_number ?? '-' }}</td>
                    <td><span class="badge {{ $adjustment->adjustment_type === 'void_sale' ? 'b-red' : 'b-blue' }}">{{ str_replace('_', ' ', $adjustment->adjustment_type) }}</span></td>
                    <td>KES {{ number_format($adjustment->amount, 2) }}</td>
                    <td>{{ $adjustment->actor?->name ?? '-' }}</td>
                    <td>{{ $adjustment->approver?->name ?? (($adjustment->meta['approval_source'] ?? null) === 'settings_override_pin' ? 'Override PIN' : '-') }}</td>
                    <td>{{ $adjustment->reason }}</td>
                </tr>
                @if($adjustment->adjustment_type === 'return_items' && $adjustment->items->isNotEmpty())
                    <tr>
                        <td colspan="7" style="color:var(--text3)">
                            @foreach($adjustment->items as $line)
                                <div>{{ number_format($line->quantity, 2) }} × {{ $line->product_name }} · KES {{ number_format($line->line_total, 2) }} @if($line->restocked)<strong style="color:var(--green)">restocked</strong>@else<em style="font-style:normal;color:var(--gold)">not restocked</em>@endif</div>
                            @endforeach
                        </td>
                    </tr>
                @endif
            @empty
                <tr><td colspan="7" style="color:var(--text3)">No refunds or voids recorded yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
