@extends('layouts.app', ['title' => 'Recipes / BOM'])
@section('content')
<style>
    .rec-hero{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:16px}
    .rec-stat,.rec-card{border:1px solid var(--border);border-radius:20px;background:linear-gradient(180deg,rgba(43,37,38,.96),rgba(31,26,27,.98))}
    .rec-stat{padding:16px 18px}
    .rec-k{font-size:11px;color:var(--text3);text-transform:uppercase;letter-spacing:.08em}
    .rec-v{margin-top:8px;font-size:26px;font-weight:800;color:var(--text);font-family:'Space Mono',monospace}
    .rec-h{margin-top:6px;font-size:12px;color:var(--text2)}
    .rec-shell{display:grid;grid-template-columns:minmax(0,1.3fr) minmax(320px,.95fr);gap:16px}
    .rec-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:16px 18px;border-bottom:1px solid var(--border)}
    .rec-title{font-size:13px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
    .rec-sub{font-size:12px;color:var(--text3);margin-top:4px}
    .rec-pad{padding:16px 18px}
    .rec-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .rec-full{grid-column:1 / -1}
    .rec-lines{display:flex;flex-direction:column;gap:10px}
    .rec-line{display:grid;grid-template-columns:minmax(0,1.4fr) .7fr .7fr .7fr;gap:10px;align-items:end}
    .rec-list{display:flex;flex-direction:column;gap:12px}
    .recipe-card{padding:14px;border:1px solid var(--border);border-radius:16px;background:rgba(255,255,255,.025)}
    .recipe-card h4{margin:0;font-size:14px}
    .recipe-card p{margin:5px 0 0;color:var(--text3);font-size:11px}
    .recipe-meta{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}
    .rec-badge{display:inline-flex;align-items:center;padding:4px 8px;border-radius:999px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.06em}
    .rec-badge.blue{background:rgba(40,188,238,.12);color:var(--blue)}
    .rec-badge.gold{background:rgba(249,181,28,.14);color:var(--gold)}
    .rec-badge.green{background:rgba(62,207,142,.12);color:var(--green)}
    .rec-mini{display:flex;flex-direction:column;gap:10px}
    .rec-mini-item{display:flex;justify-content:space-between;gap:10px;padding:11px 0;border-bottom:1px solid var(--border)}
    .rec-mini-item:last-child{border-bottom:none}
    .rec-mini-item strong{font-size:13px}
    .rec-mini-item span{display:block;font-size:11px;color:var(--text3);margin-top:3px}
    .rec-note{margin-top:12px;padding:12px;border-radius:14px;background:rgba(40,188,238,.08);border:1px solid rgba(40,188,238,.16);font-size:12px;color:var(--text2);line-height:1.5}
    @media (max-width:1180px){.rec-hero{grid-template-columns:repeat(2,1fr)}.rec-shell{grid-template-columns:1fr}}
    @media (max-width:760px){.rec-hero,.rec-grid,.rec-line{grid-template-columns:1fr}}
</style>

<div class="rec-hero">
    <div class="rec-stat">
        <div class="rec-k">Recipes</div>
        <div class="rec-v">{{ $summary['recipes'] }}</div>
        <div class="rec-h">All BOM versions</div>
    </div>
    <div class="rec-stat">
        <div class="rec-k">Active Recipes</div>
        <div class="rec-v">{{ $summary['active_recipes'] }}</div>
        <div class="rec-h">Current production-ready BOMs</div>
    </div>
    <div class="rec-stat">
        <div class="rec-k">Ingredients</div>
        <div class="rec-v">{{ $summary['ingredients'] }}</div>
        <div class="rec-h">Raw and semi-finished components</div>
    </div>
    <div class="rec-stat">
        <div class="rec-k">Production Runs</div>
        <div class="rec-v">{{ $summary['production_runs'] }}</div>
        <div class="rec-h">Logged batch issues and outputs</div>
    </div>
</div>

<div class="rec-shell">
    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="rec-card">
            <div class="rec-head">
                <div>
                    <div class="rec-title">Recipe Builder</div>
                    <div class="rec-sub">Define how a finished or semi-finished item consumes ingredients.</div>
                </div>
            </div>
            <div class="rec-pad">
                <form method="post" action="{{ route('actions.recipe') }}">
                    @csrf
                    <div class="rec-grid">
                        <div>
                            <div class="lbl">Output Product</div>
                            <select class="inp" name="product_id" required>
                                <option value="">Choose product</option>
                                @foreach($finished as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <div class="lbl">Version</div>
                            <input class="inp" type="number" min="1" name="version" value="1">
                        </div>
                        <div>
                            <div class="lbl">Yield Quantity</div>
                            <input class="inp" type="number" step="0.0001" min="0.0001" name="yield_quantity" required>
                        </div>
                        <div>
                            <div class="lbl">Yield Unit</div>
                            <input class="inp" name="yield_unit" value="portion" required>
                        </div>
                    </div>

                    <div class="sec-head" style="margin:18px 0 12px"><span class="sec-title">Ingredients</span></div>
                    <div class="rec-lines">
                        @for($i = 0; $i < 6; $i++)
                            <div class="rec-line">
                                <div>
                                    <div class="lbl">Ingredient</div>
                                    <select class="inp" name="ingredient_product_id[]">
                                        <option value="">Choose ingredient</option>
                                        @foreach($ingredients as $ingredient)
                                            <option value="{{ $ingredient->id }}">{{ $ingredient->name }} · {{ $ingredient->unit }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <div class="lbl">Qty Required</div>
                                    <input class="inp" type="number" step="0.0001" min="0" name="quantity_required[]">
                                </div>
                                <div>
                                    <div class="lbl">Unit</div>
                                    <input class="inp" name="unit[]" value="pcs">
                                </div>
                                <div>
                                    <div class="lbl">Wastage %</div>
                                    <input class="inp" type="number" step="0.01" min="0" name="wastage_percent[]" value="0">
                                </div>
                            </div>
                        @endfor
                    </div>

                    <button class="btn btn-primary" style="margin-top:14px">Save Active Recipe</button>
                </form>
                <div class="rec-note">
                    Saving a new active recipe archives the previous active BOM for that product, so your costing and ingredient logic can evolve without losing history.
                </div>
            </div>
        </div>

        <div class="rec-card">
            <div class="rec-head">
                <div>
                    <div class="rec-title">Active BOM Register</div>
                    <div class="rec-sub">See how each output item is composed and what it will consume during production or sale.</div>
                </div>
            </div>
            <div class="rec-pad">
                <div class="rec-list">
                    @forelse($recipes as $recipe)
                        @php
                            $estimatedCost = $recipe->items->sum(fn ($item) => (float) ($item->quantity_required ?? 0) * (float) ($item->ingredient->cost_price ?? 0));
                        @endphp
                        <div class="recipe-card">
                            <h4>{{ $recipe->product->name ?? 'Output Item' }}</h4>
                            <p>Yield {{ number_format((float) $recipe->yield_quantity, 2) }} {{ $recipe->yield_unit }} · version {{ $recipe->version }}</p>
                            <div class="recipe-meta">
                                <span class="rec-badge {{ $recipe->status === 'active' ? 'green' : 'gold' }}">{{ $recipe->status }}</span>
                                <span class="rec-badge blue">{{ $recipe->items->count() }} ingredients</span>
                                <span class="rec-badge gold">Est. KES {{ number_format($estimatedCost, 2) }}</span>
                            </div>
                            <div style="margin-top:12px;color:var(--text2);font-size:12px;line-height:1.6">
                                @foreach($recipe->items as $item)
                                    <div>{{ number_format((float) $item->quantity_required, 4) }} {{ $item->unit }} {{ $item->ingredient->name ?? '-' }} @if((float) $item->wastage_percent > 0) · {{ number_format((float) $item->wastage_percent, 2) }}% wastage @endif</div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div style="color:var(--text3);font-size:12px">No recipes saved yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div style="display:flex;flex-direction:column;gap:16px">
        <div class="rec-card">
            <div class="rec-head">
                <div>
                    <div class="rec-title">Production Issue</div>
                    <div class="rec-sub">Run a batch and post both ingredient consumption and finished output to stock.</div>
                </div>
            </div>
            <div class="rec-pad">
                <form method="post" action="{{ route('actions.production') }}">
                    @csrf
                    <div class="rec-grid">
                        <div class="rec-full">
                            <div class="lbl">Recipe</div>
                            <select class="inp" name="recipe_id" required>
                                <option value="">Choose active recipe</option>
                                @foreach($recipes->where('status', 'active') as $recipe)
                                    <option value="{{ $recipe->id }}">{{ $recipe->product->name ?? 'Output Item' }} · yields {{ number_format((float) $recipe->yield_quantity, 2) }} {{ $recipe->yield_unit }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <div class="lbl">Planned Output Quantity</div>
                            <input class="inp" type="number" step="0.0001" min="0.0001" name="planned_quantity" required>
                        </div>
                        <div class="rec-full">
                            <div class="lbl">Notes</div>
                            <textarea class="inp" rows="3" name="notes" placeholder="Shift, station, batch notes, or production remarks."></textarea>
                        </div>
                    </div>
                    <button class="btn btn-primary">Post Production Run</button>
                </form>
            </div>
        </div>

        <div class="rec-card">
            <div class="rec-head">
                <div>
                    <div class="rec-title">Recent Production Runs</div>
                    <div class="rec-sub">Latest batches issued into stock.</div>
                </div>
            </div>
            <div class="rec-pad">
                <div class="rec-mini">
                    @forelse($productionRuns as $run)
                        <div class="rec-mini-item">
                            <div>
                                <strong>{{ $run->product->name ?? ($run->recipe->product->name ?? '-') }}</strong>
                                <span>{{ $run->created_at?->format('d M H:i') }} · {{ $run->actor?->name ?? 'System' }}</span>
                            </div>
                            <div class="rec-badge green">{{ number_format((float) $run->planned_quantity, 2) }} {{ $run->meta['yield_unit'] ?? ($run->recipe->yield_unit ?? '') }}</div>
                        </div>
                    @empty
                        <div style="color:var(--text3);font-size:12px">No production runs logged yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
