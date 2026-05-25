<?php

use App\Http\Controllers\ActionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\PosController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('/orders', [PosController::class, 'orders'])->name('orders');
    Route::post('/pos/sale', [PosController::class, 'postSale'])->name('pos.sale');
    Route::post('/pos/hold', [PosController::class, 'holdOrder'])->name('pos.hold');
    Route::post('/pos/kitchen', [PosController::class, 'sendToKitchen'])->name('pos.kitchen');
    Route::get('/pos/receipts/{sale}', [PosController::class, 'receipt'])->name('pos.receipt');
    Route::post('/pos/receipts/{sale}/void', [PosController::class, 'voidSale'])->name('pos.void-sale');
    Route::post('/pos/receipts/{sale}/refund', [PosController::class, 'refundSale'])->name('pos.refund-sale');
    Route::post('/pos/receipts/{sale}/return-items', [PosController::class, 'returnItems'])->name('pos.return-items');

    Route::get('/inventory', [ModuleController::class, 'inventory'])->name('inventory');
    Route::get('/tables', [ModuleController::class, 'tables'])->name('tables');
    Route::get('/kds', [ModuleController::class, 'kds'])->name('kds');
    Route::get('/recipes', [ModuleController::class, 'recipes'])->name('recipes');
    Route::get('/purchases', [ModuleController::class, 'purchases'])->name('purchases');
    Route::get('/expenses', [ModuleController::class, 'expenses'])->name('expenses');
    Route::get('/credit', [ModuleController::class, 'credit'])->name('credit');
    Route::get('/reports', [ModuleController::class, 'reports'])->name('reports');
    Route::get('/hotel', [ModuleController::class, 'hotel'])->name('hotel');
    Route::get('/users', [ModuleController::class, 'users'])->name('users');
    Route::get('/settings', [ModuleController::class, 'settings'])->name('settings');

    Route::post('/actions/product', [ActionController::class, 'product'])->name('actions.product');
    Route::post('/actions/recipe', [ActionController::class, 'recipe'])->name('actions.recipe');
    Route::post('/actions/production', [ActionController::class, 'production'])->name('actions.production');
    Route::post('/actions/table', [ActionController::class, 'table'])->name('actions.table');
    Route::post('/actions/kds', [ActionController::class, 'kds'])->name('actions.kds');
    Route::post('/actions/purchase', [ActionController::class, 'purchase'])->name('actions.purchase');
    Route::post('/actions/supplier', [ActionController::class, 'supplier'])->name('actions.supplier');
    Route::post('/actions/wastage', [ActionController::class, 'wastage'])->name('actions.wastage');
    Route::post('/actions/stock-adjustment', [ActionController::class, 'stockAdjustment'])->name('actions.stock-adjustment');
    Route::post('/actions/expense', [ActionController::class, 'expense'])->name('actions.expense');
    Route::post('/actions/customer', [ActionController::class, 'customer'])->name('actions.customer');
    Route::post('/actions/user', [ActionController::class, 'user'])->name('actions.user');
    Route::post('/actions/settings', [ActionController::class, 'settings'])->name('actions.settings');
});
