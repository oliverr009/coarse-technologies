@extends('layouts.app', ['title' => 'Purchases'])
@section('content')
<div class="card" style="margin-bottom:16px"><div class="sec-head"><span class="sec-title">Post Purchase / Stock In</span></div>
<form method="post" action="{{ route('actions.purchase') }}">@csrf
<div class="form-grid"><div><div class="lbl">Supplier</div><select class="inp" name="supplier_id">@foreach($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div><div style="grid-column:span 3"><div class="lbl">Notes</div><input class="inp" name="notes"></div></div>
@for($i=0;$i<5;$i++)<div class="form-grid" style="margin-top:8px"><div><div class="lbl">Product</div><select class="inp" name="product_id[]"><option value="">--</option>@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div><div><div class="lbl">Quantity</div><input class="inp" name="quantity[]" type="number" step="0.0001"></div><div><div class="lbl">Unit Cost</div><input class="inp" name="unit_cost[]" type="number" step="0.01"></div><div></div></div>@endfor
<p><button class="btn btn-primary">Post Purchase</button></p></form></div>
<div class="card"><div class="sec-head"><span class="sec-title">Recent Purchases</span></div><table><thead><tr><th>No</th><th>Total</th><th>Status</th><th>Date</th></tr></thead><tbody>@foreach($purchases as $p)<tr><td>{{ $p->purchase_number }}</td><td>KES {{ number_format($p->total_amount,2) }}</td><td><span class="badge b-green">{{ $p->status }}</span></td><td>{{ $p->created_at->format('d M H:i') }}</td></tr>@endforeach</tbody></table></div>
@endsection

