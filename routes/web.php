<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GateController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/petugas-login', [GateController::class, 'showLogin'])->name('gate.login');
Route::post('/petugas-login', [GateController::class, 'login']);
Route::post('/petugas-logout', [GateController::class, 'logout'])->name('gate.logout');

Route::get('/gate-monitor', [GateController::class, 'index'])->name('gate.monitor');
Route::post('/gate-scan', [GateController::class, 'scan'])->name('gate.scan');