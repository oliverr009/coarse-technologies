@extends('layouts.app', ['title' => 'Tables'])

@section('content')
<style>
.floor-hero{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:16px}
.floor-stat,.floor-card{border:1px solid var(--border);border-radius:20px;background:linear-gradient(180deg,rgba(43,37,38,.96),rgba(31,26,27,.98))}
.floor-stat{padding:16px 18px}
.floor-k{font-size:11px;color:var(--text3);text-transform:uppercase;letter-spacing:.08em}
.floor-v{margin-top:8px;font-size:26px;font-weight:800;color:var(--text);font-family:'Space Mono',monospace}
.floor-h{margin-top:6px;font-size:12px;color:var(--text2)}
.floor-shell{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(320px,.95fr);gap:16px}
.floor-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}
.floor-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:16px 18px;border-bottom:1px solid var(--border)}
.floor-title{font-size:13px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
.floor-sub{font-size:12px;color:var(--text3);margin-top:4px}
.floor-pad{padding:16px 18px}
.table-tile{padding:16px;border:1px solid var(--border);border-radius:18px;background:rgba(255,255,255,.025);display:flex;flex-direction:column;gap:12px}
.table-top{display:flex;align-items:flex-start;justify-content:space-between;gap:10px}
.table-name{font-size:18px;font-weight:800;color:var(--text)}
.table-meta{font-size:11px;color:var(--text3);margin-top:4px}
.floor-badge{display:inline-flex;align-items:center;padding:5px 9px;border-radius:999px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.06em}
.fb-av{background:rgba(62,207,142,.12);color:var(--green)}
.fb-oc{background:rgba(40,188,238,.12);color:var(--blue)}
.fb-rs{background:rgba(249,181,28,.14);color:var(--gold)}
.fb-cl{background:rgba(248,113,113,.12);color:var(--red)}
.table-note{padding:10px 12px;border-radius:12px;background:rgba(255,255,255,.035);font-size:12px;color:var(--text2);line-height:1.5}
.table-actions{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.table-actions form{display:contents}
.table-btn{height:38px;border-radius:12px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.03);color:#c7bfc0;font-size:12px;font-weight:800;cursor:pointer}
.table-btn:hover{filter:brightness(1.05)}
.mini-list{display:flex;flex-direction:column;gap:10px}
.mini-item{display:flex;justify-content:space-between;gap:10px;padding:11px 0;border-bottom:1px solid var(--border)}
.mini-item:last-child{border-bottom:none}
.mini-item strong{font-size:13px}
.mini-item span{display:block;font-size:11px;color:var(--text3);margin-top:3px}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.full{grid-column:1 / -1}
@media(max-width:1180px){.floor-hero{grid-template-columns:repeat(2,1fr)}.floor-shell{grid-template-columns:1fr}.floor-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:760px){.floor-hero,.floor-grid,.form-grid,.table-actions{grid-template-columns:1fr}}
</style>

<div class="floor-hero">
    <div class="floor-stat"><div class="floor-k">Available</div><div class="floor-v">{{ $summary['available'] }}</div><div class="floor-h">Ready to seat now</div></div>
    <div class="floor-stat"><div class="floor-k">Occupied</div><div class="floor-v">{{ $summary['occupied'] }}</div><div class="floor-h">Live dine-in tables</div></div>
    <div class="floor-stat"><div class="floor-k">Reserved</div><div class="floor-v">{{ $summary['reserved'] }}</div><div class="floor-h">Held for upcoming guests</div></div>
    <div class="floor-stat"><div class="floor-k">Needs Cleaning</div><div class="floor-v">{{ $summary['needs_cleaning'] }}</div><div class="floor-h">Turn these before the next cover</div></div>
</div>

<div class="floor-shell">
    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="floor-card">
            <div class="floor-head">
                <div>
                    <div class="floor-title">Floor Board</div>
                    <div class="floor-sub">See table state, active orders, and fast-turn actions in one place.</div>
                </div>
            </div>
            <div class="floor-pad">
                <div class="floor-grid">
                    @foreach($tables as $table)
                        @php
                            $badge = match($table->status) {
                                'available' => 'fb-av',
                                'occupied' => 'fb-oc',
                                'reserved' => 'fb-rs',
                                default => 'fb-cl',
                            };
                        @endphp
                        <article class="table-tile">
                            <div class="table-top">
                                <div>
                                    <div class="table-name">{{ $table->name }}</div>
                                    <div class="table-meta">{{ $table->orders->count() }} active orders</div>
                                </div>
                                <span class="floor-badge {{ $badge }}">{{ str_replace('_', ' ', $table->status) }}</span>
                            </div>

                            @if($table->orders->isNotEmpty())
                                <div class="table-note">
                                    @foreach($table->orders->take(2) as $order)
                                        <div>{{ $order->order_number }} · {{ number_format((float) $order->subtotal, 2) }} KSh · {{ $order->items->sum('quantity') }} items</div>
                                    @endforeach
                                </div>
                            @endif

                            <div class="table-actions">
                                @foreach(['available' => 'Available', 'occupied' => 'Occupied', 'reserved' => 'Reserve', 'needs_cleaning' => 'Clean'] as $status => $label)
                                    <form method="post" action="{{ route('actions.table') }}">
                                        @csrf
                                        <input type="hidden" name="table_id" value="{{ $table->id }}">
                                        <input type="hidden" name="status" value="{{ $status }}">
                                        <button class="table-btn" type="submit">{{ $label }}</button>
                                    </form>
                                @endforeach
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="floor-card">
            <div class="floor-head">
                <div>
                    <div class="floor-title">Book Reservation</div>
                    <div class="floor-sub">Capture upcoming covers and pre-assign a table when needed.</div>
                </div>
            </div>
            <div class="floor-pad">
                <form method="post" action="{{ route('actions.reservation') }}">
                    @csrf
                    <div class="form-grid">
                        <div class="full"><div class="lbl">Guest Name</div><input class="inp" name="customer_name" required></div>
                        <div><div class="lbl">Phone</div><input class="inp" name="customer_phone"></div>
                        <div><div class="lbl">Covers</div><input class="inp" type="number" min="1" max="50" name="covers" value="2" required></div>
                        <div><div class="lbl">Table</div><select class="inp" name="restaurant_table_id"><option value="">Unassigned</option>@foreach($tables as $table)<option value="{{ $table->id }}">{{ $table->name }}</option>@endforeach</select></div>
                        <div><div class="lbl">Reserved For</div><input class="inp" type="datetime-local" name="reserved_for" required></div>
                        <div class="full"><div class="lbl">Notes</div><textarea class="inp" rows="3" name="notes" placeholder="Birthday, terrace request, allergy, or arrival note."></textarea></div>
                    </div>
                    <button class="btn btn-primary">Save Reservation</button>
                </form>
            </div>
        </div>

        <div class="floor-card">
            <div class="floor-head">
                <div>
                    <div class="floor-title">Upcoming Reservations</div>
                    <div class="floor-sub">Keep the next seatings visible to the host stand.</div>
                </div>
            </div>
            <div class="floor-pad">
                <div class="mini-list">
                    @forelse($reservations as $reservation)
                        <div class="mini-item">
                            <div>
                                <strong>{{ $reservation->customer_name }}</strong>
                                <span>{{ $reservation->reserved_for?->format('d M · H:i') }} · {{ $reservation->covers }} covers · {{ $reservation->table?->name ?? 'Unassigned table' }}</span>
                            </div>
                            <span class="floor-badge fb-rs">{{ $reservation->status }}</span>
                        </div>
                    @empty
                        <div style="color:var(--text3);font-size:12px">No upcoming reservations yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
