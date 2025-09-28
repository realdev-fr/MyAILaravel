<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\MCPTestController;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');
