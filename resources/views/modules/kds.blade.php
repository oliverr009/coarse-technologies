@extends('layouts.app', ['title' => 'Kitchen Display'])
@section('content')
<div class="grid-3">@forelse($items as $item)<div class="kds-card"><div class="sec-head"><span class="sec-title">{{ $item->product_name }}</span><span class="badge b-gold">{{ $item->kitchen_status }}</span></div><p>Order {{ $item->order->order_number }} · {{ $item->order->table->name ?? 'Takeaway' }}</p><p>Qty: {{ number_format($item->quantity, 2) }}</p><form method="post" action="{{ route('actions.kds') }}">@csrf<input type="hidden" name="item_id" value="{{ $item->id }}"><button name="status" value="preparing" class="btn btn-gold btn-sm">Preparing</button> <button name="status" value="ready" class="btn btn-primary btn-sm">Ready</button></form></div>@empty<div class="card">No active kitchen tickets.</div>@endforelse</div>
@endsection

