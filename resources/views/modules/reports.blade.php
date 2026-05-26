@extends('layouts.app', ['title' => 'Reports'])
@section('content')
<style>
.ops-hero{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:16px}
.ops-stat,.ops-card{border:1px solid var(--border);border-radius:20px;background:linear-gradient(180deg,rgba(43,37,38,.96),rgba(31,26,27,.98))}
.ops-stat{padding:16px 18px}
.ops-k{font-size:11px;color:var(--text3);text-transform:uppercase;letter-spacing:.08em}
.ops-v{margin-top:8px;font-size:26px;font-weight:800;color:var(--text);font-family:'Space Mono',monospace}
.ops-h{margin-top:6px;font-size:12px;color:var(--text2)}
.ops-grid{display:grid;grid-template-columns:1.2fr .8fr;gap:16px}
.ops-stack{display:flex;flex-direction:column;gap:16px}
.ops-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:16px 18px;border-bottom:1px solid var(--border)}
.ops-title{font-size:13px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
.ops-sub{font-size:12px;color:var(--text3);margin-top:4px;line-height:1.5}
.ops-pad{padding:16px 18px}
.ops-mini{display:flex;flex-direction:column;gap:10px}
.ops-mini-item{display:flex;justify-content:space-between;gap:10px;padding:11px 0;border-bottom:1px solid var(--border)}
.ops-mini-item:last-child{border-bottom:none}
.ops-mini-item strong{font-size:13px}
.ops-mini-item span{display:block;font-size:11px;color:var(--text3);margin-top:3px}
.ops-badge{display:inline-flex;align-items:center;padding:4px 8px;border-radius:999px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.06em}
.ob-blue{background:rgba(40,188,238,.12);color:var(--blue)}
.ob-gold{background:rgba(249,181,28,.14);color:var(--gold)}
.ob-green{background:rgba(62,207,142,.12);color:var(--green)}
.ob-red{background:rgba(248,113,113,.12);color:var(--red)}
@media(max-width:1180px){.ops-hero{grid-template-columns:repeat(2,1fr)}.ops-grid{grid-template-columns:1fr}}
@media(max-width:760px){.ops-hero{grid-template-columns:1fr}}
</style>

<div class="ops-hero">
    <div class="ops-stat"><div class="ops-k">Sales Today</div><div class="ops-v">KES {{ number_format($opsSummary['sales_today_value'], 0) }}</div><div class="ops-h">{{ $opsSummary['sales_today_count'] }} completed sales today</div></div>
    <div class="ops-stat"><div class="ops-k">Live Orders</div><div class="ops-v">{{ $opsSummary['live_orders'] }}</div><div class="ops-h">{{ $opsSummary['kitchen_pending'] }} kitchen items still in progress</div></div>
    <div class="ops-stat"><div class="ops-k">Floor Load</div><div class="ops-v">{{ $opsSummary['tables_occupied'] }}</div><div class="ops-h">{{ $opsSummary['tables_cleaning'] }} tables waiting on cleanup</div></div>
    <div class="ops-stat"><div class="ops-k">Stock Alerts</div><div class="ops-v">{{ $opsSummary['stock_alerts'] }}</div><div class="ops-h">{{ $opsSummary['reservations_today'] }} reservations booked for today</div></div>
</div>

<div class="ops-grid">
    <div class="ops-stack">
        <div class="ops-card">
            <div class="ops-head">
                <div>
                    <div class="ops-title">Live Operations Snapshot</div>
                    <div class="ops-sub">Current service across orders, tables, and today’s business flow.</div>
                </div>
            </div>
            <div class="ops-pad">
                <div class="ops-mini">
                    @forelse($liveOrders as $order)
                        <div class="ops-mini-item">
                            <div>
                                <strong>{{ $order->order_number }}</strong>
                                <span>{{ $order->customer?->name ?: 'Walk-in customer' }} · {{ $order->table?->name ?: 'No table' }} · {{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                            </div>
                            <div class="ops-badge {{ $order->status === 'ready' ? 'ob-green' : ($order->status === 'held' ? 'ob-gold' : 'ob-blue') }}">KES {{ number_format((float) $order->subtotal, 0) }}</div>
                        </div>
                    @empty
                        <div style="color:var(--text3);font-size:12px">No live orders right now.</div>
                    @endforelse
                </div>
            </div>
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
    </div>

    <div class="ops-stack">
        <div class="ops-card">
            <div class="ops-head">
                <div>
                    <div class="ops-title">Floor Summary</div>
                    <div class="ops-sub">Front-of-house pressure points for the current service.</div>
                </div>
            </div>
            <div class="ops-pad">
                <div class="ops-mini">
                    <div class="ops-mini-item"><div><strong>Available Tables</strong><span>Ready for seating</span></div><div class="ops-badge ob-green">{{ $tableSummary['available'] }}</div></div>
                    <div class="ops-mini-item"><div><strong>Occupied Tables</strong><span>Guests currently seated</span></div><div class="ops-badge ob-blue">{{ $tableSummary['occupied'] }}</div></div>
                    <div class="ops-mini-item"><div><strong>Reserved Tables</strong><span>Held for bookings</span></div><div class="ops-badge ob-gold">{{ $tableSummary['reserved'] }}</div></div>
                    <div class="ops-mini-item"><div><strong>Needs Cleaning</strong><span>Turn before the next cover</span></div><div class="ops-badge ob-red">{{ $tableSummary['needs_cleaning'] }}</div></div>
                </div>
            </div>
        </div>

        <div class="ops-card">
            <div class="ops-head">
                <div>
                    <div class="ops-title">Today’s Reservations</div>
                    <div class="ops-sub">Upcoming bookings the host stand should keep in view.</div>
                </div>
            </div>
            <div class="ops-pad">
                <div class="ops-mini">
                    @forelse($reservationsToday as $reservation)
                        <div class="ops-mini-item">
                            <div>
                                <strong>{{ $reservation->customer_name }}</strong>
                                <span>{{ $reservation->reserved_for?->format('H:i') }} · {{ $reservation->covers }} covers · {{ $reservation->table?->name ?? 'Unassigned table' }}</span>
                            </div>
                            <div class="ops-badge ob-gold">{{ $reservation->status }}</div>
                        </div>
                    @empty
                        <div style="color:var(--text3);font-size:12px">No reservations scheduled for today.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="ops-card">
            <div class="ops-head">
                <div>
                    <div class="ops-title">Stock Alert Queue</div>
                    <div class="ops-sub">Items below or at reorder level that need management attention.</div>
                </div>
            </div>
            <div class="ops-pad">
                <div class="ops-mini">
                    @forelse($stockAlerts as $product)
                        <div class="ops-mini-item">
                            <div>
                                <strong>{{ $product->name }}</strong>
                                <span>On hand {{ number_format((float) ($product->stock_qty ?? 0), 2) }} {{ $product->unit }} · reorder at {{ number_format((float) $product->reorder_level, 2) }}</span>
                            </div>
                            <div class="ops-badge {{ (float) ($product->stock_qty ?? 0) < 0 ? 'ob-red' : 'ob-gold' }}">{{ (float) ($product->stock_qty ?? 0) < 0 ? 'negative' : 'reorder' }}</div>
                        </div>
                    @empty
                        <div style="color:var(--text3);font-size:12px">No stock alerts right now.</div>
                    @endforelse
                </div>
            </div>
        </div>
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
    <div class="sec-head"><span class="sec-title">Inventory Adjustments</span></div>
    <table>
        <thead>
            <tr>
                <th>When</th>
                <th>Product</th>
                <th>Reason</th>
                <th>Expected</th>
                <th>Counted</th>
                <th>Variance</th>
                <th>Actor</th>
            </tr>
        </thead>
        <tbody>
            @forelse($inventoryAdjustments as $adjustment)
                <tr>
                    <td>{{ $adjustment->created_at?->format('d M H:i') }}</td>
                    <td>{{ $adjustment->product?->name ?? '-' }}</td>
                    <td><span class="badge b-gold">{{ str_replace('_', ' ', $adjustment->reason) }}</span></td>
                    <td>{{ number_format($adjustment->expected_qty, 2) }}</td>
                    <td>{{ number_format($adjustment->counted_qty, 2) }}</td>
                    <td style="color:{{ $adjustment->variance_qty >= 0 ? 'var(--green)' : 'var(--red)' }}">{{ $adjustment->variance_qty >= 0 ? '+' : '' }}{{ number_format($adjustment->variance_qty, 2) }}</td>
                    <td>{{ $adjustment->actor?->name ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" style="color:var(--text3)">No inventory adjustments recorded yet.</td></tr>
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
                                <div>{{ number_format($line->quantity, 2) }} × {{ $line->product_name }} · {{ number_format($line->line_total, 2) }} KES @if($line->restocked)<strong style="color:var(--green)">restocked</strong>@else<em style="font-style:normal;color:var(--gold)">not restocked</em>@endif</div>
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
