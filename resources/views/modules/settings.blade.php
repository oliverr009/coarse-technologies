@extends('layouts.app', ['title' => 'Settings'])
@section('content')
<style>
.set-shell{display:flex;flex-direction:column;gap:16px}
.set-hero{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
.set-stat,.set-card{border:1px solid var(--border);border-radius:20px;background:linear-gradient(180deg,rgba(43,37,38,.96),rgba(31,26,27,.98))}
.set-stat{padding:16px 18px}
.set-k{font-size:11px;color:var(--text3);text-transform:uppercase;letter-spacing:.08em}
.set-v{margin-top:8px;font-size:26px;font-weight:800;color:var(--text);font-family:'Space Mono',monospace}
.set-h{margin-top:6px;font-size:12px;color:var(--text2)}
.set-card-head{display:flex;justify-content:space-between;gap:12px;padding:16px 18px;border-bottom:1px solid var(--border)}
.set-title{font-size:13px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
.set-sub{font-size:12px;color:var(--text3);margin-top:4px}
.set-pad{padding:16px 18px}
.set-form{display:flex;flex-direction:column;gap:18px}
.set-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.set-grid-3{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
.set-full{grid-column:1 / -1}
.set-section{padding:14px;border:1px solid var(--border);border-radius:16px;background:rgba(255,255,255,.025)}
.set-section-title{font-size:12px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;margin-bottom:12px}
.set-checks{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.set-check{display:flex;gap:10px;align-items:flex-start;padding:10px 12px;border-radius:14px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.04)}
.set-check input{margin-top:3px}
.set-check strong{display:block;font-size:13px}
.set-check span{display:block;font-size:11px;color:var(--text3);margin-top:3px;line-height:1.5}
.set-note{padding:12px 14px;border-radius:14px;background:rgba(40,188,238,.08);border:1px solid rgba(40,188,238,.16);font-size:12px;color:var(--text2);line-height:1.55}
.set-side{display:flex;flex-direction:column;gap:16px}
.set-mini{display:flex;flex-direction:column;gap:10px}
.set-mini-item{display:flex;justify-content:space-between;gap:10px;padding:11px 0;border-bottom:1px solid var(--border)}
.set-mini-item:last-child{border-bottom:none}
.set-mini-item strong{font-size:13px}
.set-mini-item span{display:block;font-size:11px;color:var(--text3);margin-top:3px}
.set-badge{display:inline-flex;align-items:center;padding:4px 8px;border-radius:999px;font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
.set-green{background:rgba(62,207,142,.12);color:var(--green)}
.set-gold{background:rgba(255,191,71,.12);color:var(--gold)}
.set-blue{background:rgba(40,188,238,.12);color:var(--blue)}
@media(max-width:1180px){.set-hero{grid-template-columns:repeat(2,1fr)}.set-grid-3{grid-template-columns:repeat(2,1fr)}}
@media(max-width:880px){.set-grid,.set-grid-3,.set-checks,.set-hero{grid-template-columns:1fr}}
</style>

<div class="set-shell">
    <div class="set-hero">
        <div class="set-stat"><div class="set-k">VAT Rate</div><div class="set-v">{{ number_format($summary['tax_rate'], 0) }}%</div><div class="set-h">Tax used during POS sale posting</div></div>
        <div class="set-stat"><div class="set-k">Service Charge</div><div class="set-v">{{ number_format($summary['service_charge_rate'], 0) }}%</div><div class="set-h">Default charge applied on the sell screen</div></div>
        <div class="set-stat"><div class="set-k">Payment Modes</div><div class="set-v">{{ $summary['payment_methods'] }}</div><div class="set-h">Methods currently enabled for operators</div></div>
        <div class="set-stat"><div class="set-k">Discount Gate</div><div class="set-v">{{ number_format($summary['approval_threshold'], 0) }}%</div><div class="set-h">Automatic manager approval threshold</div></div>
    </div>

    <div class="grid-2">
        <div class="set-card">
            <div class="set-card-head">
                <div>
                    <div class="set-title">System Control Center</div>
                    <div class="set-sub">Business identity, commercial rules, payment behavior, receipt output, and approval controls.</div>
                </div>
                <span class="set-badge set-gold">Restaurant Mode</span>
            </div>
            <div class="set-pad">
                <form method="post" action="{{ route('actions.settings') }}" class="set-form">
                    @csrf

                    <div class="set-section">
                        <div class="set-section-title">Business Identity</div>
                        <div class="set-grid">
                            <div><div class="lbl">Business Name</div><input class="inp" name="business_name" value="{{ $settings['business_name'] }}"></div>
                            <div><div class="lbl">Branch Name</div><input class="inp" name="branch_name" value="{{ $settings['branch_name'] }}"></div>
                            <div><div class="lbl">Business Phone</div><input class="inp" name="business_phone" value="{{ $settings['business_phone'] }}"></div>
                            <div><div class="lbl">Business Email</div><input class="inp" type="email" name="business_email" value="{{ $settings['business_email'] }}"></div>
                            <div><div class="lbl">KRA PIN</div><input class="inp" name="kra_pin" value="{{ $settings['kra_pin'] }}"></div>
                            <div><div class="lbl">Currency</div><input class="inp" name="currency" value="{{ $settings['currency'] }}"></div>
                            <div class="set-full"><div class="lbl">Business Address</div><input class="inp" name="business_address" value="{{ $settings['business_address'] }}"></div>
                        </div>
                    </div>

                    <div class="set-section">
                        <div class="set-section-title">Tax And Charge Rules</div>
                        <div class="set-grid">
                            <div><div class="lbl">VAT / Tax Rate %</div><input class="inp" type="number" step="0.01" min="0" max="100" name="tax_rate" value="{{ $settings['tax_rate'] }}"></div>
                            <div><div class="lbl">Service Charge Rate %</div><input class="inp" type="number" step="0.01" min="0" max="100" name="service_charge_rate" value="{{ $settings['service_charge_rate'] }}"></div>
                        </div>
                        <div class="set-checks" style="margin-top:12px">
                            <label class="set-check">
                                <input type="checkbox" name="service_charge_enabled" value="1" @checked($settings['service_charge_enabled'])>
                                <span><strong>Enable service charge by default</strong><span>Push the configured service charge into the POS payment flow automatically.</span></span>
                            </label>
                            <label class="set-check">
                                <input type="checkbox" name="table_required_for_dine_in" value="1" @checked($settings['table_required_for_dine_in'])>
                                <span><strong>Require table on dine-in</strong><span>Useful when every dine-in order should attach to a table before holding or sending.</span></span>
                            </label>
                        </div>
                    </div>

                    <div class="set-section">
                        <div class="set-section-title">Payment Controls</div>
                        <div class="set-checks">
                            <label class="set-check">
                                <input type="checkbox" name="payment_cash_enabled" value="1" @checked($settings['payment_cash_enabled'])>
                                <span><strong>Cash</strong><span>Keep cash tender available on the sell screen and receipt posting flow.</span></span>
                            </label>
                            <label class="set-check">
                                <input type="checkbox" name="payment_mpesa_enabled" value="1" @checked($settings['payment_mpesa_enabled'])>
                                <span><strong>M-Pesa</strong><span>Enable mobile money as a first-class operator action.</span></span>
                            </label>
                            <label class="set-check">
                                <input type="checkbox" name="payment_card_enabled" value="1" @checked($settings['payment_card_enabled'])>
                                <span><strong>Card</strong><span>Keep bank card settlement visible during payment collection.</span></span>
                            </label>
                            <label class="set-check">
                                <input type="checkbox" name="payment_credit_enabled" value="1" @checked($settings['payment_credit_enabled'])>
                                <span><strong>Credit</strong><span>Allow customer balance and account-based settlement when approved operationally.</span></span>
                            </label>
                        </div>
                    </div>

                    <div class="set-section">
                        <div class="set-section-title">Approvals And Safeguards</div>
                        <div class="set-grid">
                            <div><div class="lbl">Discount Approval Threshold %</div><input class="inp" type="number" step="0.01" min="0" max="100" name="discount_approval_threshold" value="{{ $settings['discount_approval_threshold'] }}"></div>
                            <div><div class="lbl">Default Guest Count</div><input class="inp" type="number" min="1" max="99" name="default_guest_count" value="{{ $settings['default_guest_count'] }}"></div>
                        </div>
                        <div class="set-checks" style="margin-top:12px">
                            <label class="set-check">
                                <input type="checkbox" name="void_requires_manager" value="1" @checked($settings['void_requires_manager'])>
                                <span><strong>Void requires manager</strong><span>Keep destructive bill actions behind an approval gate and audit trail.</span></span>
                            </label>
                            <label class="set-check">
                                <input type="checkbox" name="refund_requires_manager" value="1" @checked($settings['refund_requires_manager'])>
                                <span><strong>Refund requires manager</strong><span>Protect post-sale cash-out actions with explicit approval.</span></span>
                            </label>
                            <label class="set-check">
                                <input type="checkbox" name="allow_negative_inventory" value="1" @checked($settings['allow_negative_inventory'])>
                                <span><strong>Allow negative inventory</strong><span>Use only when operations accept temporary stock debt during service.</span></span>
                            </label>
                        </div>
                        <div style="margin-top:12px">
                            <div class="lbl">Manager Override PIN</div>
                            <input class="inp" name="manager_override_pin" type="password" placeholder="Set or replace override PIN">
                            <span style="display:block;font-size:11px;color:var(--text3);margin-top:6px">Leave blank to keep the current override PIN. Managers and admins can still approve with their own account password.</span>
                        </div>
                    </div>

                    <div class="set-section">
                        <div class="set-section-title">Receipt And POS Defaults</div>
                        <div class="set-grid">
                            <div><div class="lbl">Receipt Brand Label</div><input class="inp" name="receipt_prefix" value="{{ $settings['receipt_prefix'] }}"></div>
                            <div><div class="lbl">Default Order Type</div>
                                <select class="inp" name="default_order_type">
                                    <option value="dine_in" @selected($settings['default_order_type'] === 'dine_in')>Dine-in</option>
                                    <option value="takeaway" @selected($settings['default_order_type'] === 'takeaway')>Takeaway</option>
                                    <option value="delivery" @selected($settings['default_order_type'] === 'delivery')>Delivery</option>
                                </select>
                            </div>
                            <div class="set-full"><div class="lbl">Receipt Footer</div><input class="inp" name="receipt_footer" value="{{ $settings['receipt_footer'] }}"></div>
                        </div>
                    </div>

                    <button class="btn btn-primary">Save Settings</button>
                </form>
            </div>
        </div>

        <div class="set-side">
            <div class="set-card">
                <div class="set-card-head">
                    <div>
                        <div class="set-title">Live Effect</div>
                        <div class="set-sub">These are the settings already shaping the running POS and receipt flow.</div>
                    </div>
                    <span class="set-badge set-green">Applied</span>
                </div>
                <div class="set-pad">
                    <div class="set-mini">
                        <div class="set-mini-item"><div><strong>Receipt Identity</strong><span>Brand label, business details, and footer on the printed receipt.</span></div><div>{{ $settings['receipt_prefix'] }}</div></div>
                        <div class="set-mini-item"><div><strong>Sale Tax Logic</strong><span>VAT rate used when posting completed sales.</span></div><div>{{ number_format((float) $settings['tax_rate'], 2) }}%</div></div>
                        <div class="set-mini-item"><div><strong>Service Charge</strong><span>Default charge carried into the POS tender flow.</span></div><div>{{ $settings['service_charge_enabled'] ? number_format((float) $settings['service_charge_rate'], 2).'%' : 'Off' }}</div></div>
                        <div class="set-mini-item"><div><strong>Order Start Mode</strong><span>The default service type when operators open a fresh bill.</span></div><div>{{ str_replace('_', ' ', $settings['default_order_type']) }}</div></div>
                    </div>
                </div>
            </div>

            <div class="set-card">
                <div class="set-card-head">
                    <div>
                        <div class="set-title">Operating Guidance</div>
                        <div class="set-sub">What this settings pass is meant to control right now.</div>
                    </div>
                    <span class="set-badge set-blue">Core</span>
                </div>
                <div class="set-pad">
                    <div class="set-note">
                        This pass turns settings into the commercial control layer for the restaurant engine. It now governs tax, service charge, receipt identity, payment availability, approval thresholds, and default POS behavior. More advanced settings like branch-by-branch overrides, receipt printer mapping, and hotel/PMS operational defaults can sit on top of this later.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
