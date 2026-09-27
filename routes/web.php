<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\StudentProfileController;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Livewire\Admin\CategoryManager as AdminCategoryManager;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\SystemTipTemplateManager;
use App\Livewire\Admin\UserManager as AdminUserManager;
use App\Livewire\Student\BudgetManager;
use App\Livewire\Student\CategoryManager;
use App\Livewire\Student\Dashboard;
use App\Livewire\Student\MonthlyReports;
use App\Livewire\Student\SavingTipsManager;
use App\Livewire\Student\TransactionList;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
})->name('home');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    // Student Authentication
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Separate Direct-Access Administrator Login
    Route::get('/admin/login', [LoginController::class, 'showAdminLoginForm'])->name('admin.login');
    Route::post('/admin/login', [LoginController::class, 'adminLogin'])->name('admin.login.submit');

    Route::get('/forgot-password', [PasswordResetController::class, 'showLinkForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');

    // Student Registration
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Student Protected Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', EnsureUserIsActive::class])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/profile', [StudentProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [StudentProfileController::class, 'update'])->name('profile.update');

    Route::get('/categories', CategoryManager::class)->name('categories');
    Route::get('/transactions', TransactionList::class)->name('transactions');
    Route::get('/budgets', BudgetManager::class)->name('budgets');
    Route::get('/reports', MonthlyReports::class)->name('reports');
    Route::get('/reports/export/pdf', [ReportExportController::class, 'exportPdf'])->name('reports.export.pdf');
    Route::get('/reports/export/csv', [ReportExportController::class, 'exportCsv'])->name('reports.export.csv');
    Route::get('/tips', SavingTipsManager::class)->name('student.tips');
});

/*
|--------------------------------------------------------------------------
| Administrator Protected Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware(['auth', EnsureUserIsActive::class, EnsureUserIsAdmin::class])->group(function () {
    Route::get('/dashboard', AdminDashboard::class)->name('admin.dashboard');
    Route::get('/categories', AdminCategoryManager::class)->name('admin.categories');
    Route::get('/users', AdminUserManager::class)->name('admin.users');
    Route::get('/tip-templates', SystemTipTemplateManager::class)->name('admin.tip-templates');
});
