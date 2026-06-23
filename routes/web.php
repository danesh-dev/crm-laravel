<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';

use App\Livewire\Customers\Index;
use App\Livewire\Customers\Create;
use App\Livewire\Customers\Edit;
use App\Livewire\Dashboard\Index as DashboardIndex;

Route::get('/dashboard', DashboardIndex::class)->name('dashboard');

Route::get('/customers', Index::class)->name('customers.index');
Route::get('/customers/create', Create::class)->name('customers.create');
Route::get('/customers/{customer}/create', Edit::class)->name('customers.edit');
