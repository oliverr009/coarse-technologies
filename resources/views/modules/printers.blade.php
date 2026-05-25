@extends('layouts.app', ['title' => 'Printers'])
@section('content')
<style>
.prt-shell{display:flex;flex-direction:column;gap:16px}
.prt-hero{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
.prt-stat,.prt-card{border:1px solid var(--border);border-radius:20px;background:linear-gradient(180deg,rgba(43,37,38,.96),rgba(31,26,27,.98))}
.prt-stat{padding:16px 18px}
.prt-k{font-size:11px;color:var(--text3);text-transform:uppercase;letter-spacing:.08em}
.prt-v{margin-top:8px;font-size:26px;font-weight:800;color:var(--text);font-family:'Space Mono',monospace}
.prt-h{margin-top:6px;font-size:12px;color:var(--text2)}
.prt-card-head{display:flex;justify-content:space-between;gap:12px;padding:16px 18px;border-bottom:1px solid var(--border)}
.prt-title{font-size:13px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
.prt-sub{font-size:12px;color:var(--text3);margin-top:4px}
.prt-pad{padding:16px 18px}
.prt-form{display:flex;flex-direction:column;gap:18px}
.prt-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.prt-full{grid-column:1 / -1}
.prt-section{padding:14px;border:1px solid var(--border);border-radius:16px;background:rgba(255,255,255,.025)}
.prt-section-title{font-size:12px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;margin-bottom:12px}
.prt-checks{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.prt-check{display:flex;gap:10px;align-items:flex-start;padding:10px 12px;border-radius:14px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.04)}
.prt-check input{margin-top:3px}
.prt-check strong{display:block;font-size:13px}
.prt-check span{display:block;font-size:11px;color:var(--text3);margin-top:3px;line-height:1.5}
.prt-note{padding:12px 14px;border-radius:14px;background:rgba(40,188,238,.08);border:1px solid rgba(40,188,238,.16);font-size:12px;color:var(--text2);line-height:1.55}
.prt-side{display:flex;flex-direction:column;gap:16px}
.prt-mini{display:flex;flex-direction:column;gap:10px}
.prt-mini-item{display:flex;justify-content:space-between;gap:10px;padding:11px 0;border-bottom:1px solid var(--border)}
.prt-mini-item:last-child{border-bottom:none}
.prt-mini-item strong{font-size:13px}
.prt-mini-item span{display:block;font-size:11px;color:var(--text3);margin-top:3px}
.prt-badge{display:inline-flex;align-items:center;padding:4px 8px;border-radius:999px;font-size:10px;font-weight:800;letter-spacing:.06em;text-transform:uppercase}
.prt-blue{background:rgba(40,188,238,.12);color:var(--blue)}
.prt-gold{background:rgba(255,191,71,.12);color:var(--gold)}
.prt-green{background:rgba(62,207,142,.12);color:var(--green)}
@media(max-width:1180px){.prt-hero{grid-template-columns:repeat(2,1fr)}}
@media(max-width:880px){.prt-grid,.prt-checks,.prt-hero{grid-template-columns:1fr}}
</style>

<div class="prt-shell">
    <div class="prt-hero">
        <div class="prt-stat"><div class="prt-k">Auto Routes</div><div class="prt-v">{{ $summary['auto_routes'] }}</div><div class="prt-h">Printers set to auto-print immediately</div></div>
        <div class="prt-stat"><div class="prt-k">Browser Routes</div><div class="prt-v">{{ $summary['browser_routes'] }}</div><div class="prt-h">Outputs currently relying on browser print flow</div></div>
        <div class="prt-stat"><div class="prt-k">Bar Categories</div><div class="prt-v">{{ $summary['bar_categories'] }}</div><div class="prt-h">Menu groups treated as bar tickets</div></div>
        <div class="prt-stat"><div class="prt-k">Kitchen Categories</div><div class="prt-v">{{ $summary['kitchen_categories'] }}</div><div class="prt-h">Menu groups treated as kitchen tickets</div></div>
    </div>

    <div class="grid-2">
        <div class="prt-card">
            <div class="prt-card-head">
                <div>
                    <div class="prt-title">Printer Setup And Routing</div>
                    <div class="prt-sub">Configure receipt, kitchen, and bar output separately so the system knows where each document should go.</div>
                </div>
                <span class="prt-badge prt-gold">Routing Foundation</span>
            </div>
            <div class="prt-pad">
                <form method="post" action="{{ route('actions.printers') }}" class="prt-form">
                    @csrf

                    @foreach ([
                        'receipt' => 'Receipt Printer',
                        'kitchen' => 'Kitchen Printer',
                        'bar' => 'Bar Printer',
                    ] as $prefix => $label)
                        <div class="prt-section">
                            <div class="prt-section-title">{{ $label }}</div>
                            <div class="prt-grid">
                                <div><div class="lbl">Printer Name</div><input class="inp" name="{{ $prefix }}_printer_name" value="{{ $settings[$prefix.'_printer_name'] }}"></div>
                                <div><div class="lbl">Connection Mode</div>
                                    <select class="inp" name="{{ $prefix }}_printer_connection">
                                        <option value="browser" @selected($settings[$prefix.'_printer_connection'] === 'browser')>Browser Print</option>
                                        <option value="network" @selected($settings[$prefix.'_printer_connection'] === 'network')>Network Printer</option>
                                        <option value="windows_shared" @selected($settings[$prefix.'_printer_connection'] === 'windows_shared')>Windows Shared Queue</option>
                                        <option value="usb" @selected($settings[$prefix.'_printer_connection'] === 'usb')>USB / Local</option>
                                        <option value="agent" @selected($settings[$prefix.'_printer_connection'] === 'agent')>Local Print Agent</option>
                                    </select>
                                </div>
                                <div><div class="lbl">Target / Queue</div><input class="inp" name="{{ $prefix }}_printer_target" value="{{ $settings[$prefix.'_printer_target'] }}" placeholder="IP, queue name, browser station, or share path"></div>
                                <div><div class="lbl">Paper Size</div>
                                    <select class="inp" name="{{ $prefix }}_printer_paper">
                                        <option value="80mm" @selected($settings[$prefix.'_printer_paper'] === '80mm')>80mm</option>
                                        <option value="58mm" @selected($settings[$prefix.'_printer_paper'] === '58mm')>58mm</option>
                                        <option value="A4" @selected($settings[$prefix.'_printer_paper'] === 'A4')>A4</option>
                                    </select>
                                </div>
                                <div><div class="lbl">Copies</div><input class="inp" type="number" min="1" max="5" name="{{ $prefix }}_printer_copies" value="{{ $settings[$prefix.'_printer_copies'] }}"></div>
                            </div>
                            <div class="prt-checks" style="margin-top:12px">
                                <label class="prt-check">
                                    <input type="checkbox" name="{{ $prefix }}_printer_auto_print" value="1" @checked($settings[$prefix.'_printer_auto_print'])>
                                    <span><strong>Auto-print this route</strong><span>Browser mode will launch the print dialog automatically. Device modes remain saved for the next bridge layer.</span></span>
                                </label>
                            </div>
                        </div>
                    @endforeach

                    <div class="prt-section">
                        <div class="prt-section-title">Station Routing Rules</div>
                        <div class="prt-grid">
                            <div class="prt-full"><div class="lbl">Bar Categories</div><input class="inp" name="bar_categories_csv" value="{{ $settings['bar_categories_csv'] }}" placeholder="Bar,Drinks,Cocktails,Mocktails"></div>
                            <div class="prt-full"><div class="lbl">Kitchen Categories</div><input class="inp" name="kitchen_categories_csv" value="{{ $settings['kitchen_categories_csv'] }}" placeholder="Mains,Sides,Breakfast,Pizza"></div>
                        </div>
                    </div>

                    <div class="prt-section">
                        <div class="prt-section-title">Controls</div>
                        <div class="prt-checks">
                            <label class="prt-check">
                                <input type="checkbox" name="print_reprint_requires_manager" value="1" @checked($settings['print_reprint_requires_manager'])>
                                <span><strong>Manager approval for reprints</strong><span>Keep receipt or ticket reprints auditable when sensitive environments require it.</span></span>
                            </label>
                            <label class="prt-check">
                                <input type="checkbox" name="print_logo_on_receipt" value="1" @checked($settings['print_logo_on_receipt'])>
                                <span><strong>Print logo or brand header</strong><span>Use the receipt identity from Settings on printed customer receipts.</span></span>
                            </label>
                        </div>
                    </div>

                    <button class="btn btn-primary">Save Printer Settings</button>
                </form>
            </div>
        </div>

        <div class="prt-side">
            <div class="prt-card">
                <div class="prt-card-head">
                    <div>
                        <div class="prt-title">Rollout Guidance</div>
                        <div class="prt-sub">Recommended path from easiest testing to production-ready device routing.</div>
                    </div>
                    <span class="prt-badge prt-blue">Practical</span>
                </div>
                <div class="prt-pad">
                    <div class="prt-note">
                        Start with browser print for testing and cashier training. Then move receipt, kitchen, and bar printers to network or local-agent routing once the physical devices and station ownership are settled.
                    </div>
                </div>
            </div>

            <div class="prt-card">
                <div class="prt-card-head">
                    <div>
                        <div class="prt-title">Current Routes</div>
                        <div class="prt-sub">A quick read on where each operational print job is configured to go.</div>
                    </div>
                    <span class="prt-badge prt-green">Live Config</span>
                </div>
                <div class="prt-pad">
                    <div class="prt-mini">
                        <div class="prt-mini-item"><div><strong>Receipts</strong><span>{{ strtoupper($settings['receipt_printer_connection']) }} · {{ $settings['receipt_printer_target'] ?: 'No target set' }}</span></div><div>{{ $settings['receipt_printer_name'] }}</div></div>
                        <div class="prt-mini-item"><div><strong>Kitchen Tickets</strong><span>{{ strtoupper($settings['kitchen_printer_connection']) }} · {{ $settings['kitchen_printer_target'] ?: 'No target set' }}</span></div><div>{{ $settings['kitchen_printer_name'] }}</div></div>
                        <div class="prt-mini-item"><div><strong>Bar Tickets</strong><span>{{ strtoupper($settings['bar_printer_connection']) }} · {{ $settings['bar_printer_target'] ?: 'No target set' }}</span></div><div>{{ $settings['bar_printer_name'] }}</div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
