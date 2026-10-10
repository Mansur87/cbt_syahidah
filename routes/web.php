<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/results', [ResultController::class, 'index'])->name('results.index');
    Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/exams', [ExamController::class, 'index'])->name('exams.index');
    Route::get('/exams/create', [ExamController::class, 'create'])->name('exams.create');
    Route::post('/exams', [ExamController::class, 'store'])->name('exams.store');
    Route::get('/exams/{exam}/edit', [ExamController::class, 'edit'])->name('exams.edit');
    Route::put('/exams/{exam}', [ExamController::class, 'update'])->name('exams.update');
    Route::delete('/exams/{exam}', [ExamController::class, 'destroy'])->name('exams.destroy');
    Route::post('/exams/{exam}/start', [ExamController::class, 'start'])->name('exams.start');
    Route::get('/attempts/{attempt}', [ExamController::class, 'take'])->name('attempts.show');
    Route::put('/attempts/{attempt}/answers/{question}', [ExamController::class, 'saveAnswer'])->name('attempts.answers.update');
    Route::post('/attempts/{attempt}/submit', [ExamController::class, 'submit'])->name('attempts.submit');
    Route::get('/attempts/{attempt}/result', [ExamController::class, 'result'])->name('attempts.result');
    Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
    Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
    Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
