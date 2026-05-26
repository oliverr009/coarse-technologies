@extends('layouts.app', ['title' => 'Hotel / PMS'])
@section('content')
<style>
.pms-shell{display:flex;flex-direction:column;gap:16px}
.pms-hero{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:12px}
.pms-stat,.pms-card{border:1px solid var(--border);border-radius:20px;background:linear-gradient(180deg,rgba(43,37,38,.96),rgba(31,26,27,.98))}
.pms-stat{padding:16px 18px}.pms-k{font-size:11px;color:var(--text3);text-transform:uppercase;letter-spacing:.08em}.pms-v{margin-top:8px;font-size:24px;font-weight:800;color:var(--text);font-family:'Space Mono',monospace}.pms-h{margin-top:6px;font-size:12px;color:var(--text2);line-height:1.45}
.pms-grid{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(380px,.85fr);gap:16px}.pms-stack{display:flex;flex-direction:column;gap:16px}
.pms-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:16px 18px;border-bottom:1px solid var(--border)}.pms-title{font-size:13px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}.pms-sub{font-size:12px;color:var(--text3);margin-top:4px;line-height:1.45}.pms-pad{padding:16px 18px}
.pms-form{display:flex;flex-direction:column;gap:12px}.pms-form-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}.pms-full{grid-column:1/-1}
.pms-mini-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.pms-room-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.room-card,.pms-panel{border:1px solid var(--border);border-radius:16px;background:rgba(255,255,255,.025);padding:14px}.room-top,.pms-row{display:flex;justify-content:space-between;gap:10px;align-items:flex-start}.room-no{font-size:21px;font-weight:800;color:var(--text);font-family:'Space Mono',monospace}.room-type,.pms-muted{font-size:12px;color:var(--text3);line-height:1.45}.room-meta{margin-top:12px;display:flex;flex-direction:column;gap:7px}.room-line{display:flex;justify-content:space-between;gap:10px;font-size:12px;color:var(--text2)}.room-line strong{text-align:right;color:var(--text)}
.pms-badge{display:inline-flex;align-items:center;padding:4px 8px;border-radius:999px;font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}.rb-occ{background:rgba(62,207,142,.12);color:var(--green)}.rb-rsv{background:rgba(255,191,71,.12);color:var(--gold)}.rb-clean{background:rgba(40,188,238,.12);color:var(--blue)}.rb-dirty{background:rgba(248,113,113,.12);color:var(--red)}.rb-oos{background:rgba(148,163,184,.18);color:#cbd5e1}
.pms-list{display:flex;flex-direction:column;gap:10px}.pms-list-item{border:1px solid var(--border);border-radius:14px;background:rgba(255,255,255,.025);padding:12px}.pms-list-item strong{display:block;font-size:13px;color:var(--text)}.pms-list-item span{display:block;margin-top:4px;font-size:11px;color:var(--text3);line-height:1.45}
.pms-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}.pms-inline{display:grid;grid-template-columns:1fr 1fr auto;gap:8px;margin-top:10px}.pms-table{overflow:hidden;border:1px solid var(--border);border-radius:16px}.pms-table table{width:100%;border-collapse:collapse}.pms-table th,.pms-table td{padding:11px 12px;border-bottom:1px solid var(--border);font-size:12px;text-align:left;vertical-align:top}.pms-table th{font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:var(--text3);background:rgba(255,255,255,.02)}.pms-table tr:last-child td{border-bottom:none}
.pms-note{padding:12px;border-radius:14px;background:rgba(40,188,238,.08);border:1px solid rgba(40,188,238,.16);font-size:12px;color:var(--text2);line-height:1.55}
.pms-drop{display:block}.pms-drop>summary{list-style:none;cursor:pointer}.pms-drop>summary::-webkit-details-marker{display:none}.pms-summary{transition:background .16s ease}.pms-summary:hover{background:rgba(255,255,255,.025)}.pms-toggle{display:inline-flex;align-items:center;gap:7px;padding:8px 12px;border-radius:12px;background:rgba(40,188,238,.12);border:1px solid rgba(40,188,238,.22);color:var(--blue);font-size:12px;font-weight:800}.pms-toggle i{font-size:16px}.pms-drop[open] .pms-toggle i{transform:rotate(180deg)}.room-manage{margin-top:12px}.room-manage>summary{list-style:none;cursor:pointer}.room-manage>summary::-webkit-details-marker{display:none}.room-manage-btn{width:100%;height:38px;border-radius:12px;border:1px solid var(--border);background:rgba(255,255,255,.035);color:var(--text2);display:flex;align-items:center;justify-content:center;gap:7px;font-size:12px;font-weight:800}.room-manage[open] .room-manage-btn i{transform:rotate(180deg)}
@media(max-width:1480px){.pms-hero{grid-template-columns:repeat(3,1fr)}.pms-room-grid{grid-template-columns:repeat(2,1fr)}}@media(max-width:1180px){.pms-grid{grid-template-columns:1fr}.pms-form-grid,.pms-mini-grid{grid-template-columns:repeat(2,1fr)}}@media(max-width:760px){.pms-hero,.pms-form-grid,.pms-mini-grid,.pms-room-grid,.pms-inline{grid-template-columns:1fr}}
</style>

