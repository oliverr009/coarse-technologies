@extends('layouts.app', ['title' => 'Shifts & Till'])
@section('content')
<style>
.sft-hero{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:16px}
.sft-stat,.sft-card{border:1px solid var(--border);border-radius:20px;background:linear-gradient(180deg,rgba(43,37,38,.96),rgba(31,26,27,.98))}
.sft-stat{padding:16px 18px}
.sft-k{font-size:11px;color:var(--text3);text-transform:uppercase;letter-spacing:.08em}
.sft-v{margin-top:8px;font-size:26px;font-weight:800;color:var(--text);font-family:'Space Mono',monospace}
.sft-h{margin-top:6px;font-size:12px;color:var(--text2)}
.sft-shell{display:grid;grid-template-columns:minmax(0,1.1fr) minmax(340px,.9fr);gap:16px}
.sft-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:16px 18px;border-bottom:1px solid var(--border)}
.sft-title{font-size:13px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
.sft-sub{font-size:12px;color:var(--text3);margin-top:4px}
.sft-pad{padding:16px 18px}
.sft-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.sft-full{grid-column:1 / -1}
.sft-badge{display:inline-flex;align-items:center;padding:4px 8px;border-radius:999px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.06em}
.sb-open{background:rgba(62,207,142,.12);color:var(--green)}
.sb-closed{background:rgba(248,113,113,.12);color:var(--red)}
.sft-mini{display:flex;flex-direction:column;gap:10px}
.sft-mini-item{display:flex;justify-content:space-between;gap:10px;padding:11px 0;border-bottom:1px solid var(--border)}
.sft-mini-item:last-child{border-bottom:none}
.sft-mini-item strong{font-size:13px}
.sft-mini-item span{display:block;font-size:11px;color:var(--text3);margin-top:3px}
.sft-note{margin-top:12px;padding:12px;border-radius:14px;background:rgba(40,188,238,.08);border:1px solid rgba(40,188,238,.16);font-size:12px;color:var(--text2);line-height:1.5}
@media(max-width:1180px){.sft-hero{grid-template-columns:repeat(2,1fr)}.sft-shell{grid-template-columns:1fr}}
@media(max-width:760px){.sft-hero,.sft-grid{grid-template-columns:1fr}}
</style>

<div class="sft-hero">
    <div class="sft-stat"><div class="sft-k">Open Shifts</div><div class="sft-v">{{ $summary['open_shifts'] }}</div><div class="sft-h">Currently active tills</div></div>
    <div class="sft-stat"><div class="sft-k">Today’s Shifts</div><div class="sft-v">{{ $summary['today_shifts'] }}</div><div class="sft-h">Opened during today’s service</div></div>
    <div class="sft-stat"><div class="sft-k">Today Variance</div><div class="sft-v">KES {{ number_format($summary['today_variance'], 0) }}</div><div class="sft-h">Net over/short for today</div></div>
    <div class="sft-stat"><div class="sft-k">Cash Entries</div><div class="sft-v">{{ $summary['cash_entries'] }}</div><div class="sft-h">Drawer movements logged today</div></div>
</div>

<div class="sft-shell">
    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="sft-card">
            <div class="sft-head">
                <div>
                    <div class="sft-title">Current Shift</div>
                    <div class="sft-sub">Open one till per cashier, track drawer movements, then reconcile cleanly at close.</div>
                </div>
                @if($activeShift)
                    <span class="sft-badge sb-open">Open</span>
                @endif
            </div>
            <div class="sft-pad">
                @if($activeShift)
                    <div class="sft-mini">
                        <div class="sft-mini-item"><div><strong>{{ $activeShift->shift_number }}</strong><span>{{ $activeShift->cashier?->name ?? 'Cashier' }} · opened {{ $activeShift->opened_at?->format('d M H:i') }}</span></div><div class="sft-badge sb-open">open</div></div>
                        <div class="sft-mini-item"><div><strong>Opening Float</strong><span>Starting drawer cash</span></div><div>KES {{ number_format((float) $activeShift->opening_float, 2) }}</div></div>
                        <div class="sft-mini-item"><div><strong>Expected Cash</strong><span>Float plus manual cash movements</span></div><div>KES {{ number_format((float) $activeShift->expected_cash, 2) }}</div></div>
                    </div>

                    <div class="sft-note">Sales cash-up can be tied in later. This pass gives you a controlled opening, manual in/out tracking, and close reconciliation foundation now.</div>
                @else
                    <form method="post" action="{{ route('actions.shifts.open') }}">
                        @csrf
                        <div class="sft-grid">
                            <div><div class="lbl">Opening Float</div><input class="inp" type="number" step="0.01" min="0" name="opening_float" required></div>
                            <div class="sft-full"><div class="lbl">Notes</div><textarea class="inp" rows="3" name="notes" placeholder="Shift name, drawer note, or starting remarks."></textarea></div>
                        </div>
                        <button class="btn btn-primary">Open Shift</button>
                    </form>
                @endif
            </div>
        </div>

        @if($activeShift)
            <div class="sft-card">
                <div class="sft-head">
                    <div>
                        <div class="sft-title">Cash Movements</div>
                        <div class="sft-sub">Record float top-ups, payouts, petty cash, and other till movements.</div>
                    </div>
                </div>
                <div class="sft-pad">
                    <form method="post" action="{{ route('actions.shifts.entry') }}">
                        @csrf
                        <input type="hidden" name="shift_id" value="{{ $activeShift->id }}">
                        <div class="sft-grid">
                            <div><div class="lbl">Entry Type</div><select class="inp" name="entry_type"><option value="cash_in">Cash In</option><option value="cash_out">Cash Out</option><option value="petty_cash">Petty Cash</option><option value="payout">Payout</option><option value="float_topup">Float Top-Up</option></select></div>
                            <div><div class="lbl">Amount</div><input class="inp" type="number" step="0.01" min="0.01" name="amount" required></div>
                            <div class="sft-full"><div class="lbl">Reason</div><input class="inp" name="reason" required></div>
                            <div class="sft-full"><div class="lbl">Notes</div><textarea class="inp" rows="2" name="notes"></textarea></div>
                        </div>
                        <button class="btn btn-primary">Post Cash Entry</button>
                    </form>
                </div>
            </div>

            <div class="sft-card">
                <div class="sft-head">
                    <div>
                        <div class="sft-title">Close Shift</div>
                        <div class="sft-sub">Count the drawer and record the variance against expected cash.</div>
                    </div>
                </div>
                <div class="sft-pad">
                    <form method="post" action="{{ route('actions.shifts.close') }}">
                        @csrf
                        <input type="hidden" name="shift_id" value="{{ $activeShift->id }}">
                        <div class="sft-grid">
                            <div><div class="lbl">Counted Cash</div><input class="inp" type="number" step="0.01" min="0" name="counted_cash" required></div>
                            <div class="sft-full"><div class="lbl">Closing Notes</div><textarea class="inp" rows="2" name="notes" placeholder="Over/short reason, supervisor note, or handover remark."></textarea></div>
                        </div>
                        <button class="btn btn-primary">Close Shift</button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    <div class="sft-card">
        <div class="sft-head">
            <div>
                <div class="sft-title">Recent Shifts</div>
                <div class="sft-sub">Latest cashier sessions with opening, expected, counted, and variance values.</div>
            </div>
        </div>
        <div class="sft-pad">
            <div class="sft-mini">
                @forelse($recentShifts as $shift)
                    <div class="sft-mini-item">
                        <div>
                            <strong>{{ $shift->shift_number }}</strong>
                            <span>{{ $shift->cashier?->name ?? 'Cashier' }} · {{ $shift->opened_at?->format('d M H:i') }} @if($shift->closed_at) to {{ $shift->closed_at?->format('H:i') }} @endif</span>
                            <span>Float {{ number_format((float) $shift->opening_float, 2) }} · Expected {{ number_format((float) $shift->expected_cash, 2) }} · Counted {{ number_format((float) ($shift->counted_cash ?? 0), 2) }}</span>
                        </div>
                        <div style="text-align:right">
                            <div class="sft-badge {{ $shift->status === 'open' ? 'sb-open' : 'sb-closed' }}">{{ $shift->status }}</div>
                            @if($shift->status === 'closed')
                                <div style="margin-top:6px;font-size:12px;color:{{ (float) $shift->variance_amount === 0.0 ? 'var(--green)' : 'var(--gold)' }}">Var {{ number_format((float) $shift->variance_amount, 2) }}</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div style="color:var(--text3);font-size:12px">No shifts recorded yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
