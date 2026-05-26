@extends('layouts.app', ['title' => 'Hotel / PMS'])
@section('content')
<style>
.pms-shell{display:flex;flex-direction:column;gap:14px}
.pms-stats{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:10px}
.pms-stat,.pms-card,.pms-action-menu{border:1px solid var(--border);background:linear-gradient(180deg,rgba(43,37,38,.96),rgba(31,26,27,.98));border-radius:18px}
.pms-stat{padding:14px 16px}.pms-k{font-size:10px;color:var(--text3);text-transform:uppercase;letter-spacing:.08em}.pms-v{margin-top:7px;font-size:22px;font-weight:800;color:var(--text);font-family:'Space Mono',monospace}.pms-h{margin-top:5px;font-size:11px;color:var(--text2);line-height:1.4}
.pms-actions-bar{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px}.pms-action{position:relative}.pms-action>summary{list-style:none}.pms-action>summary::-webkit-details-marker{display:none}
.pms-action-btn{width:100%;min-height:58px;border:1px solid var(--border);border-radius:16px;background:rgba(255,255,255,.035);color:var(--text);display:flex;align-items:center;gap:12px;padding:10px 14px;cursor:pointer;transition:.16s ease}.pms-action-btn:hover{border-color:rgba(40,188,238,.32);background:rgba(40,188,238,.08);transform:translateY(-1px)}.pms-action-btn i{width:34px;height:34px;border-radius:12px;background:rgba(40,188,238,.12);color:var(--blue);display:grid;place-items:center;font-size:18px}.pms-action-btn strong{display:block;font-size:13px}.pms-action-btn span{display:block;font-size:11px;color:var(--text3);margin-top:2px}
.pms-action-menu{position:absolute;z-index:20;top:66px;left:0;width:min(760px,calc(100vw - 120px));padding:16px;box-shadow:0 24px 70px rgba(0,0,0,.34)}.pms-action:nth-child(n+4) .pms-action-menu{left:auto;right:0}
.pms-main{display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:14px;align-items:start}.pms-card-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:15px 16px;border-bottom:1px solid var(--border)}.pms-title{font-size:13px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}.pms-sub{font-size:12px;color:var(--text3);margin-top:4px;line-height:1.45}.pms-pad{padding:15px 16px}
.pms-form{display:flex;flex-direction:column;gap:10px}.pms-form-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}.pms-mini-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.pms-full{grid-column:1/-1}
.room-rack-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px}.room-rack-title{font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--text3)}.rack-legend{display:flex;gap:12px;flex-wrap:wrap;font-size:11px;color:var(--text2)}.rack-legend span{display:flex;align-items:center;gap:5px}.dot{width:9px;height:9px;border-radius:50%}
.floor-label{font-size:11px;font-weight:800;color:var(--text3);text-transform:uppercase;letter-spacing:.08em;margin:14px 0 8px}.room-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(132px,1fr));gap:10px}.room-tile{position:relative}.room-tile>summary{list-style:none}.room-tile>summary::-webkit-details-marker{display:none}
.room-btn{min-height:122px;width:100%;border:2px solid var(--border);border-radius:17px;background:rgba(255,255,255,.03);color:var(--text);cursor:pointer;padding:12px;text-align:left;transition:.16s ease;display:flex;flex-direction:column;justify-content:space-between;box-shadow:inset 0 0 0 1px rgba(255,255,255,.03)}.room-btn:hover{transform:translateY(-3px);box-shadow:0 16px 34px rgba(0,0,0,.18),inset 0 0 0 1px rgba(255,255,255,.06)}.room-tile[open] .room-btn{border-color:rgba(40,188,238,.65);box-shadow:0 0 0 3px rgba(40,188,238,.16)}
.room-btn.st-vacant_clean{background:linear-gradient(145deg,rgba(40,188,238,.24),rgba(40,188,238,.08));border-color:rgba(40,188,238,.62)}.room-btn.st-occupied{background:linear-gradient(145deg,rgba(62,207,142,.26),rgba(62,207,142,.08));border-color:rgba(62,207,142,.62)}.room-btn.st-reserved{background:linear-gradient(145deg,rgba(249,181,28,.28),rgba(249,181,28,.09));border-color:rgba(249,181,28,.68)}.room-btn.st-dirty{background:linear-gradient(145deg,rgba(248,113,113,.28),rgba(248,113,113,.09));border-color:rgba(248,113,113,.70)}.room-btn.st-out_of_order{background:linear-gradient(145deg,rgba(148,163,184,.30),rgba(148,163,184,.10));border-color:rgba(148,163,184,.66)}
.room-num{font-size:24px;font-weight:800;font-family:'Space Mono',monospace}.room-type{font-size:11px;color:var(--text3);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.room-guest{font-size:12px;color:var(--text2);margin-top:8px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.room-rate{font-size:11px;color:var(--text3);margin-top:2px}.room-status-line{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:10px}
.badge{display:inline-flex;align-items:center;padding:5px 9px;border-radius:999px;font-size:9px;font-weight:900;text-transform:uppercase;letter-spacing:.05em;border:1px solid transparent}.is-occ{background:rgba(62,207,142,.22);color:var(--green);border-color:rgba(62,207,142,.42)}.is-rsv{background:rgba(255,191,71,.24);color:var(--gold);border-color:rgba(255,191,71,.45)}.is-clean{background:rgba(40,188,238,.24);color:var(--blue);border-color:rgba(40,188,238,.45)}.is-dirty{background:rgba(248,113,113,.24);color:var(--red);border-color:rgba(248,113,113,.45)}.is-oos{background:rgba(148,163,184,.26);color:#cbd5e1;border-color:rgba(148,163,184,.45)}
.room-pop{position:absolute;z-index:12;top:126px;left:0;width:310px;border:1px solid var(--border);border-radius:16px;background:linear-gradient(180deg,rgba(43,37,38,.98),rgba(31,26,27,.99));padding:12px;box-shadow:0 24px 70px rgba(0,0,0,.35)}.room-pop.right-edge{left:auto;right:0}.room-pop-title{font-size:12px;font-weight:800;color:var(--text);margin-bottom:9px}.room-pop-row{display:flex;justify-content:space-between;gap:10px;font-size:12px;color:var(--text2);padding:6px 0;border-bottom:1px solid var(--border)}.room-pop-row strong{color:var(--text);text-align:right}.room-pop-actions{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:10px}
.pms-list{display:flex;flex-direction:column;gap:10px}.pms-list-item{border:1px solid var(--border);border-radius:14px;background:rgba(255,255,255,.025);padding:12px}.pms-list-item strong{display:block;font-size:13px;color:var(--text)}.pms-list-item span{display:block;font-size:11px;color:var(--text3);line-height:1.45;margin-top:4px}.pms-inline{display:grid;grid-template-columns:1fr 1fr auto;gap:8px;margin-top:10px}.pms-note{padding:12px;border-radius:14px;background:rgba(40,188,238,.08);border:1px solid rgba(40,188,238,.16);font-size:12px;color:var(--text2);line-height:1.5}
.pms-table{overflow:hidden;border:1px solid var(--border);border-radius:14px}.pms-table table{width:100%;border-collapse:collapse}.pms-table th,.pms-table td{padding:10px 11px;border-bottom:1px solid var(--border);font-size:12px;text-align:left}.pms-table th{font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:var(--text3);background:rgba(255,255,255,.02)}.pms-table tr:last-child td{border-bottom:none}
.activity-list{display:flex;flex-direction:column;gap:9px}.activity{display:flex;gap:10px;padding:10px;border:1px solid var(--border);border-radius:13px;background:rgba(255,255,255,.025)}.activity i{width:30px;height:30px;border-radius:10px;background:rgba(40,188,238,.12);color:var(--blue);display:grid;place-items:center;flex:0 0 auto}.activity strong{display:block;font-size:12px;color:var(--text)}.activity span{display:block;font-size:11px;color:var(--text3);margin-top:3px;line-height:1.4}.setup-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}.setup-panel{border:1px solid var(--border);border-radius:16px;padding:14px;background:rgba(255,255,255,.025)}.setup-title{font-size:12px;font-weight:900;letter-spacing:.06em;text-transform:uppercase;margin-bottom:10px;color:var(--text)}
html[data-theme=light] .pms-stat,html[data-theme=light] .pms-card,html[data-theme=light] .pms-action-menu,html[data-theme=light] .room-pop{background:linear-gradient(180deg,rgba(255,255,255,.97),rgba(245,248,252,.99));box-shadow:0 16px 46px rgba(27,33,83,.08)}html[data-theme=light] .room-btn,html[data-theme=light] .pms-action-btn,html[data-theme=light] .pms-list-item,html[data-theme=light] .activity{background:rgba(27,33,83,.035)}
@media(max-width:1320px){.pms-stats{grid-template-columns:repeat(3,1fr)}.pms-actions-bar{grid-template-columns:repeat(3,1fr)}.pms-main{grid-template-columns:1fr}.pms-action-menu{width:calc(100vw - 120px);left:0!important;right:auto!important}}@media(max-width:980px){.setup-grid{grid-template-columns:1fr}}@media(max-width:820px){.pms-stats,.pms-actions-bar,.pms-form-grid,.pms-mini-grid,.pms-inline{grid-template-columns:1fr}.room-pop{position:static;width:100%;margin-top:10px}.pms-action-menu{position:static;width:100%;margin-top:8px}}
</style>

@php
    $tone = fn ($status) => match($status) {
        'occupied' => 'is-occ',
        'reserved' => 'is-rsv',
        'vacant_clean' => 'is-clean',
        'dirty' => 'is-dirty',
        default => 'is-oos',
    };
    $statusLabel = fn ($status) => str_replace('_', ' ', $status);
    $cleanRooms = $rooms->where('status', 'vacant_clean');
    $dirtyRooms = $rooms->whereIn('status', ['dirty', 'out_of_order']);
    $roomsByFloor = $rooms->groupBy(fn ($room) => $room->floor ?: 'Unmapped');
@endphp

<div class="pms-shell">
    <div class="pms-stats">
        <div class="pms-stat"><div class="pms-k">Arrivals</div><div class="pms-v">{{ $summary['arrivals_today'] }}</div><div class="pms-h">Expected today</div></div>
        <div class="pms-stat"><div class="pms-k">Departures</div><div class="pms-v">{{ $summary['departures_today'] }}</div><div class="pms-h">Due today</div></div>
        <div class="pms-stat"><div class="pms-k">Vacant Clean</div><div class="pms-v">{{ $summary['inventory_ready'] }}</div><div class="pms-h">Ready rooms</div></div>
        <div class="pms-stat"><div class="pms-k">Occupied</div><div class="pms-v">{{ $summary['occupied'] }}</div><div class="pms-h">In-house rooms</div></div>
        <div class="pms-stat"><div class="pms-k">Dirty / OOO</div><div class="pms-v">{{ $summary['dirty'] + $summary['out_of_order'] }}</div><div class="pms-h">Needs attention</div></div>
        <div class="pms-stat"><div class="pms-k">Open Balance</div><div class="pms-v">{{ number_format($summary['open_balance'], 0) }}</div><div class="pms-h">KES on folios</div></div>
    </div>

    <div class="pms-actions-bar">
        <details class="pms-action">
            <summary class="pms-action-btn"><i class="ti ti-calendar-plus"></i><div><strong>Reservations</strong><span>New booking</span></div></summary>
            <div class="pms-action-menu">
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
                        <div><div class="lbl">Nightly Rate</div><input class="inp" type="number" step="0.01" min="0" name="nightly_rate" required></div>
                        <div><div class="lbl">Deposit</div><input class="inp" type="number" step="0.01" min="0" name="deposit_amount" value="0"></div>
                        <div><div class="lbl">Rate Plan</div><input class="inp" name="rate_plan" value="rack"></div>
                        <div class="pms-full"><div class="lbl">Notes</div><input class="inp" name="notes" placeholder="Arrival time, preference, source, special requests"></div>
                    </div>
                    <button class="btn btn-primary">Confirm Reservation</button>
                </form>
            </div>
        </details>

        <details class="pms-action">
            <summary class="pms-action-btn"><i class="ti ti-login"></i><div><strong>Check In</strong><span>Arrivals / walk-in</span></div></summary>
            <div class="pms-action-menu">
                <div class="pms-mini-grid">
                    <div>
                        <div class="pms-title" style="margin-bottom:10px">Arrivals Queue</div>
                        <div class="pms-list">
                            @forelse($checkInQueue as $reservation)
                                <div class="pms-list-item">
                                    <strong>{{ $reservation->guest_name }}</strong>
                                    <span>{{ $reservation->check_in_date?->format('d M') }} to {{ $reservation->check_out_date?->format('d M') }} · {{ $reservation->roomType->name ?? 'Room type pending' }}</span>
                                    <form method="post" action="{{ route('actions.hotel.check-in') }}" class="pms-form" style="margin-top:10px">
                                        @csrf
                                        <input type="hidden" name="hotel_reservation_id" value="{{ $reservation->id }}">
                                        <select class="inp" name="hotel_room_id" required><option value="">Select room</option>@foreach($rooms->whereIn('status', ['vacant_clean', 'reserved']) as $room)<option value="{{ $room->id }}" @selected($reservation->hotel_room_id === $room->id)>{{ $room->room_number }} · {{ $room->roomType->name ?? 'Room' }}</option>@endforeach</select>
                                        <input class="inp" type="number" step="0.01" min="0" name="deposit_amount" value="{{ $reservation->deposit_amount }}">
                                        <button class="btn btn-primary">Check In</button>
                                    </form>
                                </div>
                            @empty
                                <div class="pms-note">No arrivals in the next seven days.</div>
                            @endforelse
                        </div>
                    </div>
                    <form method="post" action="{{ route('actions.hotel.check-in') }}" class="pms-form">
                        @csrf
                        <div class="pms-title">Walk-in Guest</div>
                        <div><div class="lbl">Guest Name</div><input class="inp" name="guest_name" required></div>
                        <div><div class="lbl">Phone</div><input class="inp" name="guest_phone"></div>
                        <div><div class="lbl">Room</div><select class="inp" name="hotel_room_id" required><option value="">Select clean room</option>@foreach($cleanRooms as $room)<option value="{{ $room->id }}">{{ $room->room_number }} · {{ $room->roomType->name ?? 'Room' }}</option>@endforeach</select></div>
                        <div><div class="lbl">Checkout Date</div><input class="inp" type="date" name="check_out_date" value="{{ now()->addDay()->toDateString() }}" required></div>
                        <div><div class="lbl">Room Rate</div><input class="inp" type="number" step="0.01" min="0" name="room_rate"></div>
                        <div><div class="lbl">Payment / Deposit</div><input class="inp" type="number" step="0.01" min="0" name="deposit_amount" value="0"></div>
                        <button class="btn btn-primary">Check In Walk-in</button>
                    </form>
                </div>
            </div>
        </details>

        <details class="pms-action">
            <summary class="pms-action-btn"><i class="ti ti-spray"></i><div><strong>Housekeeping</strong><span>Clean / inspect</span></div></summary>
            <div class="pms-action-menu">
                <div class="pms-list">
                    @forelse($dirtyRooms as $room)
                        <div class="pms-list-item">
                            <strong>Room {{ $room->room_number }} · {{ $statusLabel($room->status) }}</strong>
                            <span>{{ $room->roomType->name ?? 'Room' }} · Housekeeping: {{ $statusLabel($room->housekeeping_status) }}</span>
                            <form method="post" action="{{ route('actions.hotel.room-status') }}" class="pms-inline">
                                @csrf
                                <input type="hidden" name="hotel_room_id" value="{{ $room->id }}">
                                <select class="inp" name="status"><option value="vacant_clean">vacant clean</option><option value="dirty" @selected($room->status === 'dirty')>dirty</option><option value="out_of_order" @selected($room->status === 'out_of_order')>out of order</option></select>
                                <select class="inp" name="housekeeping_status"><option value="clean">clean</option><option value="inspected">inspected</option><option value="dirty" @selected($room->housekeeping_status === 'dirty')>dirty</option><option value="out_of_order" @selected($room->housekeeping_status === 'out_of_order')>out of order</option></select>
                                <button class="btn">Update</button>
                            </form>
                        </div>
                    @empty
                        <div class="pms-note">No rooms currently waiting for housekeeping.</div>
                    @endforelse
                </div>
            </div>
        </details>

        <details class="pms-action">
            <summary class="pms-action-btn"><i class="ti ti-building-cog"></i><div><strong>Management</strong><span>Rooms setup</span></div></summary>
            <div class="pms-action-menu">
                <div class="setup-grid">
                    <form method="post" action="{{ route('actions.hotel.room-type') }}" class="setup-panel pms-form">
                        @csrf
                        <div class="setup-title">Room Types & Rates</div>
                        <div class="pms-mini-grid">
                            <div><div class="lbl">Type Name</div><input class="inp" name="name" placeholder="Deluxe Twin" required></div>
                            <div><div class="lbl">Code</div><input class="inp" name="code" placeholder="DLX-T"></div>
                            <div><div class="lbl">Base Rate</div><input class="inp" type="number" step="0.01" min="0" name="base_rate" required></div>
                            <div><div class="lbl">Max Occupancy</div><input class="inp" type="number" min="1" max="20" name="max_occupancy" value="2" required></div>
                            <div class="pms-full"><div class="lbl">Description</div><input class="inp" name="description" placeholder="Bed setup, amenities, selling notes"></div>
                        </div>
                        <button class="btn btn-primary">Save Room Type</button>
                    </form>

                    <form method="post" action="{{ route('actions.hotel.room') }}" class="setup-panel pms-form">
                        @csrf
                        <div class="setup-title">Room Inventory</div>
                        <div class="pms-mini-grid">
                            <div><div class="lbl">Room Number</div><input class="inp" name="room_number" placeholder="205" required></div>
                            <div><div class="lbl">Floor</div><input class="inp" name="floor" placeholder="2"></div>
                            <div><div class="lbl">Room Type</div><select class="inp" name="room_type_id" required><option value="">Select type</option>@foreach($roomTypes as $type)<option value="{{ $type->id }}">{{ $type->name }}</option>@endforeach</select></div>
                            <div><div class="lbl">Rate Override</div><input class="inp" type="number" step="0.01" min="0" name="current_rate" placeholder="Optional"></div>
                            <div><div class="lbl">Room Status</div><select class="inp" name="status"><option value="vacant_clean">Vacant clean</option><option value="dirty">Dirty</option><option value="reserved">Reserved</option><option value="out_of_order">Out of order</option><option value="occupied">Occupied</option></select></div>
                            <div><div class="lbl">Housekeeping</div><select class="inp" name="housekeeping_status"><option value="clean">Clean</option><option value="inspected">Inspected</option><option value="dirty">Dirty</option><option value="out_of_order">Out of order</option></select></div>
                            <div class="pms-full"><div class="lbl">Notes</div><input class="inp" name="notes" placeholder="Maintenance, view, bed setup, accessibility"></div>
                        </div>
                        <button class="btn btn-primary">Save Room</button>
                    </form>
                </div>

                <div class="pms-table" style="margin-top:14px">
                    <table>
                        <thead><tr><th>Room Type</th><th>Code</th><th>Base Rate</th><th>Occupancy</th><th>Rooms</th></tr></thead>
                        <tbody>
                            @foreach($roomTypes as $type)
                                <tr><td>{{ $type->name }}</td><td>{{ $type->code ?: '-' }}</td><td>KES {{ number_format((float) $type->base_rate, 0) }}</td><td>{{ $type->max_occupancy }} pax</td><td>{{ $type->rooms_count }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </details>

        <details class="pms-action">
            <summary class="pms-action-btn"><i class="ti ti-chart-bar"></i><div><strong>Reports</strong><span>Availability / folios</span></div></summary>
            <div class="pms-action-menu">
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
                <div class="pms-mini-grid" style="margin-top:10px">
                    <div class="pms-note">Open folio balance: KES {{ number_format($summary['open_balance'], 2) }}</div>
                    <div class="pms-note">Reservation feed: {{ $summary['reservation_feed'] }} active reservation record(s).</div>
                </div>
            </div>
        </details>
    </div>

    <div class="pms-main">
        <section class="pms-card">
            <div class="pms-card-head">
                <div><div class="pms-title">Room Rack</div><div class="pms-sub">Tap a room to check in, check out, or update housekeeping without opening a separate page.</div></div>
            </div>
            <div class="pms-pad">
                <div class="room-rack-head">
                    <div class="room-rack-title">Live Room Tiles</div>
                    <div class="rack-legend">
                    <span><i class="dot" style="background:var(--blue)"></i> Vacant clean</span>
                    <span><i class="dot" style="background:var(--green)"></i> Occupied</span>
                    <span><i class="dot" style="background:var(--gold)"></i> Reserved</span>
                    <span><i class="dot" style="background:var(--red)"></i> Dirty / blocked</span>
                    </div>
                </div>
                @foreach($roomsByFloor as $floor => $floorRooms)
                    <div class="floor-label">Floor {{ $floor }}</div>
                    <div class="room-grid">
                        @foreach($floorRooms as $room)
                            @php $folio = $room->folios->first(); @endphp
                            <details class="room-tile">
                                <summary class="room-btn st-{{ $room->status }}">
                                    <div>
                                        <div class="room-num">{{ $room->room_number }}</div>
                                        <div class="room-type">{{ $room->roomType->name ?? 'Room Type Pending' }}</div>
                                        <div class="room-guest">{{ $room->active_guest_name ?: ($room->status === 'dirty' ? 'Needs cleaning' : 'Ready for assignment') }}</div>
                                        <div class="room-rate">KES {{ number_format((float) ($room->current_rate ?? $room->roomType->base_rate ?? 0), 0) }}</div>
                                    </div>
                                    <div class="room-status-line">
                                        <span class="badge {{ $tone($room->status) }}">{{ $statusLabel($room->status) }}</span>
                                        <span class="badge {{ $room->housekeeping_status === 'dirty' ? 'is-dirty' : 'is-clean' }}">{{ $statusLabel($room->housekeeping_status) }}</span>
                                    </div>
                                </summary>
                                <div class="room-pop">
                                    <div class="room-pop-title">Room {{ $room->room_number }}</div>
                                    <div class="room-pop-row"><span>Guest</span><strong>{{ $room->active_guest_name ?: 'No active guest' }}</strong></div>
                                    <div class="room-pop-row"><span>Balance</span><strong>KES {{ number_format((float) $room->active_folio_balance, 2) }}</strong></div>
                                    <div class="room-pop-row"><span>Status</span><strong>{{ $statusLabel($room->status) }}</strong></div>

                                    @if($room->status === 'occupied' && $folio)
                                        <form method="post" action="{{ route('actions.hotel.check-out') }}" class="pms-form" style="margin-top:10px">
                                            @csrf
                                            <input type="hidden" name="hotel_folio_id" value="{{ $folio->id }}">
                                            <input class="inp" type="number" step="0.01" min="0" name="payment_amount" value="{{ max(0, (float) $folio->balance) }}">
                                            <select class="inp" name="payment_method"><option value="cash">Cash</option><option value="mpesa">M-Pesa</option><option value="card">Card</option><option value="bank">Bank</option><option value="credit">Credit</option></select>
                                            <button class="btn btn-primary">Check Out</button>
                                        </form>
                                    @elseif($room->status === 'vacant_clean')
                                        <form method="post" action="{{ route('actions.hotel.check-in') }}" class="pms-form" style="margin-top:10px">
                                            @csrf
                                            <input type="hidden" name="hotel_room_id" value="{{ $room->id }}">
                                            <input class="inp" name="guest_name" placeholder="Guest name" required>
                                            <input class="inp" type="date" name="check_out_date" value="{{ now()->addDay()->toDateString() }}" required>
                                            <input class="inp" type="number" step="0.01" min="0" name="room_rate" value="{{ $room->current_rate ?: $room->roomType->base_rate }}">
                                            <button class="btn btn-primary">Check In</button>
                                        </form>
                                    @else
                                        <form method="post" action="{{ route('actions.hotel.room-status') }}" class="pms-form" style="margin-top:10px">
                                            @csrf
                                            <input type="hidden" name="hotel_room_id" value="{{ $room->id }}">
                                            <select class="inp" name="status">
                                                @foreach(['vacant_clean','occupied','reserved','dirty','out_of_order'] as $status)<option value="{{ $status }}" @selected($room->status === $status)>{{ $statusLabel($status) }}</option>@endforeach
                                            </select>
                                            <select class="inp" name="housekeeping_status">
                                                @foreach(['clean','dirty','inspected','out_of_order'] as $status)<option value="{{ $status }}" @selected($room->housekeeping_status === $status)>{{ $statusLabel($status) }}</option>@endforeach
                                            </select>
                                            <button class="btn">Update Room</button>
                                        </form>
                                    @endif
                                </div>
                            </details>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </section>

        <aside class="pms-card">
            <div class="pms-card-head">
                <div><div class="pms-title">Recent Activity</div><div class="pms-sub">Quick operational feed for the front office.</div></div>
            </div>
            <div class="pms-pad">
                <div class="activity-list">
                    @foreach($hotelReservations->take(5) as $reservation)
                        <div class="activity"><i class="ti ti-calendar-check"></i><div><strong>{{ $reservation->guest_name }}</strong><span>{{ ucfirst(str_replace('_', ' ', $reservation->status)) }} · {{ $reservation->check_in_date?->format('d M') }} to {{ $reservation->check_out_date?->format('d M') }}</span></div></div>
                    @endforeach
                    @foreach($recentFolios->take(5) as $folio)
                        <div class="activity"><i class="ti ti-receipt-2"></i><div><strong>{{ $folio->guest_name }}</strong><span>{{ ucfirst($folio->status) }} folio · Room {{ $folio->room->room_number ?? '-' }} · KES {{ number_format((float) $folio->balance, 2) }}</span></div></div>
                    @endforeach
                    @foreach($dirtyRooms->take(4) as $room)
                        <div class="activity"><i class="ti ti-spray"></i><div><strong>Room {{ $room->room_number }}</strong><span>{{ $statusLabel($room->status) }} · housekeeping {{ $statusLabel($room->housekeeping_status) }}</span></div></div>
                    @endforeach
                    @if($hotelReservations->isEmpty() && $recentFolios->isEmpty() && $dirtyRooms->isEmpty())
                        <div class="pms-note">No recent activity yet. Reservations, check-ins, check-outs, and housekeeping updates will appear here.</div>
                    @endif
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
