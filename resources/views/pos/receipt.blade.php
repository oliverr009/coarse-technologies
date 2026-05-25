@extends('layouts.app', ['title' => 'Receipt'])

@php
    $changeDue = max(0, (float) $sale->amount_paid - (float) $sale->total_amount);
    $balanceDue = max(0, (float) $sale->balance_due);
    $refundedAmount = (float) $sale->adjustments->where('adjustment_type', 'refund_sale')->sum('amount');
@endphp

@section('content')
<div class="receipt-layout">
    <div class="receipt-card">
        <div class="receipt-head">
            <div>
                <div class="receipt-brand">COARSE POS</div>
                <div class="receipt-muted">Restaurant Receipt</div>
            </div>
            <div class="receipt-actions print-hide">
                <button class="btn btn-gold" type="button" onclick="window.print()">Print</button>
                <a class="btn btn-primary" href="{{ route('pos.index') }}">New Sale</a>
            </div>
        </div>

        <div class="receipt-number">{{ $sale->sale_number }}</div>

        <div class="receipt-meta">
            <span>Date: <strong>{{ $sale->created_at->format('d M Y H:i') }}</strong></span>
            <span>Cashier: <strong>{{ $sale->cashier?->name ?? 'N/A' }}</strong></span>
            <span>Type: <strong>{{ str_replace('_', ' ', $sale->order_type) }}</strong></span>
            <span>Payment: <strong>{{ strtoupper($sale->payment_method) }}</strong></span>
            <span>Status: <strong>{{ strtoupper(str_replace('_', ' ', $sale->status)) }}</strong></span>
            @if($sale->table)<span>Table: <strong>{{ $sale->table->name }}</strong></span>@endif
            <span>Customer: <strong>{{ $sale->customer?->name ?? 'Walk-in' }}</strong></span>
        </div>

        @if($sale->notes)
            <div class="receipt-note">{{ $sale->notes }}</div>
        @endif

        <table class="receipt-items">
            <thead>
                <tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->product_name }}</strong>
                            @if($item->notes)<div class="receipt-muted">{{ $item->notes }}</div>@endif
                        </td>
                        <td>{{ number_format($item->quantity, 2) }}</td>
                        <td>{{ number_format($item->unit_price, 2) }}</td>
                        <td>{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="receipt-totals">
            <div><span>Subtotal</span><strong>KES {{ number_format($sale->subtotal, 2) }}</strong></div>
            <div><span>Discount @if($sale->discount_value > 0)({{ $sale->discount_type === 'percent' ? number_format($sale->discount_value, 2).'%' : 'fixed' }})@endif</span><strong>- KES {{ number_format($sale->discount_amount, 2) }}</strong></div>
            <div><span>Service charge @if($sale->service_charge_rate > 0)({{ number_format($sale->service_charge_rate, 2) }}%)@endif</span><strong>KES {{ number_format($sale->service_charge_amount, 2) }}</strong></div>
            <div><span>VAT</span><strong>KES {{ number_format($sale->tax_amount, 2) }}</strong></div>
            <div class="receipt-grand"><span>Total</span><strong>KES {{ number_format($sale->total_amount, 2) }}</strong></div>
            <div><span>Received</span><strong>KES {{ number_format($sale->amount_paid, 2) }}</strong></div>
            <div><span>Refunded</span><strong>KES {{ number_format($refundedAmount, 2) }}</strong></div>
            <div><span>Change due</span><strong>KES {{ number_format($changeDue, 2) }}</strong></div>
            <div><span>Balance / Credit</span><strong>KES {{ number_format($balanceDue, 2) }}</strong></div>
        </div>

        <div class="receipt-payments">
            <div class="sec-title">Payments</div>
            @forelse($sale->payments as $payment)
                <div>
                    <span>{{ strtoupper($payment->method) }} @if($payment->reference)<em>{{ $payment->reference }}</em>@endif</span>
                    <strong>KES {{ number_format($payment->amount, 2) }}</strong>
                </div>
            @empty
                <div class="receipt-muted">No payment line recorded. Balance is on customer credit.</div>
            @endforelse
        </div>

        <div class="card print-hide" style="margin-top:18px">
            <div class="sec-head"><span class="sec-title">Post-Sale Controls</span></div>
            <div class="grid-2">
                <form method="post" action="{{ route('pos.void-sale', $sale) }}">
                    @csrf
                    <div class="lbl">Void Sale</div>
                    <p><input class="inp" name="reason" placeholder="Reason for void" required></p>
                    <p><textarea class="inp" name="notes" rows="2" placeholder="Notes for audit trail"></textarea></p>
                    <p><input class="inp" name="manager_pin" type="password" placeholder="Manager PIN or manager password" required></p>
                    <button class="btn btn-danger" {{ $sale->status === 'voided' ? 'disabled' : '' }}>Void Sale & Restock</button>
                </form>

                <form method="post" action="{{ route('pos.refund-sale', $sale) }}">
                    @csrf
                    <div class="lbl">Refund Sale</div>
                    <p><input class="inp" name="amount" type="number" step="0.01" max="{{ number_format(max(0, (float) $sale->total_amount - $refundedAmount), 2, '.', '') }}" placeholder="Refund amount" required></p>
                    <p>
                        <select class="inp" name="refund_method">
                            <option value="cash">Cash</option>
                            <option value="mpesa">M-Pesa</option>
                            <option value="card">Card</option>
                            <option value="credit_adjustment">Credit Adjustment</option>
                            <option value="bank">Bank</option>
                        </select>
                    </p>
                    <p><input class="inp" name="reason" placeholder="Reason for refund" required></p>
                    <p><input class="inp" name="manager_pin" type="password" placeholder="Manager PIN or manager password" required></p>
                    <button class="btn btn-primary" {{ $sale->status === 'voided' ? 'disabled' : '' }}>Record Refund</button>
                </form>
            </div>
        </div>

        <div class="card print-hide" style="margin-top:16px">
            <div class="sec-head"><span class="sec-title">Adjustment Audit</span></div>
            <table>
                <thead>
                    <tr><th>When</th><th>Type</th><th>Amount</th><th>Reason</th><th>Approved By</th></tr>
                </thead>
                <tbody>
                    @forelse($sale->adjustments as $adjustment)
                        <tr>
                            <td>{{ $adjustment->created_at?->format('d M H:i') }}</td>
                            <td><span class="badge {{ $adjustment->adjustment_type === 'void_sale' ? 'b-red' : 'b-gold' }}">{{ str_replace('_', ' ', $adjustment->adjustment_type) }}</span></td>
                            <td>KES {{ number_format($adjustment->amount, 2) }}</td>
                            <td>{{ $adjustment->reason }}</td>
                            <td>{{ $adjustment->approver?->name ?? (($adjustment->meta['approval_source'] ?? null) === 'settings_override_pin' ? 'Override PIN' : '-') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="color:var(--text3)">No sale adjustments recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="receipt-footer">
            <strong>Thank you</strong>
            <span>Powered by COARSE Restaurant POS</span>
        </div>

        <div class="receipt-actions print-hide">
            <a class="btn btn-primary" href="{{ route('pos.index') }}">New Sale</a>
            <a class="btn btn-ghost" href="{{ route('reports') }}">View Reports</a>
        </div>
    </div>
</div>
@endsection
