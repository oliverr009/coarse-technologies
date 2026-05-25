@extends('layouts.app', ['title' => 'Hotel / PMS'])
@section('content')
<style>
.pms-hero{display:grid;grid-template-columns:repeat(8,minmax(0,1fr));gap:12px;margin-bottom:16px}
.pms-stat,.pms-card{border:1px solid var(--border);border-radius:20px;background:linear-gradient(180deg,rgba(43,37,38,.96),rgba(31,26,27,.98))}
.pms-stat{padding:16px 18px}
.pms-k{font-size:11px;color:var(--text3);text-transform:uppercase;letter-spacing:.08em}
.pms-v{margin-top:8px;font-size:24px;font-weight:800;color:var(--text);font-family:'Space Mono',monospace}
.pms-h{margin-top:6px;font-size:12px;color:var(--text2);line-height:1.45}
.pms-shell{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(360px,.85fr);gap:16px}
.pms-stack{display:flex;flex-direction:column;gap:16px}
.pms-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:16px 18px;border-bottom:1px solid var(--border)}
.pms-title{font-size:13px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
.pms-sub{font-size:12px;color:var(--text3);margin-top:4px;line-height:1.45}
.pms-pad{padding:16px 18px}
.pms-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.room-card{border:1px solid var(--border);border-radius:18px;padding:14px;background:rgba(255,255,255,.025)}
.room-top{display:flex;align-items:flex-start;justify-content:space-between;gap:10px}
.room-no{font-size:22px;font-weight:800;color:var(--text);font-family:'Space Mono',monospace}
.room-type{font-size:12px;color:var(--text3);margin-top:4px}
.room-meta{margin-top:12px;display:flex;flex-direction:column;gap:8px}
.room-line{display:flex;justify-content:space-between;gap:10px;font-size:12px;color:var(--text2)}
.room-line strong{text-align:right;color:var(--text)}
.room-badge{display:inline-flex;align-items:center;padding:4px 8px;border-radius:999px;font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
.rb-occ{background:rgba(62,207,142,.12);color:var(--green)}
.rb-rsv{background:rgba(255,191,71,.12);color:var(--gold)}
.rb-clean{background:rgba(40,188,238,.12);color:var(--blue)}
.rb-dirty{background:rgba(248,113,113,.12);color:var(--red)}
.rb-oos{background:rgba(148,163,184,.18);color:#cbd5e1}
.house-badge{display:inline-flex;align-items:center;padding:3px 8px;border-radius:999px;font-size:10px;font-weight:700;background:rgba(255,255,255,.06);color:var(--text2);text-transform:uppercase}
.table-shell{overflow:hidden;border:1px solid var(--border);border-radius:16px}
.table-shell table{width:100%;border-collapse:collapse}
.table-shell th,.table-shell td{padding:12px 14px;border-bottom:1px solid var(--border);font-size:12px;text-align:left;vertical-align:top}
.table-shell th{font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:var(--text3);background:rgba(255,255,255,.02)}
.table-shell tr:last-child td{border-bottom:none}
.table-shell td strong{display:block;color:var(--text);font-size:13px}
.table-shell td span{display:block;margin-top:4px;color:var(--text3)}
.pms-mini{display:flex;flex-direction:column;gap:10px}
.pms-mini-item{display:flex;justify-content:space-between;gap:10px;padding:11px 0;border-bottom:1px solid var(--border)}
.pms-mini-item:last-child{border-bottom:none}
.pms-mini-item strong{font-size:13px}
.pms-mini-item span{display:block;font-size:11px;color:var(--text3);margin-top:3px}
.guest-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.guest-card{border:1px solid var(--border);border-radius:16px;padding:13px 14px;background:rgba(255,255,255,.03)}
.guest-card strong{display:block;font-size:13px;color:var(--text)}
.guest-meta{margin-top:6px;font-size:12px;color:var(--text2);line-height:1.5}
.guest-chip{display:inline-flex;align-items:center;padding:3px 8px;border-radius:999px;background:rgba(255,191,71,.12);color:var(--gold);font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
.pms-note{margin-top:14px;padding:12px;border-radius:14px;background:rgba(40,188,238,.08);border:1px solid rgba(40,188,238,.16);font-size:12px;color:var(--text2);line-height:1.55}
@media(max-width:1480px){.pms-hero{grid-template-columns:repeat(4,1fr)}}
@media(max-width:1280px){.pms-grid{grid-template-columns:repeat(2,1fr)}.pms-shell{grid-template-columns:1fr}.guest-grid{grid-template-columns:1fr}}
@media(max-width:760px){.pms-hero,.pms-grid{grid-template-columns:1fr}.table-shell{overflow:auto}}
</style>

@php
    $statusClass = fn ($status) => match($status) {
        'occupied' => 'rb-occ',
        'reserved' => 'rb-rsv',
        'vacant_clean' => 'rb-clean',
        'dirty' => 'rb-dirty',
        default => 'rb-oos',
    };
@endphp

<div class="pms-hero">
    <div class="pms-stat"><div class="pms-k">Rooms Ready</div><div class="pms-v">{{ $summary['inventory_ready'] }}</div><div class="pms-h">Sellable rooms that can take a new arrival right now.</div></div>
    <div class="pms-stat"><div class="pms-k">Occupied</div><div class="pms-v">{{ $summary['occupied'] }}</div><div class="pms-h">Current in-house rooms.</div></div>
    <div class="pms-stat"><div class="pms-k">Reserved</div><div class="pms-v">{{ $summary['reserved'] }}</div><div class="pms-h">Held for upcoming arrivals.</div></div>
    <div class="pms-stat"><div class="pms-k">Dirty</div><div class="pms-v">{{ $summary['dirty'] }}</div><div class="pms-h">Waiting on housekeeping turnover.</div></div>
    <div class="pms-stat"><div class="pms-k">Out Of Order</div><div class="pms-v">{{ $summary['out_of_order'] }}</div><div class="pms-h">Temporarily blocked from sale.</div></div>
    <div class="pms-stat"><div class="pms-k">Open Folios</div><div class="pms-v">{{ $summary['folios'] }}</div><div class="pms-h">Rooms already carrying guest balance.</div></div>
    <div class="pms-stat"><div class="pms-k">Room Types</div><div class="pms-v">{{ $summary['room_types'] }}</div><div class="pms-h">Rate products available to the front desk.</div></div>
    <div class="pms-stat"><div class="pms-k">Guest Profiles</div><div class="pms-v">{{ $summary['guest_profiles'] }}</div><div class="pms-h">Known guest identities ready for check-in later.</div></div>
</div>

<div class="pms-shell">
    <div class="pms-stack">
        <div class="pms-card">
            <div class="pms-head">
                <div>
                    <div class="pms-title">Room Inventory</div>
                    <div class="pms-sub">Real PMS phase-one room control: type, current rate, housekeeping state, and folio direction all in one place.</div>
                </div>
            </div>
            <div class="pms-pad">
                <div class="pms-grid">
                    @foreach($rooms as $room)
                        <div class="room-card">
                            <div class="room-top">
                                <div>
                                    <div class="room-no">{{ $room->room_number }}</div>
                                    <div class="room-type">{{ $room->roomType->name ?? 'Room Type Pending' }}</div>
                                </div>
                                <span class="room-badge {{ $statusClass($room->status) }}">{{ str_replace('_', ' ', $room->status) }}</span>
                            </div>
                            <div class="room-meta">
                                <div class="room-line"><span>Floor</span><strong>{{ $room->floor ?: 'Unmapped' }}</strong></div>
                                <div class="room-line"><span>Guest</span><strong>{{ $room->active_guest_name ?: 'No active guest' }}</strong></div>
                                <div class="room-line"><span>Night Rate</span><strong>KES {{ number_format((float) ($room->current_rate ?? $room->roomType->base_rate ?? 0), 0) }}</strong></div>
                                <div class="room-line"><span>Folio Balance</span><strong>KES {{ number_format((float) ($room->active_folio_balance ?? 0), 0) }}</strong></div>
                            </div>
                            <div style="margin-top:12px">
                                <span class="house-badge">{{ str_replace('_', ' ', $room->housekeeping_status ?? 'clean') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="pms-card">
            <div class="pms-head">
                <div>
                    <div class="pms-title">Room Types And Rates</div>
                    <div class="pms-sub">The commercial room catalog that later check-in, rate assignment, and folio posting will depend on.</div>
                </div>
            </div>
            <div class="pms-pad">
                <div class="table-shell">
                    <table>
                        <thead>
                            <tr>
                                <th>Room Type</th>
                                <th>Code</th>
                                <th>Base Rate</th>
                                <th>Max Occupancy</th>
                                <th>Inventory</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($roomTypes as $type)
                                <tr>
                                    <td><strong>{{ $type->name }}</strong></td>
                                    <td>{{ $type->code ?: 'Pending' }}</td>
                                    <td>KES {{ number_format((float) ($type->base_rate ?? 0), 0) }}</td>
                                    <td>{{ $type->max_occupancy ?? 0 }} pax</td>
                                    <td>{{ $type->rooms_count ?? 0 }} room(s)</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="pms-card">
            <div class="pms-head">
                <div>
                    <div class="pms-title">Build Direction</div>
                    <div class="pms-sub">This phase gives the PMS its inventory and guest spine so the next operations layer can land cleanly.</div>
                </div>
            </div>
            <div class="pms-pad">
                <div class="pms-note">
                    With room inventory and guest visibility in place, the next PMS layer should add stay records, check-in and check-out flow, folios, and restaurant bills posted straight to a room instead of forcing immediate settlement.
                </div>
            </div>
        </div>
    </div>

    <div class="pms-stack">
        <div class="pms-card">
            <div class="pms-head">
                <div>
                    <div class="pms-title">Guest Profiles</div>
                    <div class="pms-sub">A starter guest registry built from your current customer book and reservation stream.</div>
                </div>
            </div>
            <div class="pms-pad">
                <div class="guest-grid">
                    @forelse($guestProfiles as $guest)
                        <div class="guest-card">
                            <span class="guest-chip">{{ $guest->profile_source }}</span>
                            <strong style="margin-top:10px">{{ $guest->name }}</strong>
                            <div class="guest-meta">
                                <div>{{ $guest->phone ?: 'Phone pending' }}</div>
                                <div>{{ $guest->email ?: 'Email pending' }}</div>
                                <div>{{ $guest->activity_hint }}</div>
                            </div>
                        </div>
                    @empty
                        <div style="color:var(--text3);font-size:12px">Guest profiles will appear here once customers or reservations exist.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="pms-card">
            <div class="pms-head">
                <div>
                    <div class="pms-title">Reservation Feed</div>
                    <div class="pms-sub">Future arrivals that should convert into stays once check-in is introduced.</div>
                </div>
            </div>
            <div class="pms-pad">
                <div class="pms-mini">
                    @forelse($reservations as $reservation)
                        <div class="pms-mini-item">
                            <div>
                                <strong>{{ $reservation->customer_name }}</strong>
                                <span>{{ $reservation->reserved_for?->format('d M · H:i') }} · {{ $reservation->covers }} guests</span>
                                <span>{{ $reservation->table?->name ?? 'No room or table linked yet' }}</span>
                            </div>
                            <div><span class="room-badge rb-rsv">{{ $reservation->status }}</span></div>
                        </div>
                    @empty
                        <div style="color:var(--text3);font-size:12px">No reservations in the feed yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
