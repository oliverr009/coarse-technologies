<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CreditAccount;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\HotelFolio;
use App\Models\HotelReservation;
use App\Models\HotelRoom;
use App\Models\HotelRoomType;
use App\Models\InventoryAdjustment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PosAuditLog;
use App\Models\Product;
use App\Models\ProductionRun;
use App\Models\Purchase;
use App\Models\Recipe;
use App\Models\Reservation;
use App\Models\RestaurantTable;
use App\Models\Sale;
use App\Models\SaleAdjustment;
use App\Models\Setting;
use App\Models\Shift;
use App\Models\ShiftCashEntry;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Services\RolePermissionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModuleController extends Controller
{
    public function inventory()
    {
        $products = Product::query()
            ->with('category')
            ->withSum('stockLevels as stock_qty', 'quantity')
            ->orderBy('name')
            ->get();

        $trackedProducts = $products->whereIn('product_type', ['raw_material', 'resale_item', 'semi_finished']);
        $stockValue = $trackedProducts->sum(function ($product) {
            return (float) ($product->stock_qty ?? 0) * (float) ($product->cost_price ?? 0);
        });

        return view('modules.inventory', [
            'products' => $products,
            'categories' => Category::query()->orderBy('name')->get(),
            'movements' => StockMovement::query()->with('product')->latest()->limit(18)->get(),
            'adjustments' => InventoryAdjustment::query()->with(['product', 'actor'])->latest()->limit(12)->get(),
            'lowStock' => $trackedProducts
                ->filter(fn ($product) => (float) ($product->stock_qty ?? 0) <= (float) $product->reorder_level)
                ->sortBy('stock_qty')
                ->take(8)
                ->values(),
            'negativeStock' => $trackedProducts
                ->filter(fn ($product) => (float) ($product->stock_qty ?? 0) < 0)
                ->sortBy('stock_qty')
                ->take(8)
                ->values(),
            'summary' => [
                'products' => $products->count(),
                'menu_items' => $products->whereIn('product_type', ['finished_product', 'resale_item'])->count(),
                'raw_materials' => $products->where('product_type', 'raw_material')->count(),
                'low_stock' => $trackedProducts->filter(fn ($product) => (float) ($product->stock_qty ?? 0) <= (float) $product->reorder_level)->count(),
                'negative_stock' => $trackedProducts->filter(fn ($product) => (float) ($product->stock_qty ?? 0) < 0)->count(),
                'stock_value' => $stockValue,
                'adjustments' => InventoryAdjustment::query()->count(),
            ],
        ]);
    }

    public function tables()
    {
        $tables = RestaurantTable::query()
            ->with(['orders' => fn ($query) => $query->with('items')->whereIn('status', ['held', 'sent'])->latest()])
            ->orderBy('name')
            ->get();

        $reservations = Reservation::query()
            ->with('table')
            ->whereIn('status', ['booked', 'arrived'])
            ->orderBy('reserved_for')
            ->limit(20)
            ->get();

        return view('modules.tables', [
            'tables' => $tables,
            'reservations' => $reservations,
            'summary' => [
                'available' => $tables->where('status', 'available')->count(),
                'occupied' => $tables->where('status', 'occupied')->count(),
                'reserved' => $tables->where('status', 'reserved')->count(),
                'needs_cleaning' => $tables->where('status', 'needs_cleaning')->count(),
            ],
        ]);
    }

    public function kds()
    {
        $items = OrderItem::query()
            ->with(['order.table'])
            ->whereIn('kitchen_status', ['pending', 'preparing', 'ready'])
            ->latest()
            ->get();

        return view('modules.kds', [
            'items' => $items,
            'summary' => [
                'pending' => $items->where('kitchen_status', 'pending')->count(),
                'preparing' => $items->where('kitchen_status', 'preparing')->count(),
                'ready' => $items->where('kitchen_status', 'ready')->count(),
                'tables' => $items->pluck('order.restaurant_table_id')->filter()->unique()->count(),
            ],
        ]);
    }

    public function recipes()
    {
        $recipes = Recipe::query()->with('product', 'items.ingredient')->latest()->get();

        return view('modules.recipes', [
            'recipes' => $recipes,
            'finished' => Product::query()->whereIn('product_type', ['finished_product', 'semi_finished'])->orderBy('name')->get(),
            'ingredients' => Product::query()->whereIn('product_type', ['raw_material', 'semi_finished'])->orderBy('name')->get(),
            'productionRuns' => ProductionRun::query()->with(['recipe.product', 'actor'])->latest()->limit(18)->get(),
            'summary' => [
                'recipes' => $recipes->count(),
                'active_recipes' => $recipes->where('status', 'active')->count(),
                'ingredients' => Product::query()->whereIn('product_type', ['raw_material', 'semi_finished'])->count(),
                'production_runs' => ProductionRun::query()->count(),
            ],
        ]);
    }

    public function purchases()
    {
        $purchases = Purchase::query()->with(['items.product', 'supplier'])->latest()->get();
        $thisMonth = now()->startOfMonth();
        $topSuppliers = $purchases
            ->filter(fn ($purchase) => $purchase->supplier)
            ->groupBy('supplier_id')
            ->map(function ($rows) {
                $supplier = $rows->first()->supplier;

                return (object) [
                    'supplier' => $supplier,
                    'purchase_count' => $rows->count(),
                    'total_amount' => (float) $rows->sum('total_amount'),
                    'last_purchase_at' => $rows->max('created_at'),
                ];
            })
            ->sortByDesc('total_amount')
            ->take(6)
            ->values();

        return view('modules.purchases', [
            'purchases' => $purchases,
            'products' => Product::query()->whereIn('product_type', ['raw_material', 'resale_item', 'semi_finished'])->orderBy('name')->get(),
            'suppliers' => Supplier::query()->orderBy('name')->get(),
            'movements' => StockMovement::query()->with('product')->where('movement_type', 'PURCHASE')->latest()->limit(12)->get(),
            'topSuppliers' => $topSuppliers,
            'summary' => [
                'purchases' => $purchases->count(),
                'value' => $purchases->sum('total_amount'),
                'items' => $purchases->sum(fn ($purchase) => $purchase->items->count()),
                'suppliers' => Supplier::query()->count(),
                'month_value' => (float) $purchases->where('created_at', '>=', $thisMonth)->sum('total_amount'),
                'avg_receipt' => (float) ($purchases->count() ? ($purchases->sum('total_amount') / $purchases->count()) : 0),
            ],
        ]);
    }

    public function expenses()
    {
        return view('modules.expenses', ['expenses' => Expense::query()->latest()->get()]);
    }

    public function credit()
    {
        $customers = Customer::query()
            ->withSum('creditAccounts as credit_balance', 'amount')
            ->withCount('sales')
            ->orderBy('name')
            ->get();
        $credits = CreditAccount::query()->with(['customer', 'sale'])->latest()->get();
        $monthStart = now()->startOfMonth();
        $totalOutstanding = (float) $credits->sum('amount');
        $overdue = (float) $credits
            ->filter(fn ($entry) => (float) $entry->amount > 0 && $entry->due_date && $entry->due_date->isPast() && ! $entry->due_date->isToday())
            ->sum('amount');
        $collectionsThisMonth = (float) abs($credits
            ->filter(fn ($entry) => (float) $entry->amount < 0 && $entry->created_at >= $monthStart)
            ->sum('amount'));
        $nearLimit = $customers
            ->filter(function ($customer) {
                $limit = (float) $customer->credit_limit;

                return $limit > 0 && (float) ($customer->credit_balance ?? 0) >= ($limit * 0.8);
            })
            ->count();
        $creditWatchlist = $customers
            ->sortByDesc(function ($customer) {
                return (float) ($customer->credit_balance ?? 0);
            })
            ->take(8);

        return view('modules.credit', [
            'customers' => $customers,
            'credits' => $credits,
            'creditWatchlist' => $creditWatchlist,
            'summary' => [
                'customers' => $customers->count(),
                'outstanding' => $totalOutstanding,
                'overdue' => $overdue,
                'collections_month' => $collectionsThisMonth,
                'near_limit' => $nearLimit,
            ],
        ]);
    }

    public function reports()
    {
        $today = now()->startOfDay();
        $salesToday = Sale::query()->where('created_at', '>=', $today)->get();
        $liveOrders = Order::query()->with(['table', 'customer'])->whereIn('status', ['held', 'sent', 'ready'])->latest()->limit(12)->get();
        $tables = RestaurantTable::query()->get();
        $reservationsToday = Reservation::query()->with('table')->whereDate('reserved_for', today())->orderBy('reserved_for')->limit(12)->get();
        $trackedProducts = Product::query()
            ->withSum('stockLevels as stock_qty', 'quantity')
            ->whereIn('product_type', ['raw_material', 'resale_item', 'semi_finished'])
            ->get();

        return view('modules.reports', [
            'movements' => StockMovement::query()->with('product')->latest()->limit(80)->get(),
            'consumption' => StockMovement::query()
                ->join('products', 'products.id', '=', 'stock_movements.product_id')
                ->where('movement_type', 'SALE_CONSUMPTION')
                ->selectRaw('products.name, products.unit, SUM(ABS(stock_movements.quantity)) qty, SUM(COALESCE(stock_movements.total_cost,0)) cost')
                ->groupBy('products.name', 'products.unit')
                ->orderByDesc('qty')
                ->get(),
            'expenses' => Expense::query()->sum('amount'),
            'auditLogs' => PosAuditLog::query()->with(['actor', 'approver', 'order', 'sale'])->latest()->limit(20)->get(),
            'saleAdjustments' => SaleAdjustment::query()->with(['sale', 'actor', 'approver', 'items'])->latest()->limit(20)->get(),
            'inventoryAdjustments' => InventoryAdjustment::query()->with(['product', 'actor'])->latest()->limit(20)->get(),
            'opsSummary' => [
                'sales_today_count' => $salesToday->count(),
                'sales_today_value' => (float) $salesToday->sum('total_amount'),
                'live_orders' => $liveOrders->count(),
                'kitchen_pending' => OrderItem::query()->whereIn('kitchen_status', ['pending', 'preparing'])->count(),
                'tables_occupied' => $tables->where('status', 'occupied')->count(),
                'tables_cleaning' => $tables->where('status', 'needs_cleaning')->count(),
                'reservations_today' => $reservationsToday->count(),
                'stock_alerts' => $trackedProducts->filter(fn ($product) => (float) ($product->stock_qty ?? 0) <= (float) $product->reorder_level)->count(),
            ],
            'liveOrders' => $liveOrders,
            'tableSummary' => [
                'available' => $tables->where('status', 'available')->count(),
                'occupied' => $tables->where('status', 'occupied')->count(),
                'reserved' => $tables->where('status', 'reserved')->count(),
                'needs_cleaning' => $tables->where('status', 'needs_cleaning')->count(),
            ],
            'reservationsToday' => $reservationsToday,
            'stockAlerts' => $trackedProducts
                ->filter(fn ($product) => (float) ($product->stock_qty ?? 0) <= (float) $product->reorder_level)
                ->sortBy('stock_qty')
                ->take(12)
                ->values(),
        ]);
    }

    public function users(RolePermissionService $permissions)
    {
        $permissions->authorize(request()->user(), 'users');
        $users = User::query()->orderBy('name')->get();
        $roles = $permissions->roles();
        $matrix = collect($permissions->matrix())
            ->map(fn ($allowedRoles, $ability) => [
                'ability' => $ability,
                'label' => ucwords(str_replace(['_', '-'], ' ', $ability)),
                'roles' => collect($roles)->filter(fn ($label, $role) => in_array($role, $allowedRoles, true))->keys()->values()->all(),
            ])
            ->values();

        return view('modules.users', [
            'users' => $users,
            'roles' => $roles,
            'matrix' => $matrix,
            'summary' => [
                'active' => $users->where('is_active', true)->count(),
                'inactive' => $users->where('is_active', false)->count(),
                'admins' => $users->where('role', 'admin')->count(),
                'managers' => $users->where('role', 'manager')->count(),
                'cashiers' => $users->where('role', 'cashier')->count(),
                'kitchen' => $users->where('role', 'kitchen')->count(),
            ],
        ]);
    }

    public function settings(RolePermissionService $permissions)
    {
        $permissions->authorize(request()->user(), 'settings');
        $stored = Setting::query()->pluck('value', 'key');
        $settings = collect([
            'business_name' => 'Coarse Restaurant - Main Branch',
            'branch_name' => 'Main Branch',
            'business_phone' => '',
            'business_email' => '',
            'business_address' => '',
            'kra_pin' => '',
            'currency' => 'KES',
            'tax_rate' => 16,
            'service_charge_rate' => 10,
            'service_charge_enabled' => true,
            'receipt_footer' => 'Thank you for dining with us.',
            'receipt_prefix' => 'COARSE POS',
            'discount_approval_threshold' => 10,
            'void_requires_manager' => true,
            'refund_requires_manager' => true,
            'payment_cash_enabled' => true,
            'payment_mpesa_enabled' => true,
            'payment_card_enabled' => true,
            'payment_credit_enabled' => true,
            'default_order_type' => 'dine_in',
            'default_guest_count' => 1,
            'allow_negative_inventory' => false,
            'table_required_for_dine_in' => false,
            'receipt_printer_name' => 'Front Cashier Receipt Printer',
            'receipt_printer_connection' => 'browser',
            'receipt_printer_target' => 'This browser / cashier station',
            'receipt_printer_paper' => '80mm',
            'receipt_printer_auto_print' => false,
            'receipt_printer_copies' => 1,
            'kitchen_printer_name' => 'Main Kitchen Printer',
            'kitchen_printer_connection' => 'browser',
            'kitchen_printer_target' => 'Kitchen ticket browser',
            'kitchen_printer_paper' => '80mm',
            'kitchen_printer_auto_print' => false,
            'kitchen_printer_copies' => 1,
            'bar_printer_name' => 'Bar Printer',
            'bar_printer_connection' => 'browser',
            'bar_printer_target' => 'Bar station browser',
            'bar_printer_paper' => '80mm',
            'bar_printer_auto_print' => false,
            'bar_printer_copies' => 1,
            'bar_categories_csv' => 'Bar,Drinks,Cocktails,Mocktails,Milkshakes,Tea & Chocolate,Barista Corner',
            'kitchen_categories_csv' => 'Mains,Sides,Breakfast,Snacks,Salads,Soups,Burgers,Sandwiches,Pizza,Desserts,Kids Menu',
            'print_reprint_requires_manager' => true,
            'print_logo_on_receipt' => true,
        ])->merge($stored);

        return view('modules.settings', [
            'settings' => $settings,
            'summary' => [
                'tax_rate' => (float) $settings['tax_rate'],
                'service_charge_rate' => $settings['service_charge_enabled'] ? (float) $settings['service_charge_rate'] : 0,
                'payment_methods' => collect([
                    'cash' => $settings['payment_cash_enabled'],
                    'mpesa' => $settings['payment_mpesa_enabled'],
                    'card' => $settings['payment_card_enabled'],
                    'credit' => $settings['payment_credit_enabled'],
                ])->filter()->count(),
                'approval_threshold' => (float) $settings['discount_approval_threshold'],
            ],
            'printerSummary' => [
                'auto_routes' => collect([$settings['receipt_printer_auto_print'], $settings['kitchen_printer_auto_print'], $settings['bar_printer_auto_print']])->filter()->count(),
                'browser_routes' => collect([$settings['receipt_printer_connection'], $settings['kitchen_printer_connection'], $settings['bar_printer_connection']])->filter(fn ($value) => $value === 'browser')->count(),
                'bar_categories' => collect(explode(',', (string) $settings['bar_categories_csv']))->filter(fn ($value) => trim($value) !== '')->count(),
                'kitchen_categories' => collect(explode(',', (string) $settings['kitchen_categories_csv']))->filter(fn ($value) => trim($value) !== '')->count(),
            ],
        ]);
    }

    public function printers(RolePermissionService $permissions)
    {
        $permissions->authorize(request()->user(), 'printers');
        $stored = Setting::query()->pluck('value', 'key');
        $settings = collect([
            'receipt_printer_name' => 'Front Cashier Receipt Printer',
            'receipt_printer_connection' => 'browser',
            'receipt_printer_target' => 'This browser / cashier station',
            'receipt_printer_paper' => '80mm',
            'receipt_printer_auto_print' => false,
            'receipt_printer_copies' => 1,
            'kitchen_printer_name' => 'Main Kitchen Printer',
            'kitchen_printer_connection' => 'browser',
            'kitchen_printer_target' => 'Kitchen ticket browser',
            'kitchen_printer_paper' => '80mm',
            'kitchen_printer_auto_print' => false,
            'kitchen_printer_copies' => 1,
            'bar_printer_name' => 'Bar Printer',
            'bar_printer_connection' => 'browser',
            'bar_printer_target' => 'Bar station browser',
            'bar_printer_paper' => '80mm',
            'bar_printer_auto_print' => false,
            'bar_printer_copies' => 1,
            'bar_categories_csv' => 'Bar,Drinks,Cocktails,Mocktails,Milkshakes,Tea & Chocolate,Barista Corner',
            'kitchen_categories_csv' => 'Mains,Sides,Breakfast,Snacks,Salads,Soups,Burgers,Sandwiches,Pizza,Desserts,Kids Menu',
            'print_reprint_requires_manager' => true,
            'print_logo_on_receipt' => true,
        ])->merge($stored);

        return view('modules.printers', [
            'settings' => $settings,
            'summary' => [
                'auto_routes' => collect([$settings['receipt_printer_auto_print'], $settings['kitchen_printer_auto_print'], $settings['bar_printer_auto_print']])->filter()->count(),
                'browser_routes' => collect([$settings['receipt_printer_connection'], $settings['kitchen_printer_connection'], $settings['bar_printer_connection']])->filter(fn ($value) => $value === 'browser')->count(),
                'bar_categories' => collect(explode(',', (string) $settings['bar_categories_csv']))->filter(fn ($value) => trim($value) !== '')->count(),
                'kitchen_categories' => collect(explode(',', (string) $settings['kitchen_categories_csv']))->filter(fn ($value) => trim($value) !== '')->count(),
            ],
        ]);
    }

    public function shifts()
    {
        $activeShift = Shift::query()
            ->with(['cashier', 'cashEntries.actor'])
            ->where('user_id', request()->user()->id)
            ->where('status', 'open')
            ->latest('opened_at')
            ->first();

        $recentShifts = Shift::query()
            ->with(['cashier', 'closer', 'cashEntries'])
            ->when(! in_array(request()->user()->role, ['admin', 'manager'], true), function ($query) {
                $query->where('user_id', request()->user()->id);
            })
            ->latest('opened_at')
            ->limit(20)
            ->get();

        $summary = [
            'open_shifts' => Shift::query()->where('status', 'open')->count(),
            'today_shifts' => Shift::query()->whereDate('opened_at', today())->count(),
            'today_variance' => (float) Shift::query()->whereDate('opened_at', today())->sum('variance_amount'),
            'cash_entries' => ShiftCashEntry::query()->whereDate('created_at', today())->count(),
        ];

        return view('modules.shifts', compact('activeShift', 'recentShifts', 'summary'));
    }

    public function hotel(RolePermissionService $permissions)
    {
        $permissions->authorize(request()->user(), 'hotel');
        $today = today();

        $legacyReservations = Reservation::query()
            ->with('table')
            ->orderBy('reserved_for')
            ->limit(8)
            ->get();

        $roomTypes = Schema::hasTable('hotel_room_types')
            ? HotelRoomType::query()->withCount('rooms')->where('is_active', true)->orderBy('name')->get()
            : collect([
                (object) ['name' => 'Standard King', 'code' => 'STD-K', 'base_rate' => 6200, 'max_occupancy' => 2, 'rooms_count' => 8],
                (object) ['name' => 'Deluxe Twin', 'code' => 'DLX-T', 'base_rate' => 7800, 'max_occupancy' => 2, 'rooms_count' => 6],
                (object) ['name' => 'Executive Suite', 'code' => 'EXE-S', 'base_rate' => 12800, 'max_occupancy' => 3, 'rooms_count' => 3],
                (object) ['name' => 'Garden Suite', 'code' => 'GDN-S', 'base_rate' => 15400, 'max_occupancy' => 4, 'rooms_count' => 2],
            ]);

        $rooms = Schema::hasTable('hotel_rooms')
            ? HotelRoom::query()->with(['roomType', 'folios' => fn ($query) => $query->where('status', 'open')])->where('is_active', true)->orderBy('room_number')->get()
            : collect([
                (object) ['room_number' => '101', 'floor' => '1', 'status' => 'occupied', 'housekeeping_status' => 'clean', 'active_guest_name' => 'In-house Guest', 'active_folio_balance' => 12400, 'current_rate' => 6200, 'roomType' => (object) ['name' => 'Standard King']],
                (object) ['room_number' => '102', 'floor' => '1', 'status' => 'vacant_clean', 'housekeeping_status' => 'clean', 'active_guest_name' => null, 'active_folio_balance' => 0, 'current_rate' => 6200, 'roomType' => (object) ['name' => 'Standard King']],
                (object) ['room_number' => '201', 'floor' => '2', 'status' => 'reserved', 'housekeeping_status' => 'inspected', 'active_guest_name' => 'Late Arrival', 'active_folio_balance' => 28500, 'current_rate' => 12800, 'roomType' => (object) ['name' => 'Executive Suite']],
                (object) ['room_number' => '202', 'floor' => '2', 'status' => 'dirty', 'housekeeping_status' => 'dirty', 'active_guest_name' => null, 'active_folio_balance' => 0, 'current_rate' => 7800, 'roomType' => (object) ['name' => 'Deluxe Twin']],
                (object) ['room_number' => '301', 'floor' => '3', 'status' => 'occupied', 'housekeeping_status' => 'clean', 'active_guest_name' => 'Walk-in Guest', 'active_folio_balance' => 19800, 'current_rate' => 15400, 'roomType' => (object) ['name' => 'Garden Suite']],
                (object) ['room_number' => '302', 'floor' => '3', 'status' => 'out_of_order', 'housekeeping_status' => 'out_of_order', 'active_guest_name' => null, 'active_folio_balance' => 0, 'current_rate' => 0, 'roomType' => (object) ['name' => 'Garden Suite']],
            ]);

        $hotelReservations = Schema::hasTable('hotel_reservations')
            ? HotelReservation::query()
                ->with(['roomType', 'room'])
                ->whereIn('status', ['confirmed', 'checked_in'])
                ->orderBy('check_in_date')
                ->limit(30)
                ->get()
            : collect();

        $openFolios = Schema::hasTable('hotel_folios')
            ? HotelFolio::query()
                ->with(['room.roomType', 'reservation', 'items'])
                ->where('status', 'open')
                ->orderBy('expected_checkout_at')
                ->get()
            : collect();

        $recentFolios = Schema::hasTable('hotel_folios')
            ? HotelFolio::query()
                ->with(['room.roomType'])
                ->latest('updated_at')
                ->limit(10)
                ->get()
            : collect();

        $availableByType = $roomTypes->map(function ($type) use ($rooms) {
            $typeRooms = $rooms->where('room_type_id', $type->id ?? null);
            if (! isset($type->id)) {
                $typeRooms = $rooms->filter(fn ($room) => ($room->roomType->name ?? null) === $type->name);
            }

            return (object) [
                'name' => $type->name,
                'base_rate' => $type->base_rate ?? 0,
                'available' => $typeRooms->where('status', 'vacant_clean')->count(),
                'reserved' => $typeRooms->where('status', 'reserved')->count(),
                'occupied' => $typeRooms->where('status', 'occupied')->count(),
                'dirty' => $typeRooms->where('status', 'dirty')->count(),
            ];
        });

        $guestProfiles = Customer::query()
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get()
            ->map(function ($customer) use ($legacyReservations, $hotelReservations) {
                $reservationCount = $legacyReservations->where('customer_phone', $customer->phone)->count()
                    + $hotelReservations->where('guest_phone', $customer->phone)->count();

                return (object) [
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'profile_source' => 'Customer profile',
                    'activity_hint' => $reservationCount ? $reservationCount . ' linked reservation(s)' : 'Ready for stay history',
                ];
            });

        $reservationGuests = $hotelReservations
            ->map(fn ($reservation) => (object) [
                'name' => $reservation->guest_name,
                'phone' => $reservation->guest_phone,
                'email' => $reservation->guest_email,
                'profile_source' => 'Hotel reservation',
                'activity_hint' => ucfirst(str_replace('_', ' ', $reservation->status)) . ' · ' . $reservation->check_in_date?->format('d M') . ' to ' . $reservation->check_out_date?->format('d M'),
            ])
            ->concat($legacyReservations
                ->groupBy(fn ($reservation) => trim(strtolower(($reservation->customer_name ?? '') . '|' . ($reservation->customer_phone ?? ''))))
                ->filter(fn ($rows, $key) => $key !== '|')
                ->map(function ($rows) {
                    $reservation = $rows->first();

                    return (object) [
                        'name' => $reservation->customer_name ?: 'Unnamed guest',
                        'phone' => $reservation->customer_phone,
                        'email' => null,
                        'profile_source' => 'Restaurant reservation',
                        'activity_hint' => $rows->count() . ' reservation(s) on record',
                    ];
                })
                ->values());

        $checkInQueue = $hotelReservations
            ->where('status', 'confirmed')
            ->filter(fn ($reservation) => $reservation->check_in_date <= $today->copy()->addDays(7))
            ->values();

        $departureQueue = $openFolios
            ->filter(fn ($folio) => $folio->expected_checkout_at?->toDateString() <= $today->copy()->addDays(1)->toDateString())
            ->values();

        $legacyReservationGuests = $legacyReservations
            ->groupBy(fn ($reservation) => trim(strtolower(($reservation->customer_name ?? '') . '|' . ($reservation->customer_phone ?? ''))))
            ->filter(fn ($rows, $key) => $key !== '|')
            ->map(function ($rows) {
                $reservation = $rows->first();

                return (object) [
                    'name' => $reservation->customer_name ?: 'Unnamed guest',
                    'phone' => $reservation->customer_phone,
                    'email' => null,
                    'profile_source' => 'Reservation feed',
                    'activity_hint' => $rows->count() . ' reservation(s) on record',
                ];
            })
            ->values();

        $guestProfiles = $guestProfiles
            ->concat($reservationGuests)
            ->unique(fn ($guest) => trim(strtolower(($guest->name ?? '') . '|' . ($guest->phone ?? ''))))
            ->take(12)
            ->values();

        return view('modules.hotel', [
            'roomTypes' => $roomTypes,
            'rooms' => $rooms,
            'guestProfiles' => $guestProfiles,
            'reservations' => $legacyReservations,
            'hotelReservations' => $hotelReservations,
            'openFolios' => $openFolios,
            'recentFolios' => $recentFolios,
            'availableByType' => $availableByType,
            'checkInQueue' => $checkInQueue,
            'departureQueue' => $departureQueue,
            'summary' => [
                'occupied' => $rooms->where('status', 'occupied')->count(),
                'reserved' => $rooms->where('status', 'reserved')->count(),
                'vacant_clean' => $rooms->where('status', 'vacant_clean')->count(),
                'dirty' => $rooms->where('status', 'dirty')->count(),
                'out_of_order' => $rooms->where('status', 'out_of_order')->count(),
                'folios' => $rooms->filter(fn ($room) => (float) ($room->active_folio_balance ?? 0) > 0)->count(),
                'reservation_feed' => $hotelReservations->count() + $legacyReservations->count(),
                'room_types' => $roomTypes->count(),
                'guest_profiles' => $guestProfiles->count(),
                'inventory_ready' => $rooms->where('status', 'vacant_clean')->count(),
                'arrivals_today' => $hotelReservations->where('check_in_date', $today)->where('status', 'confirmed')->count(),
                'departures_today' => $openFolios->filter(fn ($folio) => $folio->expected_checkout_at?->toDateString() === $today->toDateString())->count(),
                'open_balance' => (float) $openFolios->sum('balance'),
            ],
        ]);
    }
}
