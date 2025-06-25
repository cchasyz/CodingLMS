<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SetsController;
use App\Http\Middleware\UsersMiddleware;
use App\Http\Middleware\AdminsMiddleware;
use App\Http\Controllers\CoursesController;
use App\Http\Controllers\LessonsController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function(){
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/courses', [CoursesController::class, 'index']);
    Route::get('/courses/{slug}', [CoursesController::class, 'detail']);
});


Route::middleware(['auth:sanctum', UsersMiddleware::class])->group(function(){
    Route::post('/lessons/{lessonId}/contents/{contentId}/check', [LessonsController::class, 'check']);
    Route::put('/lessons/{lessonId}/complete', [LessonsController::class, 'complete']);
    Route::post('/courses/{slug}/register', [LessonsController::class, 'registerCourse']);
    Route::get('/users/progress', [LessonsController::class, 'getProgress']);
});

Route::middleware(['auth:sanctum', AdminsMiddleware::class])->group(function(){
    Route::post('/courses', [CoursesController::class, 'create']);
    Route::put('/courses/{slug}', [CoursesController::class, 'edit']);
    Route::delete('/courses/{slug}', [CoursesController::class, 'destroy']);

    Route::post('/courses/{slug}/sets', [SetsController::class, 'create']);
    Route::delete('/courses/{slug}/sets/{setId}', [SetsController::class, 'destroy']);

    Route::post('/lessons', [LessonsController::class, 'create']);
    Route::delete('/lessons/{lessonId}', [LessonsController::class, 'destroy']);
});