<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', 'pages::dashboard')->name('dashboard');
    Route::livewire('milieus/{milieu}', 'pages::milieu-overview')->name('milieus.show');
    Route::livewire('milieus/{milieu}/explore/{recordType}/{record?}', 'pages::explorer')->name('milieus.explore');
    Route::livewire('milieus/{milieu}/activity', 'pages::activity')->name('milieus.activity');
});

require __DIR__.'/settings.php';
