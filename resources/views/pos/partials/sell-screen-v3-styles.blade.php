<style>

*{box-sizing:border-box;margin:0;padding:0;-webkit-tap-highlight-color:transparent}
[data-theme="light"]{
  --bg:#f6f8fa;--bg2:#ffffff;--bg3:#f0f2f5;--bg4:#e8ebf0;
  --border:#d0d7de;--border2:#c4cdd5;
  --blue:#2563eb;--blue-dim:rgba(37,99,235,.10);
  --gold:#d97706;--gold-dim:rgba(217,119,6,.10);
  --green:#059669;--green-dim:rgba(5,150,105,.10);
  --red:#dc2626;--red-dim:rgba(220,38,38,.08);
  --purple:#7c3aed;--orange:#ea580c;
  --text:#1f2937;--text2:#6b7280;--text3:#9ca3af;
  --tile-bg:var(--bg4);--tile-active-bg:#1f2937;--tile-active-text:#fff;
  --card-bg:#fff;--card-shadow:0 1px 3px rgba(0,0,0,.04);
  --card-hover-shadow:0 8px 24px rgba(0,0,0,.08);
  --modal-backdrop:rgba(0,0,0,.5);
  --order-btn-bg:#1f2937;--order-btn-text:#fff;
  --nav-bg:#fff;--bills-bg:#fff;
  --input-focus-shadow:0 0 0 3px rgba(37,99,235,.1);
  --receipt-bg:#fff;--receipt-text:#1f2937;
}
[data-theme="dark"]{
  --bg:#0a0e14;--bg2:#111820;--bg3:#18202b;--bg4:#1e2838;
  --border:#253042;--border2:#2e3d54;
  --blue:#3b9eff;--blue-dim:rgba(59,158,255,.12);
  --gold:#f5a623;--gold-dim:rgba(245,166,35,.13);
  --green:#3ecf8e;--green-dim:rgba(62,207,142,.12);
  --red:#f87171;--red-dim:rgba(248,113,113,.1);
  --purple:#a78bfa;--orange:#fb923c;
  --text:#e6edf3;--text2:#8b949e;--text3:#4d5566;
  --tile-bg:var(--bg3);--tile-active-bg:var(--blue);--tile-active-text:#fff;
  --card-bg:var(--bg3);--card-shadow:none;
  --card-hover-shadow:0 4px 16px rgba(0,0,0,.3);
  --modal-backdrop:rgba(0,0,0,.7);
  --order-btn-bg:linear-gradient(135deg,var(--gold),#c98310);--order-btn-text:#1a0f00;
  --nav-bg:var(--bg2);--bills-bg:var(--bg2);
  --input-focus-shadow:none;
  --receipt-bg:#fff;--receipt-text:#111;
}
body{background:var(--bg);color:var(--text);height:100vh;overflow:hidden;display:flex;flex-direction:column;touch-action:manipulation}
body[data-theme="light"] .receipt{background:var(--receipt-bg);color:var(--receipt-text)}
body[data-theme="dark"] .receipt{background:#fff;color:#111}
nav{height:56px;background:var(--nav-bg);border-bottom:1px solid var(--border);display:flex;align-items:center;padding:0 20px;gap:12px;flex-shrink:0}
.nav-title{font-family:'Syne',sans-serif;font-weight:800;font-size:16px;letter-spacing:-.02em;color:var(--text)}
.nav-sub{font-size:11px;color:var(--text3);margin-top:1px}
.nav-spacer{flex:1}
.nav-pill{padding:6px 14px;border-radius:20px;font-size:11px;font-weight:700;background:var(--gold-dim);color:var(--gold);border:1px solid rgba(217,119,6,.2);display:flex;align-items:center;gap:5px}
.nav-icon{width:36px;height:36px;border-radius:10px;background:var(--bg3);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;color:var(--text2);cursor:pointer;font-size:16px;transition:all .2s}
.nav-icon:hover{color:var(--blue);border-color:var(--blue)}
.nav-icon:active{transform:scale(.95)}
.avatar{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#2563eb,#1e40af);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;color:#fff;border:2px solid rgba(37,99,235,.2)}
.bills-bar{height:52px;background:var(--bills-bg);border-bottom:1px solid var(--border);display:flex;align-items:center;padding:0 16px;gap:8px;overflow-x:auto;flex-shrink:0;scrollbar-width:none}
.bills-bar::-webkit-scrollbar{display:none}
.bill-tab{display:flex;align-items:center;gap:6px;padding:8px 14px;border-radius:12px;border:1.5px solid var(--border);font-size:13px;cursor:pointer;white-space:nowrap;transition:all .15s;color:var(--text2);background:var(--bg3);font-weight:600;min-height:40px}
.bill-tab:hover{background:var(--bg4);color:var(--text2)}
.bill-tab.active{background:var(--blue-dim);border-color:rgba(37,99,235,.35);color:var(--blue)}
.bt-name{font-weight:700;font-size:13px}
.bt-amt{font-family:'IBM Plex Mono',monospace;font-size:11px;opacity:.75;margin-left:2px}
.bt-items{font-size:10px;background:var(--bg4);color:var(--text2);padding:1px 6px;border-radius:8px;font-weight:700;margin-left:4px}
.bt-status{width:7px;height:7px;border-radius:50%;flex-shrink:0}
.bts-open{background:var(--blue)}.bts-kitchen{background:var(--orange)}.bts-hold{background:var(--gold)}
.bt-close{font-size:14px;opacity:.35;margin-left:4px;transition:opacity .15s;cursor:pointer;line-height:1;padding:2px}
.bill-tab:hover .bt-close{opacity:.8}
.new-bill-btn{width:40px;height:40px;border-radius:12px;border:2px dashed var(--border2);background:transparent;color:var(--text3);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:22px;transition:all .15s;flex-shrink:0;font-weight:700}
.new-bill-btn:hover{border-color:var(--blue);color:var(--blue)}
.new-bill-btn:active{transform:scale(.92)}
.shell{flex:1;display:grid;grid-template-columns:1fr 320px;overflow:hidden}
.left{display:flex;flex-direction:column;overflow:hidden}
.left-top{display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid var(--border);flex-shrink:0;background:var(--nav-bg)}
.order-type-group{display:flex;gap:5px}
.ot{padding:8px 16px;border-radius:24px;font-size:12px;font-weight:600;cursor:pointer;border:1.5px solid var(--border);background:var(--card-bg);color:var(--text2);transition:all .2s;display:flex;align-items:center;gap:6px;user-select:none;letter-spacing:.01em}
.ot.active-dine{border-color:var(--blue);background:var(--blue-dim);color:var(--blue)}
.ot.active-take{border-color:var(--green);background:var(--green-dim);color:var(--green)}
.ot.active-del{border-color:var(--purple);background:rgba(124,58,237,.08);color:var(--purple)}
.ot:not(.active-dine):not(.active-take):not(.active-del):hover{border-color:var(--text3);color:var(--text)}
.ot:active{transform:scale(.96)}
.search-wrap{flex:1;position:relative;max-width:280px}
.search-inp{width:100%;background:var(--bg3);border:1.5px solid var(--border);border-radius:12px;padding:10px 14px 10px 40px;font-size:13px;color:var(--text);outline:none;transition:all .2s;font-weight:500}
.search-inp:focus{border-color:var(--blue);background:var(--card-bg);box-shadow:var(--input-focus-shadow)}
.search-inp::placeholder{color:var(--text3)}
.search-icon{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text3);font-size:16px}
.search-clear{position:absolute;right:10px;top:50%;transform:translateY(-50%);width:24px;height:24px;border-radius:50%;background:var(--bg4);border:none;color:var(--text2);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:12px;transition:all .15s}
.search-clear:hover{background:var(--text3);color:#fff}
.cat-tiles-bar{display:flex;gap:8px;padding:14px 16px;border-bottom:1px solid var(--border);overflow-x:auto;flex-shrink:0;scrollbar-width:none;background:var(--bg)}
.cat-tiles-bar::-webkit-scrollbar{display:none}
.cat-tile{flex-shrink:0;padding:8px 16px;border-radius:12px;border:2px solid var(--border);background:var(--bg3);color:var(--text3);font-size:12px;font-weight:700;cursor:pointer;transition:all .15s;display:flex;align-items:center;gap:6px;user-select:none;white-space:nowrap}
.cat-tile:hover{background:var(--bg4);border-color:var(--border2)}
.cat-tile:active{transform:scale(.95)}
.cat-tile.active{background:var(--blue-dim);border-color:rgba(37,99,235,.35);color:var(--blue)}
.cat-tile .ct-count{font-size:10px;background:var(--bg);padding:1px 6px;border-radius:8px;color:var(--text3);font-weight:600;font-family:'IBM Plex Mono',monospace}
.cat-tile.active .ct-count{background:rgba(59,158,255,.15);color:var(--blue)}
.product-area{flex:1;overflow-y:auto;padding:16px;scrollbar-width:thin;scrollbar-color:var(--border2) transparent;background:var(--bg)}
.product-area::-webkit-scrollbar{width:4px}
.product-area::-webkit-scrollbar-thumb{background:var(--border2);border-radius:3px}
.cat-section{margin-bottom:20px}
.cat-header{display:flex;align-items:center;gap:10px;margin-bottom:14px;padding:0 4px}
.cat-title{font-family:'Syne',sans-serif;font-size:11px;font-weight:800;color:var(--text3);text-transform:uppercase;letter-spacing:.08em;padding-left:10px;border-left:3px solid var(--blue)}
.cat-count{font-size:10px;color:var(--text2);background:var(--bg3);padding:3px 10px;border-radius:20px;font-weight:700;font-family:'IBM Plex Mono',monospace}
.prod-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px}
.prod{background:var(--card-bg);border:1px solid var(--border);border-radius:16px;padding:16px 14px;cursor:pointer;transition:all .2s;position:relative;overflow:hidden;display:flex;flex-direction:column;align-items:center;text-align:center;min-height:140px;user-select:none;box-shadow:var(--card-shadow)}
.prod:hover{border-color:var(--blue);transform:translateY(-3px);box-shadow:var(--card-hover-shadow)}
.prod:active{transform:scale(.96);transition:transform .1s}
.prod-lp-ring{position:absolute;inset:0;border-radius:16px;border:3px solid var(--blue);opacity:0;pointer-events:none;transition:opacity .15s}
.prod.lp-active .prod-lp-ring{opacity:1}
.prod-emoji{font-size:32px;margin-bottom:6px;display:block;line-height:1}
.prod-name{font-size:13px;font-weight:700;color:var(--text);line-height:1.25;margin-bottom:4px;word-break:break-word}
.prod-price{font-size:18px;font-weight:800;color:var(--blue);font-family:'IBM Plex Mono',monospace;margin-top:auto;padding-top:8px}
.prod-stock{font-size:10px;padding:2px 7px;border-radius:6px;position:absolute;top:8px;right:8px;font-weight:700}
.ps-ok{background:rgba(5,150,105,.12);color:var(--green)}.ps-low{background:rgba(220,38,38,.12);color:var(--red);animation:pulseStock 2s ease-in-out infinite}
.prod-station{font-size:9px;font-weight:800;padding:2px 8px;border-radius:10px;position:absolute;top:8px;left:8px;letter-spacing:.04em}
.pb-kitchen{background:rgba(245,166,35,.12);color:var(--gold)}
.pb-bar{background:rgba(59,158,255,.12);color:var(--blue)}
.pb-cold{background:rgba(62,207,142,.12);color:var(--green)}
.right{background:var(--bg2);border-left:1px solid var(--border);display:flex;flex-direction:column;overflow:hidden}
.order-head{padding:12px 16px;border-bottom:1px solid var(--border);flex-shrink:0;background:var(--bg3)}
.order-meta-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px}
.order-num{font-family:'IBM Plex Mono',monospace;font-size:13px;font-weight:700;color:var(--text)}
.order-badges{display:flex;gap:5px}
.bdg{padding:3px 9px;border-radius:20px;font-size:11px;font-weight:700;display:inline-flex;align-items:center;gap:4px}
.bdg-blue{background:var(--blue-dim);color:var(--blue);border:1px solid rgba(37,99,235,.2)}
.bdg-gold{background:var(--gold-dim);color:var(--gold);border:1px solid rgba(217,119,6,.2)}
.bdg-green{background:var(--green-dim);color:var(--green);border:1px solid rgba(5,150,105,.2)}
.bdg-orange{background:rgba(251,146,60,.1);color:var(--orange);border:1px solid rgba(234,88,12,.2)}
.selects-row{display:flex;gap:6px}
.ord-sel{flex:1;background:var(--bg3);border:1.5px solid var(--border);border-radius:8px;padding:6px 8px;font-size:12px;color:var(--text2);outline:none;cursor:pointer;font-family:'DM Sans',sans-serif;font-weight:500}
.ord-sel:focus{border-color:var(--blue)}
select option{background:#fff;color:var(--text)}
.table-btn{display:flex;align-items:center;gap:6px;padding:7px 14px;border-radius:10px;border:1.5px solid var(--border);background:var(--bg3);color:var(--text);font-size:12px;font-weight:700;cursor:pointer;transition:all .2s;font-family:'DM Sans',sans-serif}
.table-btn:hover{border-color:var(--blue);color:var(--blue);background:var(--blue-dim)}
.table-btn:active{transform:scale(.96)}
.table-btn i{font-size:14px}
.cart{flex:1;overflow-y:auto;padding:10px 12px;display:flex;flex-direction:column;gap:6px;scrollbar-width:thin;scrollbar-color:var(--border2) transparent;background:var(--bg2)}
.cart::-webkit-scrollbar{width:4px}
.cart::-webkit-scrollbar-thumb{background:var(--border2);border-radius:3px}
.empty-cart{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;gap:14px;color:var(--text3)}
.empty-cart i{font-size:48px;opacity:.12}
.empty-cart p{font-size:14px;opacity:.35;font-weight:500;letter-spacing:.01em}
.empty-cart span{font-size:11px;opacity:.25;font-weight:600;text-transform:uppercase;letter-spacing:.06em}
.ci{background:var(--card-bg);border:1px solid var(--border);border-radius:12px;padding:10px 12px;transition:all .15s;box-shadow:var(--card-shadow)}
.ci:hover{border-color:var(--border2)}
.ci-row1{display:flex;align-items:center;gap:8px}
.ci-name{flex:1;font-size:13px;font-weight:700;color:var(--text);line-height:1.3}
.ci-qty-wrap{display:flex;align-items:center;gap:4px}
.ci-btn{width:32px;height:32px;border-radius:8px;background:var(--bg4);border:1.5px solid var(--border);color:var(--text2);cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:16px;transition:all .15s;flex-shrink:0;font-weight:700}
.ci-btn:hover{background:var(--blue-dim);color:var(--blue);border-color:rgba(37,99,235,.25)}
.ci-btn:active{transform:scale(.9)}
.ci-n{width:28px;text-align:center;font-size:14px;font-weight:800;font-family:'IBM Plex Mono',monospace;color:var(--text)}
.ci-price{font-size:13px;font-weight:800;color:var(--blue);font-family:'IBM Plex Mono',monospace;white-space:nowrap;min-width:60px;text-align:right}
.ci-void-btn{background:none;border:none;color:var(--text3);cursor:pointer;font-size:16px;padding:4px;border-radius:6px;transition:all .15s;flex-shrink:0}
.ci-void-btn:hover{color:var(--red);background:var(--red-dim)}
.ci-void-btn:active{transform:scale(.85)}
.ci-row2{display:flex;align-items:center;gap:8px;margin-top:6px;flex-wrap:wrap}
.ci-station{font-size:10px;font-weight:800;padding:2px 8px;border-radius:6px;flex-shrink:0;text-transform:uppercase;letter-spacing:.03em}
.cs-k{background:rgba(217,119,6,.12);color:var(--gold)}.cs-b{background:var(--blue-dim);color:var(--blue)}.cs-c{background:rgba(5,150,105,.12);color:var(--green)}
.ci-note-btn{font-size:11px;color:var(--text3);cursor:pointer;font-style:italic;transition:color .15s;background:none;border:none;text-align:left;padding:3px 6px;border-radius:6px;font-weight:500}
.ci-note-btn:hover{color:var(--blue);background:var(--blue-dim)}
.ci-kds-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;margin-left:auto}
.kds-pending{background:var(--text3)}.kds-cooking{background:var(--orange)}.kds-ready{background:var(--green)}
.totals{padding:16px;border-top:1px solid var(--border);flex-shrink:0;background:var(--bg3)}
.tot-row{display:flex;justify-content:space-between;font-size:13px;color:var(--text2);padding:4px 0;font-weight:500}
.tot-row.disc-row{color:var(--gold);font-weight:700}
.tot-divider{height:1.5px;background:var(--border);margin:8px 0}
.tot-total{display:flex;justify-content:space-between;font-size:20px;font-weight:800;padding:4px 0;margin-top:4px}
.tot-total-val{color:var(--blue);font-family:'IBM Plex Mono',monospace}
.pay-area{padding:10px 16px 12px;border-top:1px solid var(--border);flex-shrink:0;background:var(--bg3)}
.pay-main-btn{width:100%;padding:14px;border-radius:12px;border:none;background:linear-gradient(135deg,var(--green),#047857);color:#fff;font-size:15px;font-weight:800;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:all .2s;user-select:none;letter-spacing:.02em;box-shadow:0 4px 16px rgba(5,150,105,.2);font-family:'Syne',sans-serif}
.pay-main-btn:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(5,150,105,.3)}
.pay-main-btn:active{transform:scale(.97)}
.pay-main-btn:disabled{opacity:.4;cursor:not-allowed;transform:none;box-shadow:none}
.pay-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px}
.pay-grid-btn{padding:14px 4px;border-radius:12px;font-size:13px;font-weight:700;cursor:pointer;border:none;display:flex;align-items:center;justify-content:center;gap:6px;transition:all .2s;user-select:none}
.pay-grid-btn:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,.1)}
.pay-grid-btn:active{transform:scale(.96)}
.pgb-mpesa{background:linear-gradient(135deg,#00c264,#00904a);color:#fff}
.pgb-cash{background:linear-gradient(135deg,var(--blue),#1a7fd4);color:#fff}
.pgb-card{background:rgba(167,139,250,.15);color:var(--purple);border:1.5px solid rgba(124,58,237,.25)}
.pgb-credit{background:var(--gold-dim);color:var(--gold);border:1.5px solid rgba(217,119,6,.25)}
.pgb-voucher{background:var(--bg3);color:var(--text2);border:1.5px solid var(--border)}
.action-row{display:flex;gap:5px;margin-bottom:8px}
.act-btn{flex:1;padding:8px 4px;border-radius:8px;border:1px solid var(--border);background:var(--card-bg);color:var(--text2);font-size:11px;font-weight:600;cursor:pointer;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:3px;user-select:none}
.act-btn:hover{background:var(--bg3);color:var(--text);border-color:var(--text3)}
.act-btn:active{transform:scale(.95)}
.act-btn.disc-btn:hover{background:var(--gold-dim);color:var(--gold);border-color:rgba(217,119,6,.25)}
.act-btn.hold-btn:hover{background:rgba(251,146,60,.1);color:var(--orange);border-color:rgba(234,88,12,.25)}
.act-btn.split-btn:hover{background:var(--blue-dim);color:var(--blue);border-color:rgba(37,99,235,.25)}
.order-btn{width:100%;padding:14px;border-radius:12px;border:none;background:var(--order-btn-bg);color:var(--order-btn-text);font-size:14px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;transition:all .2s;font-family:'Syne',sans-serif;letter-spacing:.02em;user-select:none;box-shadow:0 4px 16px rgba(0,0,0,.15)}
.order-btn:hover{filter:brightness(1.15);transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.2)}
.order-btn:active{transform:scale(.97)}
.order-btn:disabled{opacity:.35;cursor:not-allowed;transform:none;box-shadow:none}
.order-btn i{font-size:20px}
.order-btn.pulse-ring{animation:orderPulse 2s ease-in-out infinite}
@keyframes orderPulse{0%,100%{box-shadow:0 4px 16px rgba(0,0,0,.15)}50%{box-shadow:0 4px 24px rgba(245,166,35,.3),0 0 0 4px rgba(245,166,35,.1)}}
.modal-overlay{position:fixed;inset:0;background:var(--modal-backdrop);display:none;align-items:center;justify-content:center;z-index:100;backdrop-filter:blur(4px);padding:16px}
.modal-overlay.show{display:flex}
.modal{background:var(--bg2);border:1.5px solid var(--border);border-radius:16px;padding:24px;width:400px;animation:mIn .2s ease;max-height:90vh;overflow-y:auto}
.table-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px}
.table-cell{padding:16px 12px;border-radius:14px;border:2px solid var(--border);background:var(--bg3);cursor:pointer;transition:all .2s;text-align:center;position:relative;overflow:hidden}
.table-cell:hover{transform:translateY(-3px);box-shadow:0 6px 20px rgba(0,0,0,.1)}
.table-cell:active{transform:scale(.97)}
.table-cell.t-avail{border-color:rgba(5,150,105,.3);background:rgba(5,150,105,.06)}
.table-cell.t-avail:hover{border-color:var(--green)}
.table-cell.t-open{border-color:rgba(37,99,235,.3);background:var(--blue-dim)}
.table-cell.t-open:hover{border-color:var(--blue)}
.table-cell.t-kitchen{border-color:rgba(234,88,12,.3);background:rgba(234,88,12,.08)}
.table-cell.t-kitchen:hover{border-color:var(--orange)}
.table-cell.t-pay{border-color:rgba(220,38,38,.3);background:rgba(220,38,38,.06)}
.table-cell.t-pay:hover{border-color:var(--red)}
.tc-num{font-family:'IBM Plex Mono',monospace;font-size:18px;font-weight:800;margin-bottom:4px}
.tc-status{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;opacity:.7}
.tc-covers{font-size:11px;color:var(--text2);margin-top:6px;font-weight:500}
.tc-dot{position:absolute;top:8px;right:8px;width:8px;height:8px;border-radius:50%}
.t-avail .tc-dot{background:var(--green)}
.t-open .tc-dot{background:var(--blue)}
.t-kitchen .tc-dot{background:var(--orange)}
.t-pay .tc-dot{background:var(--red)}
@keyframes mIn{from{opacity:0;transform:scale(.96) translateY(10px)}to{opacity:1;transform:scale(1) translateY(0)}}
@keyframes flashIn{0%{transform:scale(1);box-shadow:0 0 0 0 rgba(37,99,235,.4)}50%{transform:scale(1.02);box-shadow:0 0 0 4px rgba(37,99,235,.15)}100%{transform:scale(1);box-shadow:0 0 0 0 rgba(37,99,235,0)}}
@keyframes pulseStock{0%,100%{opacity:1}50%{opacity:.6}}
.modal-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px}
.modal-title{font-family:'Syne',sans-serif;font-size:15px;font-weight:800}
.modal-x{background:none;border:none;color:var(--text3);font-size:20px;cursor:pointer;padding:4px 8px;border-radius:8px;transition:all .15s}
.modal-x:hover{color:var(--text);background:var(--bg3)}
.modal-footer{display:flex;gap:8px;justify-content:flex-end;margin-top:20px}
.lbl{font-size:11px;font-weight:800;color:var(--text3);letter-spacing:.06em;text-transform:uppercase;margin-bottom:6px}
.field-inp{width:100%;background:var(--bg3);border:1.5px solid var(--border);border-radius:10px;padding:10px 14px;font-size:14px;color:var(--text);outline:none;font-family:'DM Sans',sans-serif;margin-bottom:14px;font-weight:500}
.field-inp:focus{border-color:var(--blue)}
select.field-inp option{background:#1c2330}
.btn{padding:9px 16px;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;border:none;display:inline-flex;align-items:center;gap:6px;transition:all .15s;user-select:none}
.btn:hover{filter:brightness(1.1)}
.btn:active{transform:scale(.95)}
.btn-p{background:var(--blue);color:#fff}
.btn-g{background:var(--bg3);color:var(--text2);border:1.5px solid var(--border)}
.btn-g:hover{color:var(--text)}
.btn-r{background:var(--red-dim);color:var(--red);border:1.5px solid rgba(220,38,38,.2)}
.btn-gold{background:var(--gold);color:#1a0f00;font-weight:800}
.btn-grn{background:var(--green-dim);color:var(--green);border:1.5px solid rgba(5,150,105,.2)}
.chip-row{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px}
.chip{padding:6px 12px;border-radius:8px;font-size:12px;background:var(--bg3);border:1.5px solid var(--border);color:var(--text2);cursor:pointer;transition:all .15s;font-weight:600}
.chip:hover{background:var(--blue-dim);color:var(--blue);border-color:rgba(37,99,235,.25)}
.chip:active{transform:scale(.95)}
.split-ways{display:flex;align-items:center;gap:16px;margin-bottom:16px}
.split-n{font-size:28px;font-weight:800;font-family:'IBM Plex Mono',monospace;color:var(--blue);min-width:36px;text-align:center}
.receipt{background:#fff;color:#1f2937;border-radius:12px;padding:20px;font-family:'IBM Plex Mono',monospace;font-size:11px}
.receipt-title{text-align:center;font-size:15px;font-weight:800;margin-bottom:3px}
.receipt-sub{text-align:center;font-size:10px;color:#666;margin-bottom:12px}
.r-div{border-top:1.5px dashed #ccc;margin:10px 0}
.r-row{display:flex;justify-content:space-between;padding:3px 0;font-size:11px}
.r-total{display:flex;justify-content:space-between;font-size:14px;font-weight:800;padding:4px 0}
.r-foot{text-align:center;font-size:10px;color:#888;margin-top:12px;line-height:1.5}
.pay-line{display:flex;gap:8px;align-items:center;margin-bottom:8px}
.pay-line select,.pay-line input{flex:1;background:var(--bg3);border:1.5px solid var(--border);border-radius:8px;padding:9px 12px;font-size:13px;color:var(--text);outline:none;font-weight:500}
.pay-line select:focus,.pay-line input:focus{border-color:var(--blue)}
.pay-rem{display:flex;justify-content:space-between;font-size:14px;font-weight:800;padding:10px 0 0;border-top:1.5px solid var(--border)}
.toast{position:fixed;bottom:24px;right:24px;background:var(--bg2);border:1.5px solid var(--border);border-radius:12px;padding:12px 18px;font-size:13px;display:flex;align-items:center;gap:10px;z-index:200;transform:translateY(80px);opacity:0;transition:all .3s cubic-bezier(.4,0,.2,1);pointer-events:none;font-weight:600;box-shadow:0 8px 32px rgba(0,0,0,.12)}
.toast.show{transform:translateY(0);opacity:1}

[hidden]{display:none!important}
.pos-flash{position:fixed;top:68px;right:18px;z-index:300;background:var(--bg2);color:var(--green);border:1.5px solid rgba(5,150,105,.25);box-shadow:0 8px 32px rgba(0,0,0,.12);border-radius:12px;padding:10px 14px;font-size:13px;font-weight:700}.pos-flash.error{color:var(--red);border-color:rgba(220,38,38,.25)}
.pos-empty-results{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:8px;min-height:220px;border:1.5px dashed var(--border2);border-radius:16px;color:var(--text3);background:var(--card-bg);text-align:center}.pos-empty-results[hidden]{display:none!important}.pos-empty-results i{font-size:34px;opacity:.45}.pos-empty-results strong{font-size:14px;color:var(--text)}.pos-empty-results span{font-size:12px;color:var(--text3)}
.pos-toast{position:fixed;right:24px;bottom:24px;z-index:220;background:var(--bg2);border:1.5px solid var(--border);box-shadow:0 8px 32px rgba(0,0,0,.12);color:var(--text);border-radius:12px;padding:12px 18px;font-size:13px;font-weight:700;animation:posToastIn .18s ease}.pos-toast.green{border-color:rgba(5,150,105,.35);color:var(--green)}.pos-toast.blue{border-color:rgba(37,99,235,.35);color:var(--blue)}.pos-toast.gold{border-color:rgba(217,119,6,.35);color:var(--gold)}
@keyframes posToastIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
</style>

<style>
.pos-app-frame{height:100vh;width:100vw;display:flex;min-width:0;overflow:hidden;background:var(--bg)}
.pos-app-frame>.dock{position:relative;z-index:50;flex:0 0 auto;height:100vh}
.pos-terminal-main{flex:1;min-width:0;height:100vh;display:flex;flex-direction:column;overflow:hidden;background:var(--bg)}
.pos-terminal-main>nav,.pos-terminal-main>.bills-bar{flex-shrink:0}
.pos-terminal-main>.shell{flex:1;min-height:0}
@media(max-width:860px){.pos-app-frame{display:block}.pos-app-frame>.dock{display:none}.pos-terminal-main{height:100vh;width:100vw}}
</style>
