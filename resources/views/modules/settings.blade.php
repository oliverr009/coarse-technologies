@extends('layouts.app', ['title' => 'Settings'])
@section('content')
<div class="grid-2">
    <div class="card">
        <div class="sec-head"><span class="sec-title">Business Details</span></div>
        <form method="post" action="{{ route('actions.settings') }}">
            @csrf
            <p><div class="lbl">Business Name</div><input class="inp" name="business_name" value="{{ $settings['business_name'] ?? 'Coarse Restaurant — Main Branch' }}"></p>
            <p><div class="lbl">KRA PIN</div><input class="inp" name="kra_pin" value="{{ $settings['kra_pin'] ?? '' }}"></p>
            <p><div class="lbl">Tax Rate %</div><input class="inp" name="tax_rate" type="number" step="0.01" value="{{ $settings['tax_rate'] ?? 0 }}"></p>
            <p><div class="lbl">Currency</div><input class="inp" name="currency" value="{{ $settings['currency'] ?? 'KES' }}"></p>
            <p><label><input type="checkbox" name="allow_negative_inventory" value="1" @checked($settings['allow_negative_inventory'] ?? false)> Allow negative inventory</label></p>

            <div class="sec-head" style="margin-top:22px"><span class="sec-title">Manager Approval Controls</span></div>
            <p><div class="lbl">Discount Approval Threshold %</div><input class="inp" name="discount_approval_threshold" type="number" step="0.01" value="{{ $settings['discount_approval_threshold'] ?? 10 }}"></p>
            <p>
                <div class="lbl">Manager Override PIN</div>
                <input class="inp" name="manager_override_pin" type="password" placeholder="Set or replace override PIN">
                <span style="display:block;font-size:11px;color:var(--text3);margin-top:6px">Leave blank to keep the current override PIN. Managers and admins can also approve with their own account password.</span>
            </p>

            <button class="btn btn-primary">Save Settings</button>
        </form>
    </div>
    <div class="card">
        <div class="sec-head"><span class="sec-title">Operating Mode</span></div>
        <p><span class="badge b-gold">Restaurant Mode Active</span></p>
        <p style="color:var(--text3)">Retail and Hotel/PMS remain visible for roadmap continuity. Core implementation is restaurant-first.</p>
        <div style="margin-top:18px;padding:14px;border:1px solid var(--border);border-radius:var(--radius2);background:rgba(255,255,255,.025)">
            <div class="lbl">Approval Coverage</div>
            <div style="font-size:12px;color:var(--text2);line-height:1.6">
                Discounts now require a backend-verified reason and manager approval.
                <br>Voided cart items are logged with actor, approver, reason, and reference.
            </div>
        </div>
    </div>
</div>
@endsection
