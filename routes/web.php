<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('orders.index'));
Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
Route::put('/orders/{order}', [OrderController::class, 'update'])->name('orders.update');
Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
