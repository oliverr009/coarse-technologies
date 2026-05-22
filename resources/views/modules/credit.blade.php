@extends('layouts.app', ['title' => 'Credit Sales'])
@section('content')
<div class="card" style="margin-bottom:16px"><form method="post" action="{{ route('actions.customer') }}" class="form-grid">@csrf
<div><div class="lbl">Customer</div><input class="inp" name="name" required></div><div><div class="lbl">Phone</div><input class="inp" name="phone"></div><div><div class="lbl">Email</div><input class="inp" name="email"></div><div><div class="lbl">Credit Limit</div><input class="inp" name="credit_limit" type="number" step="0.01" value="0"></div><div><button class="btn btn-primary">Add Customer</button></div>
</form></div>
<div class="grid-2"><div class="card"><div class="sec-head"><span class="sec-title">Customers</span></div><table><thead><tr><th>Name</th><th>Phone</th><th>Limit</th></tr></thead><tbody>@foreach($customers as $c)<tr><td>{{ $c->name }}</td><td>{{ $c->phone }}</td><td>KES {{ number_format($c->credit_limit,2) }}</td></tr>@endforeach</tbody></table></div><div class="card"><div class="sec-head"><span class="sec-title">Credit Ledger</span></div><table><thead><tr><th>Customer</th><th>Amount</th><th>Due</th></tr></thead><tbody>@foreach($credits as $cr)<tr><td>{{ $cr->customer->name ?? '-' }}</td><td>KES {{ number_format($cr->amount,2) }}</td><td>{{ $cr->due_date?->format('d M Y') }}</td></tr>@endforeach</tbody></table></div></div>
@endsection

