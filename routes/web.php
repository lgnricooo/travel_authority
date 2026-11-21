<?php
use App\Http\Controllers\{AuthController, ProfileController, SignatureController, TravelRequestController, AdminUserController, AdminSchoolController, AdminSignatureController, VerifyController};
use App\Http\Middleware\{NoCache, AdminOnly};
use Illuminate\Support\Facades\Route;

// Public welcome page (logged-in users go straight to their requests)
Route::get('/', fn () => auth()->check() ? redirect()->route('requests.index') : view('welcome'))->name('welcome');
Route::redirect('/home', '/requests'); // Laravel's default post-login target

// Public: check a printed copy against the system
Route::get('/verify/{code}', [VerifyController::class, 'show'])->middleware('throttle:30,1')->name('verify');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:6,1');
});

Route::middleware(['auth', NoCache::class])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/requests', [TravelRequestController::class, 'index'])->name('requests.index');
    Route::post('/requests', [TravelRequestController::class, 'store'])->name('requests.store');
    Route::get('/requests/{travelRequest}', [TravelRequestController::class, 'show'])->name('requests.show');
    Route::post('/requests/{travelRequest}/action', [TravelRequestController::class, 'action'])->name('requests.action');
    Route::get('/requests/{travelRequest}/memo', [TravelRequestController::class, 'memo'])->name('requests.memo');
    Route::get('/requests/{travelRequest}/print', [TravelRequestController::class, 'print'])->name('requests.print');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::redirect('/signature', '/profile')->name('signature.edit'); // old link
    Route::post('/signature', [SignatureController::class, 'store'])->name('signature.store');

    Route::middleware(AdminOnly::class)->group(function () {
        Route::get('/admin/signatures', [AdminSignatureController::class, 'index'])->name('admin.signatures');
        Route::post('/admin/signatures/{user}/verify', [AdminSignatureController::class, 'verify'])->name('admin.signatures.verify');
        Route::post('/admin/signatures/{user}/revoke', [AdminSignatureController::class, 'revoke'])->name('admin.signatures.revoke');
        Route::get('/admin/schools', [AdminSchoolController::class, 'index'])->name('admin.schools');
        Route::post('/admin/schools', [AdminSchoolController::class, 'store'])->name('admin.schools.store');
        Route::post('/admin/schools/{school}', [AdminSchoolController::class, 'update'])->name('admin.schools.update');
        Route::post('/admin/schools/{school}/delete', [AdminSchoolController::class, 'destroy'])->name('admin.schools.destroy');
        Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users');
        Route::post('/admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
        Route::post('/admin/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
        Route::post('/admin/users/{user}/delete', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
    });
});
