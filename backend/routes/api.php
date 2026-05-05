<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\SchoolClassController;
use App\Http\Controllers\Api\OlympiadController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\ResultController;
use App\Http\Controllers\Api\KpiController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\KpiSettingController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\TeacherVoteController;

// Публичные маршруты
Route::post('/login', [AuthController::class, 'login']);
Route::get('/chat/stream', [ChatController::class, 'stream']);

// Защищённые маршруты
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar']);

    // Общий чат
    Route::get('/chat/messages', [ChatController::class, 'index']);
    Route::post('/chat/messages', [ChatController::class, 'store']);

    // Голосования за учителей
    Route::get('/teacher-votes', [TeacherVoteController::class, 'index']);
    Route::post('/teacher-votes', [TeacherVoteController::class, 'vote']);

    // Справочники (доступны всем авторизованным)
    Route::get('/subjects', [SubjectController::class, 'index']);
    Route::get('/classes', [SchoolClassController::class, 'index']);

    // Пользователи — только администратор
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('/users', UserController::class);
        Route::post('/users/import', [UserController::class, 'import']);
        Route::get('/users/export-template', [UserController::class, 'exportTemplate']);
        Route::get('/users/export-credentials', [UserController::class, 'exportCredentials']);
        Route::post('/subjects', [SubjectController::class, 'store']);
        Route::put('/subjects/{subject}', [SubjectController::class, 'update']);
        Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy']);
        Route::post('/classes', [SchoolClassController::class, 'store']);
        Route::put('/classes/{class}', [SchoolClassController::class, 'update']);
        Route::delete('/classes/{class}', [SchoolClassController::class, 'destroy']);
        Route::get('/kpi-settings', [KpiSettingController::class, 'index']);
        Route::put('/kpi-settings', [KpiSettingController::class, 'update']);
        Route::get('/ai-settings', [KpiSettingController::class, 'getAiSettings']);
        Route::put('/ai-settings', [KpiSettingController::class, 'updateAiSettings']);
        Route::post('/ai-settings/test', [KpiSettingController::class, 'testAiKey']);
    });

    // Олимпиады — просмотр для всех, управление по ролям
    Route::get('/olympiads', [OlympiadController::class, 'index']);
    Route::get('/olympiads/{olympiad}', [OlympiadController::class, 'show']);
    Route::middleware('role:admin,director,deputy_events,deputy_edu,deputy_science,teacher')->group(function () {
        Route::post('/olympiads', [OlympiadController::class, 'store']);
        Route::put('/olympiads/{olympiad}', [OlympiadController::class, 'update']);
    });
    Route::middleware('role:admin,director,deputy_events,deputy_edu,deputy_science')->group(function () {
        Route::delete('/olympiads/{olympiad}', [OlympiadController::class, 'destroy']);
        Route::post('/olympiads/search-ai', [OlympiadController::class, 'searchAi']);
    });

    // Регистрации на олимпиады
    Route::middleware('role:admin,director,deputy_events,deputy_edu,deputy_science,teacher,student')->group(function () {
        Route::get('/registrations', [RegistrationController::class, 'index']);
        Route::post('/registrations', [RegistrationController::class, 'store']);
        Route::delete('/registrations/{registration}', [RegistrationController::class, 'destroy']);
        Route::put('/registrations/{registration}/status', [RegistrationController::class, 'updateStatus']);
    });

    // Результаты и верификация
    Route::middleware('role:admin,director,deputy_events,deputy_edu,deputy_science,teacher,student')->group(function () {
        Route::get('/results', [ResultController::class, 'index']);
        Route::get('/results/pending', [ResultController::class, 'pending']);
        Route::post('/results/{registration}', [ResultController::class, 'upload']);
    });
    Route::middleware('role:admin,director,deputy_events,deputy_edu,deputy_science')->group(function () {
        Route::put('/results/{result}/verify', [ResultController::class, 'verify']);
        Route::put('/results/{result}/reject', [ResultController::class, 'reject']);
    });

    // KPI
    Route::middleware('role:admin,director,deputy_events,deputy_edu,deputy_science,teacher,student')->group(function () {
        Route::get('/kpi/my', [KpiController::class, 'my']);
        Route::get('/kpi/user/{user}', [KpiController::class, 'forUser']);
        Route::post('/kpi/appeals', [KpiController::class, 'storeAppeal']);
    });
    Route::middleware('role:admin,director,deputy_events,deputy_edu,deputy_science')->group(function () {
        Route::get('/kpi/appeals', [KpiController::class, 'appeals']);
        Route::put('/kpi/appeals/{appeal}', [KpiController::class, 'resolveAppeal']);
    });

    // Рейтинги
    Route::get('/ratings/teachers', [RatingController::class, 'teachers']);
    Route::get('/ratings/students', [RatingController::class, 'students']);

    // Отчёты
    Route::middleware('role:admin,director,deputy_events,deputy_edu,deputy_science')->group(function () {
        Route::get('/reports/teachers', [ReportController::class, 'teachers']);
        Route::get('/reports/olympiads', [ReportController::class, 'olympiads']);
    });
});
