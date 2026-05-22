@extends('layouts.app', ['title' => 'Settings'])
@section('content')
<div class="grid-2"><div class="card"><div class="sec-head"><span class="sec-title">Business Details</span></div><form method="post" action="{{ route('actions.settings') }}">@csrf
<p><div class="lbl">Business Name</div><input class="inp" name="business_name" value="{{ $settings['business_name'] ?? 'Coarse Restaurant — Main Branch' }}"></p>
<p><div class="lbl">KRA PIN</div><input class="inp" name="kra_pin" value="{{ $settings['kra_pin'] ?? '' }}"></p>
<p><div class="lbl">Tax Rate %</div><input class="inp" name="tax_rate" type="number" step="0.01" value="{{ $settings['tax_rate'] ?? 0 }}"></p>
<p><div class="lbl">Currency</div><input class="inp" name="currency" value="{{ $settings['currency'] ?? 'KES' }}"></p>
<p><label><input type="checkbox" name="allow_negative_inventory" value="1" @checked($settings['allow_negative_inventory'] ?? false)> Allow negative inventory</label></p>
<button class="btn btn-primary">Save Settings</button></form></div><div class="card"><div class="sec-head"><span class="sec-title">Operating Mode</span></div><p><span class="badge b-gold">Restaurant Mode Active</span></p><p style="color:var(--text3)">Retail and Hotel/PMS remain visible for roadmap continuity. Core implementation is restaurant-first.</p></div></div>
@endsection

