<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardWidgetController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// --------------------------------------------------------
// Auth routes
// --------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

// --------------------------------------------------------
// Authenticated routes
// --------------------------------------------------------
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Dashboard Widgets
    Route::get('/dashboard/widgets', [DashboardWidgetController::class, 'selectWidgets'])->name('dashboard.select-widgets');
    Route::post('/dashboard/widgets', [DashboardWidgetController::class, 'saveWidgets'])->name('dashboard.save-widgets');
    Route::get('/dashboard/widgets-overview', [DashboardWidgetController::class, 'widgetsOverview'])->name('dashboard.widgets-overview');

    // Upload
    Route::get('/upload', function () {
        return redirect()->route('dashboard');
    });
    Route::post('/upload', [UploadController::class, 'store'])->name('upload.store');

    // Drilldown pagina's (klikbare stat-cards)
    Route::get('/records/medewerker', [DashboardController::class, 'byEmployee'])->name('records.by-employee');
    Route::get('/records/actie', [DashboardController::class, 'byAction'])->name('records.by-action');
    Route::get('/records/kosten', [DashboardController::class, 'byCost'])->name('records.by-cost');
    Route::get('/records/duur', [DashboardController::class, 'byDuration'])->name('records.by-duration');

    // API endpoints for AJAX
    Route::get('/api/dashboard-data', [DashboardController::class, 'getFilteredData'])->name('api.dashboard-data');

    // Logout
    Route::post('/logout', function () {
        \Illuminate\Support\Facades\Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect('/');
    })->name('logout');

});