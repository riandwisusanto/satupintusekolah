<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\v1\ConfigurationSettingController;
use App\Http\Middleware\LogRequestToDB;
use App\Http\Middleware\SetLocaleMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware([SetLocaleMiddleware::class, LogRequestToDB::class])
    ->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::get('configurations', [ConfigurationSettingController::class, 'index']);
        Route::get('configurations/{name}', [ConfigurationSettingController::class, 'get']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
            Route::patch('configurations/{name}', [ConfigurationSettingController::class, 'update']);

            Route::get('permissions/options', [App\Http\Controllers\Api\v1\RoleController::class, 'getPermissions']);
            Route::get('users/teachers', [\App\Http\Controllers\Api\v1\UserController::class, 'getTeachers']);
            Route::apiResource('users', \App\Http\Controllers\Api\v1\UserController::class);
            Route::apiResource('roles', \App\Http\Controllers\Api\v1\RoleController::class);
            Route::get('classrooms/options', [\App\Http\Controllers\Api\v1\ClassroomController::class, 'getOptions']);
            Route::apiResource('classrooms', \App\Http\Controllers\Api\v1\ClassroomController::class);
            Route::apiResource('students', \App\Http\Controllers\Api\v1\StudentController::class);
            Route::get('subjects/options', [\App\Http\Controllers\Api\v1\SubjectController::class, 'getOptions']);
            Route::apiResource('subjects', \App\Http\Controllers\Api\v1\SubjectController::class);
            Route::apiResource('academic-years', \App\Http\Controllers\Api\v1\AcademicYearController::class);
            Route::get('schedules/teacher/today', [\App\Http\Controllers\Api\v1\ScheduleController::class, 'getTeacherTodaySchedules']);
            Route::get('schedules/teacher', [\App\Http\Controllers\Api\v1\ScheduleController::class, 'getTeacherSchedules']);
            Route::apiResource('schedules', \App\Http\Controllers\Api\v1\ScheduleController::class);
            Route::apiResource('teacher-journals', \App\Http\Controllers\Api\v1\TeacherJournalController::class);
        });
    });
