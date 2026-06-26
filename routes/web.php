<?php

require __DIR__.'/settings.php';

use App\Livewire\Customers\Create;
use App\Livewire\Customers\Edit;
use App\Livewire\Customers\Index;
use App\Livewire\Dashboard\Index as DashboardIndex;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // dashboard
    Route::get('/dashboard', DashboardIndex::class)->name('dashboard');

    // customer
    Route::get('/customers', Index::class)->name('customers.index');
    Route::get('/customers/create', Create::class)->name('customers.create');
    Route::get('/customers/{customer}/edit', Edit::class)->name('customers.edit');

    // templates
    Route::get('/templates', App\Livewire\EmailTemplates\Index::class)->name('templates.index');
    Route::get('/templates/create', App\Livewire\EmailTemplates\Create::class)->name('templates.create');
    Route::get('/templates/{emailTemplate}/edit', App\Livewire\EmailTemplates\Edit::class)->name('templates.edit');

    // automations
    Route::get('/automations', App\Livewire\Automations\Index::class)->name('automations.index');
    Route::get('/automations/create', App\Livewire\Automations\Create::class)->name('automations.create');
    Route::get('/automations/{automation}/edit', App\Livewire\Automations\Edit::class)->name('automations.edit');

    Route::get('/deliveries', App\Livewire\Deliveries\Index::class)->name('deliveries.index');
});