@php
    $statusClass = fn ($status) => match($status) {
        'occupied' => 'rb-occ',
        'reserved' => 'rb-rsv',
        'vacant_clean' => 'rb-clean',
        'dirty' => 'rb-dirty',
        default => 'rb-oos',
    };
    $cleanRooms = $rooms->where('status', 'vacant_clean');
@endphp

<div class="pms-shell">
    <div class="pms-hero">
        <div class="pms-stat"><div class="pms-k">Arrivals Today</div><div class="pms-v">{{ $summary['arrivals_today'] }}</div><div class="pms-h">Confirmed guests expected today.</div></div>
        <div class="pms-stat"><div class="pms-k">Departures Today</div><div class="pms-v">{{ $summary['departures_today'] }}</div><div class="pms-h">Open folios due for check-out.</div></div>
        <div class="pms-stat"><div class="pms-k">Rooms Ready</div><div class="pms-v">{{ $summary['inventory_ready'] }}</div><div class="pms-h">Vacant clean rooms available now.</div></div>
        <div class="pms-stat"><div class="pms-k">Occupied</div><div class="pms-v">{{ $summary['occupied'] }}</div><div class="pms-h">Current in-house rooms.</div></div>
        <div class="pms-stat"><div class="pms-k">Dirty</div><div class="pms-v">{{ $summary['dirty'] }}</div><div class="pms-h">Housekeeping turnover queue.</div></div>
        <div class="pms-stat"><div class="pms-k">Open Balance</div><div class="pms-v">{{ number_format($summary['open_balance'], 0) }}</div><div class="pms-h">KES pending across open folios.</div></div>
    </div>

    <div class="pms-grid">
        <div class="pms-stack">
            <div class="pms-card">
                <details class="pms-drop">
                    <summary class="pms-head pms-summary">
                        <div><div class="pms-title">Reservation & Booking Engine</div><div class="pms-sub">Create advance reservations only when the front desk needs the form.</div></div>
                        <span class="pms-toggle"><i class="ti ti-chevron-down" aria-hidden="true"></i> Book Reservation</span>
                    </summary>
                    <div class="pms-pad">
                        <form method="post" action="{{ route('actions.hotel.reservation') }}" class="pms-form">
                            @csrf
                            <div class="pms-form-grid">
                                <div><div class="lbl">Guest Name</div><input class="inp" name="guest_name" required></div>
                                <div><div class="lbl">Phone</div><input class="inp" name="guest_phone"></div>
                                <div><div class="lbl">Email</div><input class="inp" type="email" name="guest_email"></div>
                                <div><div class="lbl">Room Type</div><select class="inp" name="room_type_id"><option value="">Select type</option>@foreach($roomTypes as $type)<option value="{{ $type->id }}">{{ $type->name }} · KES {{ number_format((float) $type->base_rate, 0) }}</option>@endforeach</select></div>
                                <div><div class="lbl">Assign Room</div><select class="inp" name="hotel_room_id"><option value="">Assign later</option>@foreach($cleanRooms as $room)<option value="{{ $room->id }}">{{ $room->room_number }} · {{ $room->roomType->name ?? 'Room' }}</option>@endforeach</select></div>
                                <div><div class="lbl">Guests</div><input class="inp" type="number" min="1" max="20" name="guests" value="1" required></div>
                                <div><div class="lbl">Check-in</div><input class="inp" type="date" name="check_in_date" value="{{ now()->toDateString() }}" required></div>
                                <div><div class="lbl">Check-out</div><input class="inp" type="date" name="check_out_date" value="{{ now()->addDay()->toDateString() }}" required></div>
                                <div><div class="lbl">Rate Plan</div><input class="inp" name="rate_plan" value="rack"></div>
                                <div><div class="lbl">Nightly Rate</div><input class="inp" type="number" step="0.01" min="0" name="nightly_rate" required></div>
                                <div><div class="lbl">Deposit</div><input class="inp" type="number" step="0.01" min="0" name="deposit_amount" value="0"></div>
                                <div class="pms-full"><div class="lbl">Notes</div><input class="inp" name="notes" placeholder="Arrival time, preference, source, special requests"></div>
                            </div>
                            <button class="btn btn-primary">Confirm Reservation</button>
                        </form>
                    </div>
                </details>
            </div>

            <div class="pms-card">
                <div class="pms-head">
                    <div><div class="pms-title">Front Desk - In House</div><div class="pms-sub">Open folios, balances, expected departures, and check-out controls.</div></div>
                    <span class="pms-badge rb-occ">Live</span>
                </div>
                <div class="pms-pad">
                    <div class="pms-list">
                        @forelse($openFolios as $folio)
                            <div class="pms-list-item">
                                <div class="pms-row">
                                    <div>
                                        <strong>{{ $folio->room->room_number ?? 'Room' }} · {{ $folio->guest_name }}</strong>
                                        <span>{{ $folio->checked_in_at?->format('d M H:i') }} to {{ $folio->expected_checkout_at?->format('d M') }} · KES {{ number_format((float) $folio->balance, 2) }} balance</span>
                                    </div>
                                    <span class="pms-badge rb-occ">In House</span>
                                </div>
                                <form method="post" action="{{ route('actions.hotel.check-out') }}" class="pms-inline">
                                    @csrf
                                    <input type="hidden" name="hotel_folio_id" value="{{ $folio->id }}">
                                    <input class="inp" type="number" step="0.01" min="0" name="payment_amount" value="{{ max(0, (float) $folio->balance) }}" placeholder="Payment">
                                    <select class="inp" name="payment_method"><option value="cash">Cash</option><option value="mpesa">M-Pesa</option><option value="card">Card</option><option value="bank">Bank</option><option value="credit">Credit</option></select>
                                    <button class="btn">Check Out</button>
                                </form>
                            </div>
                        @empty
                            <div class="pms-note">No in-house guests yet. Check in a reservation or walk-in guest to open a folio.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="pms-card">
                <div class="pms-head">
                    <div><div class="pms-title">Room Inventory & Housekeeping</div><div class="pms-sub">Update room state, housekeeping state, active guest, rate, and balance from one operational grid.</div></div>
                </div>
                <div class="pms-pad">
                    <div class="pms-room-grid">
                        @foreach($rooms as $room)
                            <div class="room-card">
                                <div class="room-top">
                                    <div><div class="room-no">{{ $room->room_number }}</div><div class="room-type">{{ $room->roomType->name ?? 'Room Type Pending' }}</div></div>
                                    <span class="pms-badge {{ $statusClass($room->status) }}">{{ str_replace('_', ' ', $room->status) }}</span>
                                </div>
                                <div class="room-meta">
                                    <div class="room-line"><span>Floor</span><strong>{{ $room->floor ?: 'Unmapped' }}</strong></div>
                                    <div class="room-line"><span>Guest</span><strong>{{ $room->active_guest_name ?: 'No active guest' }}</strong></div>
                                    <div class="room-line"><span>Rate</span><strong>KES {{ number_format((float) ($room->current_rate ?? $room->roomType->base_rate ?? 0), 0) }}</strong></div>
                                    <div class="room-line"><span>Balance</span><strong>KES {{ number_format((float) ($room->active_folio_balance ?? 0), 0) }}</strong></div>
                                </div>
                                <details class="room-manage">
                                    <summary class="room-manage-btn"><i class="ti ti-chevron-down" aria-hidden="true"></i> Manage Room</summary>
                                    <form method="post" action="{{ route('actions.hotel.room-status') }}" class="pms-form" style="margin-top:12px">
                                        @csrf
                                        <input type="hidden" name="hotel_room_id" value="{{ $room->id }}">
                                        <select class="inp" name="status">
                                            @foreach(['vacant_clean','occupied','reserved','dirty','out_of_order'] as $status)<option value="{{ $status }}" @selected($room->status === $status)>{{ str_replace('_', ' ', $status) }}</option>@endforeach
                                        </select>
                                        <select class="inp" name="housekeeping_status">
                                            @foreach(['clean','dirty','inspected','out_of_order'] as $status)<option value="{{ $status }}" @selected($room->housekeeping_status === $status)>{{ str_replace('_', ' ', $status) }}</option>@endforeach
                                        </select>
                                        <input class="inp" name="notes" value="{{ $room->notes }}" placeholder="Room note">
                                        <button class="btn">Update Room</button>
                                    </form>
                                </details>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="pms-stack">
            <div class="pms-card">
                <div class="pms-head"><div><div class="pms-title">Arrivals / Check-in</div><div class="pms-sub">Reservations due soon and walk-in check-in controls.</div></div></div>
                <div class="pms-pad">
                    <div class="pms-list">
                        @forelse($checkInQueue as $reservation)
                            <div class="pms-list-item">
                                <strong>{{ $reservation->guest_name }}</strong>
                                <span>{{ $reservation->check_in_date?->format('d M') }} to {{ $reservation->check_out_date?->format('d M') }} · {{ $reservation->roomType->name ?? 'Room type pending' }}</span>
                                <form method="post" action="{{ route('actions.hotel.check-in') }}" class="pms-form" style="margin-top:10px">
                                    @csrf
                                    <input type="hidden" name="hotel_reservation_id" value="{{ $reservation->id }}">
                                    <select class="inp" name="hotel_room_id" required>
                                        <option value="">Select room</option>
                                        @foreach($rooms->whereIn('status', ['vacant_clean', 'reserved']) as $room)<option value="{{ $room->id }}" @selected($reservation->hotel_room_id === $room->id)>{{ $room->room_number }} · {{ $room->roomType->name ?? 'Room' }}</option>@endforeach
                                    </select>
                                    <input class="inp" type="number" step="0.01" min="0" name="deposit_amount" value="{{ $reservation->deposit_amount }}">
                                    <button class="btn btn-primary">Check In</button>
                                </form>
                            </div>
                        @empty
                            <div class="pms-note">No arrivals in the next seven days.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="pms-card">
                <details class="pms-drop">
                    <summary class="pms-head pms-summary">
                        <div><div class="pms-title">Walk-in Check-in</div><div class="pms-sub">Keep the walk-in form tucked away until a guest is at the desk.</div></div>
                        <span class="pms-toggle"><i class="ti ti-chevron-down" aria-hidden="true"></i> Walk-in</span>
                    </summary>
                    <div class="pms-pad">
                        <form method="post" action="{{ route('actions.hotel.check-in') }}" class="pms-form">
                            @csrf
                            <div class="pms-mini-grid">
                                <div><div class="lbl">Guest Name</div><input class="inp" name="guest_name" required></div>
                                <div><div class="lbl">Phone</div><input class="inp" name="guest_phone"></div>
                                <div><div class="lbl">Room</div><select class="inp" name="hotel_room_id" required><option value="">Select clean room</option>@foreach($cleanRooms as $room)<option value="{{ $room->id }}">{{ $room->room_number }} · {{ $room->roomType->name ?? 'Room' }}</option>@endforeach</select></div>
                                <div><div class="lbl">Checkout Date</div><input class="inp" type="date" name="check_out_date" value="{{ now()->addDay()->toDateString() }}" required></div>
                                <div><div class="lbl">Room Rate</div><input class="inp" type="number" step="0.01" min="0" name="room_rate"></div>
                                <div><div class="lbl">Payment / Deposit</div><input class="inp" type="number" step="0.01" min="0" name="deposit_amount" value="0"></div>
                            </div>
                            <button class="btn btn-primary">Check In Walk-in</button>
                        </form>
                    </div>
                </details>
            </div>

            <div class="pms-card">
                <div class="pms-head"><div><div class="pms-title">Room Availability</div><div class="pms-sub">Availability by room type, based on live room status.</div></div></div>
                <div class="pms-pad">
                    <div class="pms-table">
                        <table>
                            <thead><tr><th>Room Type</th><th>Available</th><th>Occupied</th><th>Reserved</th><th>Dirty</th><th>Rate</th></tr></thead>
                            <tbody>
                                @foreach($availableByType as $type)
                                    <tr><td>{{ $type->name }}</td><td>{{ $type->available }}</td><td>{{ $type->occupied }}</td><td>{{ $type->reserved }}</td><td>{{ $type->dirty }}</td><td>KES {{ number_format((float) $type->base_rate, 0) }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="pms-card">
                <div class="pms-head"><div><div class="pms-title">Guest Profiles & Recent Folios</div><div class="pms-sub">Guest identities, reservation history, and recently touched folios.</div></div></div>
                <div class="pms-pad">
                    <div class="pms-list">
                        @foreach($guestProfiles->take(6) as $guest)
                            <div class="pms-list-item"><strong>{{ $guest->name }}</strong><span>{{ $guest->phone ?: 'Phone pending' }} · {{ $guest->activity_hint }}</span></div>
                        @endforeach
                        @foreach($recentFolios->take(4) as $folio)
                            <div class="pms-list-item"><strong>{{ $folio->guest_name }} · Room {{ $folio->room->room_number ?? '-' }}</strong><span>{{ ucfirst($folio->status) }} folio · KES {{ number_format((float) $folio->balance, 2) }} balance</span></div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
