@extends('layouts.app', ['title' => 'Expenses'])
@section('content')
<div class="card" style="margin-bottom:16px"><form method="post" action="{{ route('actions.expense') }}" class="form-grid">@csrf
<div><div class="lbl">Category</div><input class="inp" name="category" required></div><div><div class="lbl">Description</div><input class="inp" name="description" required></div><div><div class="lbl">Amount</div><input class="inp" name="amount" type="number" step="0.01" required></div><div><div class="lbl">Payment</div><select class="inp" name="payment_method"><option>cash</option><option>mpesa</option><option>bank</option></select></div><div><button class="btn btn-primary">Add Expense</button></div>
</form></div>
<div class="card"><table><thead><tr><th>Category</th><th>Description</th><th>Amount</th><th>Status</th></tr></thead><tbody>@foreach($expenses as $e)<tr><td>{{ $e->category }}</td><td>{{ $e->description }}</td><td>KES {{ number_format($e->amount,2) }}</td><td><span class="badge b-blue">{{ $e->status }}</span></td></tr>@endforeach</tbody></table></div>
@endsection

