<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\EngagementController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\SlideController;
use App\Http\Controllers\AdminRegistrationFieldController;
use App\Http\Controllers\AdminSurveyFieldController;
use App\Http\Controllers\AdminSettingController;
use App\Http\Controllers\AdminUserController;

Route::view('/', 'home')->name('home');

// Alias default auth "login" route to admin login so auth:admin middleware can redirect correctly.
Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

Route::get('/register', [RegisterController::class, 'showForm'])->name('register.index');
Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

Route::get('/engagement', [EngagementController::class, 'showForm'])->name('engagement.index');
Route::post('/engagement/start', [EngagementController::class, 'start'])->name('engagement.start');
Route::post('/engagement/progress', [EngagementController::class, 'updateProgress'])->name('engagement.progress');

Route::get('/survey', [SurveyController::class, 'showForm'])->name('survey.index');
Route::post('/survey', [SurveyController::class, 'store'])->name('survey.store');

Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');

Route::middleware('auth:admin')->group(function () {
    Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/profile', [AdminController::class, 'profile'])->name('admin.profile');
    Route::put('/admin/profile', [AdminController::class, 'updateProfile'])->name('admin.profile.update');
    Route::delete('/admin/profile', [AdminController::class, 'destroyProfile'])->name('admin.profile.destroy');
    Route::resource('/admin/slides', SlideController::class)->except(['show']);
    Route::resource('/admin/registration-fields', AdminRegistrationFieldController::class)->except(['show']);
    Route::resource('/admin/survey-fields', AdminSurveyFieldController::class)->except(['show']);
    Route::resource('/admin/settings', AdminSettingController::class)->except(['show']);
    Route::post('/admin/settings/test-email', [AdminSettingController::class, 'sendTestEmail'])->name('settings.test-email');
    Route::post('/admin/settings/delivery-check', [AdminSettingController::class, 'deliveryCheck'])->name('settings.delivery-check');
    Route::post('/admin/settings/clear-platform-data', [AdminSettingController::class, 'clearPlatformData'])->name('settings.clear-platform-data');
    Route::resource('/admin/users', AdminUserController::class)->except(['show'])->parameters([
        'users' => 'user',
    ]);

    Route::get('/admin/export/registrations', [AdminController::class, 'exportRegistrations'])->name('admin.export.registrations');
    Route::get('/admin/export/engagements', [AdminController::class, 'exportEngagements'])->name('admin.export.engagements');
    Route::get('/admin/export/surveys', [AdminController::class, 'exportSurveys'])->name('admin.export.surveys');
});
