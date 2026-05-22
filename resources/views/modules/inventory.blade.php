@extends('layouts.app', ['title' => 'Inventory'])
@section('content')
<div class="card" style="margin-bottom:16px"><div class="sec-head"><span class="sec-title">Add Product / Raw Material</span></div>
<form method="post" action="{{ route('actions.product') }}" class="form-grid">@csrf
<div><div class="lbl">Name</div><input class="inp" name="name" required></div><div><div class="lbl">SKU</div><input class="inp" name="sku"></div>
<div><div class="lbl">Barcode</div><input class="inp" name="barcode"></div><div><div class="lbl">Subcategory</div><input class="inp" name="subcategory"></div>
<div><div class="lbl">Category</div><select class="inp" name="category_id">@foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach</select></div>
<div><div class="lbl">Type</div><select class="inp" name="product_type"><option>raw_material</option><option>finished_product</option><option>resale_item</option><option>semi_finished</option><option>service</option></select></div>
<div><div class="lbl">Unit</div><input class="inp" name="unit" value="pcs"></div><div><div class="lbl">Cost</div><input class="inp" name="cost_price" type="number" step="0.01" value="0"></div>
<div><div class="lbl">Price</div><input class="inp" name="selling_price" type="number" step="0.01" value="0"></div><div><div class="lbl">Description</div><input class="inp" name="description"></div><div><button class="btn btn-primary">Save Product</button></div>
</form></div>
<div class="card"><div class="sec-head"><span class="sec-title">Stock On Hand</span></div><div class="tbl-wrap"><table><thead><tr><th>Product</th><th>Type</th><th>Stock</th><th>Reorder</th><th>Cost</th><th>Price</th></tr></thead><tbody>
@foreach($products as $p)<tr><td><strong>{{ $p->name }}</strong><div style="font-size:11px;color:var(--text3)">{{ $p->category?->name }} @if($p->subcategory) · {{ $p->subcategory }} @endif</div></td><td>{{ $p->product_type }}</td><td style="color:var(--blue);font-weight:700">{{ number_format((float) $p->stock_qty, 4) }} {{ $p->unit }}</td><td>{{ $p->reorder_level }}</td><td>{{ number_format($p->cost_price,2) }}</td><td>{{ number_format($p->selling_price,2) }}</td></tr>@endforeach
</tbody></table></div></div>
@endsection
