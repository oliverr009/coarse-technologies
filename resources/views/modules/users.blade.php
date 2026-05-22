@extends('layouts.app', ['title' => 'Users & Roles'])
@section('content')
<div class="card" style="margin-bottom:16px"><form method="post" action="{{ route('actions.user') }}" class="form-grid">@csrf
<div><div class="lbl">Name</div><input class="inp" name="name" required></div><div><div class="lbl">Email</div><input class="inp" name="email" type="email" required></div><div><div class="lbl">Role</div><select class="inp" name="role"><option>admin</option><option>manager</option><option>cashier</option><option>waiter</option><option>kitchen</option></select></div><div><div class="lbl">Password</div><input class="inp" name="password" type="password" required></div><div><button class="btn btn-primary">Add User</button></div>
</form></div>
<div class="card"><table><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr></thead><tbody>@foreach($users as $u)<tr><td>{{ $u->name }}</td><td>{{ $u->email }}</td><td><span class="badge b-gold">{{ $u->role }}</span></td><td><span class="badge b-green">Active</span></td></tr>@endforeach</tbody></table></div>
@endsection

