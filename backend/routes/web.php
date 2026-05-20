<?php

use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\CartWebController;
use App\Http\Controllers\Web\CheckoutWebController;
use App\Http\Controllers\Web\ContactWebController;
use App\Http\Controllers\Web\OrdersWebController;
use App\Http\Controllers\Web\ProfileWebController;
use App\Http\Controllers\Web\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'home'])->name('web.home');
Route::get('/productos', [StorefrontController::class, 'products'])->name('web.products');
Route::get('/productos/{id}', [StorefrontController::class, 'showProduct'])->whereNumber('id')->name('web.products.show');
Route::post('/contacto', [ContactWebController::class, 'store'])->name('web.contact.submit');
Route::post('/carrito/agregar', [CartWebController::class, 'add'])->name('web.cart.add');
Route::patch('/carrito/{id}', [CartWebController::class, 'update'])->whereNumber('id')->name('web.cart.update');
Route::post('/carrito/vaciar', [CartWebController::class, 'clear'])->name('web.cart.clear');

Route::get('/login', [AuthWebController::class, 'showLogin'])->name('web.login');
Route::post('/login', [AuthWebController::class, 'login'])->name('web.login.submit');
Route::get('/register', [AuthWebController::class, 'showRegister'])->name('web.register');
Route::post('/register', [AuthWebController::class, 'register'])->name('web.register.submit');
Route::post('/logout', [AuthWebController::class, 'logout'])->name('web.logout');

Route::middleware('web.user')->group(function () {
    Route::get('/perfil', [ProfileWebController::class, 'show'])->name('web.profile');
    Route::patch('/perfil', [ProfileWebController::class, 'updateProfile'])->name('web.profile.update');
    Route::patch('/perfil/password', [ProfileWebController::class, 'updatePassword'])->name('web.profile.password');
    Route::get('/checkout', [CheckoutWebController::class, 'show'])->name('web.checkout');
    Route::post('/checkout', [CheckoutWebController::class, 'store'])->name('web.checkout.submit');
    Route::get('/orders', [OrdersWebController::class, 'index'])->name('web.orders');
    Route::get('/historial', [OrdersWebController::class, 'index'])->name('web.history');
    Route::get('/orders/{id}', [OrdersWebController::class, 'show'])->whereNumber('id')->name('web.orders.show');
    Route::post('/orders/{id}/cancel', [OrdersWebController::class, 'cancel'])->whereNumber('id')->name('web.orders.cancel');
});
