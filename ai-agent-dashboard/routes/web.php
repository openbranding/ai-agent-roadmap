<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;

Route::get('/', [ReportController::class, 'dashboard'])->name('dashboard');
Route::get('/dashboard', [ReportController::class, 'dashboard']);

// Reports Page (table of logs)
Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

Route::get('/reports/export/{format}', [ReportController::class, 'export'])->name('reports.export');


