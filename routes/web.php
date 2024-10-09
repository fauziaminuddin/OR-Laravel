<?php

use App\Events\TestEvent;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ProfileController;

use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\AssignmentsController;
use App\Http\Controllers\ServiceUserController;
use App\Http\Controllers\AttributeDashboardController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/home', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Asset routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/projects', [AssetController::class, 'index'])->name('assets.index');
    Route::get('/projects/create', [AssetController::class, 'create'])->name('assets.create');
    Route::post('/projects', [AssetController::class, 'store'])->name('assets.store');
    Route::get('/projects/{id}', [AssetController::class, 'show'])->name('assets.show');
    Route::get('/projects/{id}/edit', [AssetController::class, 'edit'])->name('assets.edit');
    Route::put('/projects/{id}', [AssetController::class, 'update'])->name('assets.update');
    Route::delete('/projects/{id}', [AssetController::class, 'deleteAsset'])->name('assets.delete');
    Route::put('/projects/{id}/attributes', [AssetController::class, 'updateAttribute'])->name('assets.updateAttribute');
});

// Service User routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/access-keys', [ServiceUserController::class, 'index'])->name('service-users.index');
    Route::get('/access-keys/create', [ServiceUserController::class, 'create'])->name('service-users.create');
    Route::post('/access-keys', [ServiceUserController::class, 'store'])->name('service-users.store');
    Route::delete('/access-keys/{id}', [ServiceUserController::class, 'delete'])->name('service-users.delete');
});
// dashboard
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboards', [AttributeDashboardController::class, 'index'])->name('dashboards.index');
    Route::post('/dashboards', [AttributeDashboardController::class, 'store'])->name('dashboards.store');
    Route::put('dashboards', [AttributeDashboardController::class, 'update'])->name('dashboards.update');
    Route::delete('dashboards/{id}', [AttributeDashboardController::class, 'destroydash'])->name('dashboards.destroydash');

    Route::get('/dashboards/{id}', [AttributeDashboardController::class, 'show'])->name('dashboards.show');
    Route::post('/dashboards/{id}/widgets', [AttributeDashboardController::class, 'addWidget'])->name('dashboards.widgets.store');
    Route::get('/dashboard/widgets/{widgetId}/datapoints', [AttributeDashboardController::class, 'getDataPoints'])
    ->name('dashboard.widgets.datapoints');
    Route::put('dashboards/{dashboard}/widgets/{widget}', [AttributeDashboardController::class, 'updateWidget'])->name('dashboards.widgets.update');
    Route::delete('/dashboards/{dashboard}/widgets/{widget}', [AttributeDashboardController::class, 'destroy'])->name('widgets.destroy');
    Route::put('/dashboards/{dashboard}/widgets/{widgetId}/range', [AttributeDashboardController::class, 'updateGaugeRange'])
    ->name('widgets.updateRange');

    Route::get('/dashboard/{id}/fetch-data', [AttributeDashboardController::class, 'fetchData'])->name('dashboard.fetchData');

});

// Admin routes
Route::middleware(['auth', 'verified', \App\Http\Middleware\AdminMiddleware::class])->group(function () {   
    // Classroom routes
    Route::get('/classrooms', [ClassroomController::class, 'index'])->name('classrooms.index');
    Route::post('/classrooms', [ClassroomController::class, 'store'])->name('classrooms.store');
    Route::delete('/classrooms/{id}', [ClassroomController::class, 'destroy'])->name('classrooms.destroy');
    Route::put('/classrooms/{id}', [ClassroomController::class, 'update'])->name('classrooms.update');
    Route::get('/classrooms/{id}', [ClassroomController::class, 'show'])->name('classrooms.show');
    //collab
    Route::post('classrooms/{classroom}/collaborators', [ClassroomController::class, 'addCollaborator'])->name('collaborators.store');
    Route::delete('collaborators/{id}', [ClassroomController::class, 'removeCollaborator'])->name('collaborators.destroy');
    Route::get('search-users', [UserController::class, 'search'])->name('users.search');  // For user search functionality

    // Group routes
    Route::post('/classrooms/{classroomId}/groups', [GroupController::class, 'store'])->name('groups.store');
    Route::put('/groups/{groupId}', [GroupController::class, 'update'])->name('groups.update');
    Route::delete('/groups/{groupId}', [GroupController::class, 'destroy'])->name('groups.destroy');


});

// Assignment routes
Route::post('/groups/{groupId}/assignments', [AssignmentsController::class, 'store'])->name('assignments.store');
Route::put('/assignments/{assignment}/update', [AssignmentsController::class, 'update'])->name('assignments.update');
Route::delete('/assignments/{assignment}', [AssignmentsController::class, 'destroy'])->name('assignments.destroy');
Route::get('/assignments/{assignment}', [AssignmentsController::class, 'show'])->name('classrooms.assign');
//reply
Route::post('/assignments/{assignment}/replies', [AssignmentsController::class, 'storeReply'])->name('assignments.replies.store');
Route::put('/replies/{reply}/update', [AssignmentsController::class, 'updateReply'])->name('replies.update');
Route::delete('/replies/{reply}', [AssignmentsController::class, 'destroyReply'])->name('replies.destroy');

Route::get('classrooms/dashboards/{id}', [AssignmentsController::class, 'showDash'])->name('classrooms.dashboard');
Route::get('classrooms/dashboard/{id}/fetch-data', [AssignmentsController::class, 'fetchData'])->name('classrooms.dashboard.fetchData');

// classroom user routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/classrooms-user', [ClassroomController::class, 'collaborations'])->name('classrooms.collaborations');
    Route::get('/classrooms/{id}', [ClassroomController::class, 'show'])->name('classrooms.show');

});

Route::get('/download-example', function () {
    $filePath = public_path('downloads/example.ino'); // Path to your file
    return response()->download($filePath);
})->name('download.example');


require __DIR__.'/auth.php';
