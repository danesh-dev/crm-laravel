<?php

require __DIR__.'/settings.php';

use App\Livewire\Customers\Create;
use App\Livewire\Customers\Edit;
use App\Livewire\Customers\Index;
use App\Livewire\Customers\Show;
use App\Livewire\Dashboard\Index as DashboardIndex;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // dashboard
    Route::get('/dashboard', DashboardIndex::class)->name('dashboard');

    // customer
    Route::get('/customers', Index::class)->name('customers.index');
    Route::get('/customers/create', Create::class)->name('customers.create');
    Route::get('/customers/{customer}', Show::class)->name('customers.show');
    Route::get('/customers/{customer}/edit', Edit::class)->name('customers.edit');

    // templates
    Route::get('/templates', App\Livewire\EmailTemplates\Index::class)->name('templates.index');
    Route::get('/templates/create', App\Livewire\EmailTemplates\Create::class)->name('templates.create');
    Route::get('/templates/{emailTemplate}', App\Livewire\EmailTemplates\Show::class)->name('templates.show');
    Route::get('/templates/{emailTemplate}/edit', App\Livewire\EmailTemplates\Edit::class)->name('templates.edit');

    // automations
    Route::get('/automations', App\Livewire\Automations\Index::class)->name('automations.index');
    Route::get('/automations/create', App\Livewire\Automations\Create::class)->name('automations.create');
    Route::get('/automations/{automation}', App\Livewire\Automations\Show::class)->name('automations.show');
    Route::get('/automations/{automation}/edit', App\Livewire\Automations\Edit::class)->name('automations.edit');

    Route::get('/deliveries', App\Livewire\Deliveries\Index::class)->name('deliveries.index');
    Route::get('/deliveries/{delivery}', App\Livewire\Deliveries\Show::class)->name('deliveries.show');

    // tags
    Route::get('/tags', App\Livewire\Tags\Index::class)->name('tags.index');
    Route::get('/tags/create', App\Livewire\Tags\Create::class)->name('tags.create');
    Route::get('/tags/{tag}/edit', App\Livewire\Tags\Edit::class)->name('tags.edit');

    Route::middleware('super-admin')->group(function () {
        Route::get('/imports/customers', App\Livewire\Imports\Customers\Index::class)->name('imports.customers.index');
    });

    // Route::middleware('super-admin')->group(function () {
    Route::get('/users', App\Livewire\Users\Index::class)->name('users.index');
    Route::get('/users/create', App\Livewire\Users\Create::class)->name('users.create');
    Route::get('/users/{user}/edit', App\Livewire\Users\Edit::class)->name('users.edit');
    // });
});
