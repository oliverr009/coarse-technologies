@extends('layouts.app', ['title' => 'Credit Sales'])
@section('content')
<style>
    .credit-hero{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;margin-bottom:16px}
    .credit-stat,.credit-card{background:linear-gradient(180deg,rgba(43,37,38,.96),rgba(31,26,27,.98));border:1px solid var(--border);border-radius:20px}
    .credit-stat{padding:16px 18px}
    .credit-k{font-size:11px;color:var(--text3);text-transform:uppercase;letter-spacing:.08em}
    .credit-v{margin-top:8px;font-size:24px;font-weight:800;color:var(--text);font-family:'Space Mono',monospace}
    .credit-h{margin-top:6px;font-size:12px;color:var(--text2)}
    .credit-shell{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(340px,.9fr);gap:16px}
    .credit-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:16px 18px;border-bottom:1px solid var(--border)}
    .credit-title{font-size:13px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
    .credit-sub{font-size:12px;color:var(--text3);margin-top:4px}
    .credit-pad{padding:16px 18px}
    .credit-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
    .credit-full{grid-column:1 / -1}
    .credit-badge{display:inline-flex;align-items:center;padding:4px 8px;border-radius:999px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.06em}
    .credit-badge.blue{background:rgba(40,188,238,.12);color:var(--blue)}
    .credit-badge.gold{background:rgba(249,181,28,.14);color:var(--gold)}
    .credit-badge.green{background:rgba(62,207,142,.12);color:var(--green)}
    .credit-badge.red{background:rgba(248,113,113,.12);color:var(--red)}
    .credit-mini{display:flex;flex-direction:column;gap:0}
    .credit-line{display:flex;justify-content:space-between;gap:12px;padding:12px 0;border-bottom:1px solid var(--border)}
    .credit-line:last-child{border-bottom:none}
    .credit-line strong{font-size:13px}
    .credit-line span{display:block;font-size:11px;color:var(--text3);margin-top:3px}
    .credit-meter{height:6px;background:rgba(148,163,184,.18);border-radius:999px;overflow:hidden;margin-top:7px}
    .credit-fill{height:100%;border-radius:999px;background:linear-gradient(90deg,var(--blue),var(--gold))}
    .credit-table td,.credit-table th{font-size:12px}
    .credit-note{margin-top:12px;padding:12px;border-radius:14px;background:rgba(40,188,238,.08);border:1px solid rgba(40,188,238,.16);font-size:12px;color:var(--text2);line-height:1.5}
    @media (max-width:1180px){.credit-hero{grid-template-columns:repeat(3,1fr)}.credit-shell{grid-template-columns:1fr}}
    @media (max-width:760px){.credit-hero,.credit-grid{grid-template-columns:1fr}.credit-full{grid-column:auto}}
</style>

<div class="credit-hero">
    <div class="credit-stat">
        <div class="credit-k">Customers</div>
        <div class="credit-v">{{ $summary['customers'] }}</div>
        <div class="credit-h">Credit-enabled accounts</div>
    </div>
    <div class="credit-stat">
        <div class="credit-k">Outstanding</div>
        <div class="credit-v">KES {{ number_format($summary['outstanding'], 0) }}</div>
        <div class="credit-h">Open customer balance</div>
    </div>
    <div class="credit-stat">
        <div class="credit-k">Overdue</div>
        <div class="credit-v" style="color:var(--red)">KES {{ number_format($summary['overdue'], 0) }}</div>
        <div class="credit-h">Past due ledger debits</div>
    </div>
    <div class="credit-stat">
        <div class="credit-k">Collected This Month</div>
        <div class="credit-v" style="color:var(--green)">KES {{ number_format($summary['collections_month'], 0) }}</div>
        <div class="credit-h">Cash/M-Pesa/card/bank collections</div>
    </div>
    <div class="credit-stat">
        <div class="credit-k">Near Limit</div>
        <div class="credit-v" style="color:var(--gold)">{{ $summary['near_limit'] }}</div>
        <div class="credit-h">At 80%+ of limit</div>
    </div>
</div>

<div class="credit-shell">
    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="credit-card">
            <div class="credit-head">
                <div>
                    <div class="credit-title">Customer Accounts</div>
                    <div class="credit-sub">Balances, limits, utilization, and account activity.</div>
                </div>
                <span class="credit-badge blue">Ledger Control</span>
            </div>
            <div class="credit-pad">
                <table class="credit-table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Balance</th>
                            <th>Limit</th>
                            <th>Usage</th>
                            <th>Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($customers->isEmpty())
                            <tr><td colspan="5" style="color:var(--text3)">No customers created yet.</td></tr>
                        @else
                            @foreach($customers as $customer)
                                @php
                                    $balance = (float) ($customer->credit_balance ?? 0);
                                    $limit = (float) $customer->credit_limit;
                                    $usage = $limit > 0 ? min(100, max(0, ($balance / $limit) * 100)) : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $customer->name }}</strong>
                                        <div style="color:var(--text3);font-size:11px">{{ $customer->phone ?: 'No phone' }} @if($customer->email) · {{ $customer->email }} @endif</div>
                                    </td>
                                    <td style="color:{{ $balance > 0 ? 'var(--gold)' : 'var(--green)' }}">KES {{ number_format($balance, 2) }}</td>
                                    <td>KES {{ number_format($limit, 2) }}</td>
                                    <td>
                                        <div>{{ number_format($usage, 0) }}%</div>
                                        <div class="credit-meter"><div class="credit-fill" style="width:{{ $usage }}%"></div></div>
                                    </td>
                                    <td>{{ $customer->sales_count }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="credit-card">
            <div class="credit-head">
                <div>
                    <div class="credit-title">Credit Ledger</div>
                    <div class="credit-sub">Debit entries increase balance; credit entries reduce it.</div>
                </div>
            </div>
            <div class="credit-pad">
                <table class="credit-table">
                    <thead>
                        <tr>
                            <th>When</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Due</th>
                            <th>Reference</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($credits->isEmpty())
                            <tr><td colspan="6" style="color:var(--text3)">No credit activity yet.</td></tr>
                        @else
                            @foreach($credits as $entry)
                                <tr>
                                    <td>{{ $entry->created_at?->format('d M H:i') }}</td>
                                    <td>{{ $entry->customer?->name ?? '-' }}</td>
                                    <td><span class="credit-badge {{ (float) $entry->amount >= 0 ? 'gold' : 'green' }}">{{ $entry->type }}</span></td>
                                    <td style="color:{{ (float) $entry->amount >= 0 ? 'var(--gold)' : 'var(--green)' }}">{{ (float) $entry->amount >= 0 ? '+' : '-' }}KES {{ number_format(abs((float) $entry->amount), 2) }}</td>
                                    <td>{{ $entry->due_date?->format('d M Y') ?? '-' }}</td>
                                    <td>
                                        {{ $entry->sale?->sale_number ?? 'Manual' }}
                                        @if($entry->notes)<div style="color:var(--text3);font-size:11px">{{ $entry->notes }}</div>@endif
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="credit-card">
            <div class="credit-head">
                <div>
                    <div class="credit-title">Add Customer</div>
                    <div class="credit-sub">Create a tab/corporate customer with a controlled credit limit.</div>
                </div>
            </div>
            <div class="credit-pad">
                <form method="post" action="{{ route('actions.customer') }}">
                    @csrf
                    <div class="credit-grid">
                        <div class="credit-full"><div class="lbl">Customer</div><input class="inp" name="name" required></div>
                        <div><div class="lbl">Phone</div><input class="inp" name="phone"></div>
                        <div><div class="lbl">Email</div><input class="inp" type="email" name="email"></div>
                        <div class="credit-full"><div class="lbl">Credit Limit</div><input class="inp" name="credit_limit" type="number" step="0.01" min="0" value="0"></div>
                    </div>
                    <button class="btn btn-primary" style="margin-top:12px">Save Customer</button>
                </form>
            </div>
        </div>

        <div class="credit-card">
            <div class="credit-head">
                <div>
                    <div class="credit-title">Record Collection</div>
                    <div class="credit-sub">Post a customer payment against the credit ledger.</div>
                </div>
            </div>
            <div class="credit-pad">
                <form method="post" action="{{ route('actions.credit-payment') }}">
                    @csrf
                    <div class="credit-grid">
                        <div class="credit-full">
                            <div class="lbl">Customer</div>
                            <select class="inp" name="customer_id" required>
                                <option value="">Choose customer</option>
                                @foreach($customers as $customer)
                                    @php
                                        $balance = (float) ($customer->credit_balance ?? 0);
                                    @endphp
                                    <option value="{{ $customer->id }}">{{ $customer->name }} · KES {{ number_format($balance, 2) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div><div class="lbl">Amount</div><input class="inp" name="amount" type="number" step="0.01" min="0" required></div>
                        <div>
                            <div class="lbl">Method</div>
                            <select class="inp" name="payment_method" required>
                                <option value="mpesa">M-Pesa</option>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="bank">Bank</option>
                            </select>
                        </div>
                        <div class="credit-full"><div class="lbl">Reference</div><input class="inp" name="reference"></div>
                        <div class="credit-full"><div class="lbl">Notes</div><textarea class="inp" name="notes" rows="3"></textarea></div>
                    </div>
                    <button class="btn btn-primary" style="margin-top:12px">Post Collection</button>
                </form>
                <div class="credit-note">Credit collections are ledger credits, so the customer balance reduces without editing the original sale.</div>
            </div>
        </div>

        <div class="credit-card">
            <div class="credit-head">
                <div>
                    <div class="credit-title">Watch List</div>
                    <div class="credit-sub">Accounts with the highest current exposure.</div>
                </div>
            </div>
            <div class="credit-pad">
                <div class="credit-mini">
                    @if($creditWatchlist->isEmpty())
                        <div style="color:var(--text3);font-size:12px">No customer balances yet.</div>
                    @else
                        @foreach($creditWatchlist as $customer)
                            @php
                                $balance = (float) ($customer->credit_balance ?? 0);
                                $limit = (float) $customer->credit_limit;
                            @endphp
                            <div class="credit-line">
                                <div>
                                    <strong>{{ $customer->name }}</strong>
                                    <span>Limit KES {{ number_format($limit, 0) }} · {{ $customer->phone ?: 'No phone' }}</span>
                                </div>
                                <span class="credit-badge {{ $balance > 0 ? 'gold' : 'green' }}">KES {{ number_format($balance, 0) }}</span>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
