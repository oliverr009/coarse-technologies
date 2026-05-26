@extends('layouts.app', ['title' => 'Users & Roles'])
@section('content')
<style>
.usr-hero{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:12px;margin-bottom:16px}
.usr-stat,.usr-card{border:1px solid var(--border);border-radius:20px;background:linear-gradient(180deg,rgba(43,37,38,.96),rgba(31,26,27,.98))}
.usr-stat{padding:16px 18px}
.usr-k{font-size:11px;color:var(--text3);text-transform:uppercase;letter-spacing:.08em}
.usr-v{margin-top:8px;font-size:26px;font-weight:800;color:var(--text);font-family:'Space Mono',monospace}
.usr-h{margin-top:6px;font-size:12px;color:var(--text2)}
.usr-shell{display:grid;grid-template-columns:minmax(0,1.25fr) minmax(360px,.95fr);gap:16px}
.usr-stack{display:flex;flex-direction:column;gap:16px}
.usr-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding:16px 18px;border-bottom:1px solid var(--border)}
.usr-title{font-size:13px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
.usr-sub{font-size:12px;color:var(--text3);margin-top:4px}
.usr-pad{padding:16px 18px}
.usr-table td,.usr-table th{font-size:12px;vertical-align:top}
.usr-badge{display:inline-flex;align-items:center;padding:4px 8px;border-radius:999px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.06em}
.ub-green{background:rgba(62,207,142,.12);color:var(--green)}
.ub-red{background:rgba(248,113,113,.12);color:var(--red)}
.ub-blue{background:rgba(40,188,238,.12);color:var(--blue)}
.ub-gold{background:rgba(249,181,28,.14);color:var(--gold)}
.usr-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.usr-full{grid-column:1 / -1}
.usr-form{padding:12px;border:1px solid var(--border);border-radius:16px;background:rgba(255,255,255,.025)}
.usr-form + .usr-form{margin-top:12px}
.usr-note{margin-top:14px;padding:12px;border-radius:14px;background:rgba(40,188,238,.08);border:1px solid rgba(40,188,238,.16);font-size:12px;color:var(--text2);line-height:1.5}
.usr-tags{display:flex;flex-wrap:wrap;gap:6px}
@media(max-width:1300px){.usr-hero{grid-template-columns:repeat(3,1fr)}.usr-shell{grid-template-columns:1fr}}
@media(max-width:760px){.usr-hero,.usr-grid{grid-template-columns:1fr}}
</style>

<div class="usr-hero">
    <div class="usr-stat"><div class="usr-k">Active Users</div><div class="usr-v">{{ $summary['active'] }}</div><div class="usr-h">Can sign in and work</div></div>
    <div class="usr-stat"><div class="usr-k">Inactive Users</div><div class="usr-v">{{ $summary['inactive'] }}</div><div class="usr-h">Blocked from access</div></div>
    <div class="usr-stat"><div class="usr-k">Admins</div><div class="usr-v">{{ $summary['admins'] }}</div><div class="usr-h">Full system ownership</div></div>
    <div class="usr-stat"><div class="usr-k">Managers</div><div class="usr-v">{{ $summary['managers'] }}</div><div class="usr-h">Approvals and reporting</div></div>
    <div class="usr-stat"><div class="usr-k">Cashiers</div><div class="usr-v">{{ $summary['cashiers'] }}</div><div class="usr-h">Till and POS operators</div></div>
    <div class="usr-stat"><div class="usr-k">Kitchen Users</div><div class="usr-v">{{ $summary['kitchen'] }}</div><div class="usr-h">Prep-line only access</div></div>
</div>

<div class="usr-shell">
    <div class="usr-stack">
        <div class="usr-card">
            <div class="usr-head">
                <div>
                    <div class="usr-title">User Directory</div>
                    <div class="usr-sub">Edit identities, adjust roles, reset passwords, and control whether a staff account can still access the system.</div>
                </div>
            </div>
            <div class="usr-pad">
                <table class="usr-table">
                    <thead>
                        <tr>
                            <th>Profile</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Controls</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    <strong>{{ $user->name }}</strong>
                                    <div style="color:var(--text3)">{{ $user->email }}</div>
                                </td>
                                <td><span class="usr-badge {{ in_array($user->role, ['admin','manager'], true) ? 'ub-gold' : 'ub-blue' }}">{{ $roles[$user->role] ?? $user->role }}</span></td>
                                <td><span class="usr-badge {{ $user->is_active ? 'ub-green' : 'ub-red' }}">{{ $user->is_active ? 'active' : 'inactive' }}</span></td>
                                <td style="min-width:320px">
                                    <form method="post" action="{{ route('actions.user-update') }}" class="usr-form">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                                        <div class="usr-grid">
                                            <div><div class="lbl">Name</div><input class="inp" name="name" value="{{ $user->name }}" required></div>
                                            <div><div class="lbl">Email</div><input class="inp" type="email" name="email" value="{{ $user->email }}" required></div>
                                            <div><div class="lbl">Role</div>
                                                <select class="inp" name="role" required>
                                                    @foreach($roles as $roleKey => $roleLabel)
                                                        <option value="{{ $roleKey }}" @selected($user->role === $roleKey)>{{ $roleLabel }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div style="display:flex;align-items:flex-end">
                                                <button class="btn btn-primary" type="submit">Save Profile</button>
                                            </div>
                                        </div>
                                    </form>

                                    <form method="post" action="{{ route('actions.user-password') }}" class="usr-form">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                                        <div class="usr-grid">
                                            <div><div class="lbl">Reset Password</div><input class="inp" type="password" name="password" placeholder="New password" required></div>
                                            <div style="display:flex;align-items:flex-end">
                                                <button class="btn btn-primary" type="submit">Reset Password</button>
                                            </div>
                                        </div>
                                    </form>

                                    <form method="post" action="{{ route('actions.user-status') }}" class="usr-form">
                                        @csrf
                                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                                        <input type="hidden" name="is_active" value="{{ $user->is_active ? 0 : 1 }}">
                                        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap">
                                            <span style="font-size:12px;color:var(--text3)">Access status control for this account.</span>
                                            <button class="btn {{ $user->is_active ? 'btn-danger' : 'btn-primary' }}" type="submit">{{ $user->is_active ? 'Deactivate' : 'Activate' }}</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" style="color:var(--text3)">No users found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="usr-card">
            <div class="usr-head">
                <div>
                    <div class="usr-title">Permission Matrix</div>
                    <div class="usr-sub">This is the current backend access map, so you can see what each role is actually allowed to open.</div>
                </div>
            </div>
            <div class="usr-pad">
                <table class="usr-table">
                    <thead>
                        <tr>
                            <th>Area</th>
                            <th>Allowed Roles</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($matrix as $row)
                            <tr>
                                <td>{{ $row['label'] }}</td>
                                <td>
                                    <div class="usr-tags">
                                        @foreach($row['roles'] as $role)
                                            <span class="usr-badge {{ in_array($role, ['admin','manager'], true) ? 'ub-gold' : 'ub-blue' }}">{{ $roles[$role] ?? $role }}</span>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="usr-card">
        <div class="usr-head">
            <div>
                <div class="usr-title">Add User</div>
                <div class="usr-sub">Create a role-based login for front-of-house, back-of-house, or management.</div>
            </div>
        </div>
        <div class="usr-pad">
            <form method="post" action="{{ route('actions.user') }}">
                @csrf
                <div class="usr-grid">
                    <div class="usr-full"><div class="lbl">Name</div><input class="inp" name="name" required></div>
                    <div class="usr-full"><div class="lbl">Email</div><input class="inp" type="email" name="email" required></div>
                    <div><div class="lbl">Role</div>
                        <select class="inp" name="role" required>
                            @foreach($roles as $roleKey => $roleLabel)
                                <option value="{{ $roleKey }}">{{ $roleLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div><div class="lbl">Password</div><input class="inp" type="password" name="password" required></div>
                </div>
                <button class="btn btn-primary">Create User</button>
            </form>
            <div class="usr-note">
                Recommended split: cashier and waiter for sell-side work, kitchen for KDS only, inventory for stock and purchasing, manager for approvals and reports, admin for full setup and controls.
            </div>
        </div>
    </div>
</div>
@endsection
