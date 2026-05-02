<?php
use App\Http\Controllers\EventController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\TodoController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration())
])->name('home');
Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
    Route::apiResource('events', EventController::class);//Events
    Route::apiResource('todos', TodoController::class);//ToDos
    Route::patch('todos/{todo}/complete', [TodoController::class, 'complete'])->name('todos.complete');
    Route::get('family', [FamilyController::class, 'index'])->name('family.index');//Family Management
    Route::post('family/link', [FamilyController::class, 'link'])->name('family.link');
    Route::delete('family/{user}', [FamilyController::class, 'unlink'])->name('family.unlink');
    Route::get('family/{user}/parents', [FamilyController::class, 'parents'])->name('family.parents');
});
require __DIR__.'/settings.php';