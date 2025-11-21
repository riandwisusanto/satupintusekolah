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
            Route::get('users/teachers', [App\Http\Controllers\Api\v1\UserController::class, 'getTeachers']);
            Route::apiResource('users', \App\Http\Controllers\Api\v1\UserController::class);
            Route::apiResource('roles', \App\Http\Controllers\Api\v1\RoleController::class);
            Route::get('classrooms/options', [\App\Http\Controllers\Api\v1\ClassroomController::class, 'getOptions']);
            Route::apiResource('classrooms', \App\Http\Controllers\Api\v1\ClassroomController::class);
            Route::apiResource('students', \App\Http\Controllers\Api\v1\StudentController::class);
            Route::get('subjects/options', [\App\Http\Controllers\Api\v1\SubjectController::class, 'getOptions']);
            Route::apiResource('subjects', \App\Http\Controllers\Api\v1\SubjectController::class);
            Route::apiResource('academic-years', \App\Http\Controllers\Api\v1\AcademicYearController::class);
            Route::get('schedules/today', [\App\Http\Controllers\Api\v1\ScheduleController::class, 'today']);
            Route::apiResource('schedules', \App\Http\Controllers\Api\v1\ScheduleController::class);
            // Teacher Dashboard Custom Routes
            Route::get('journals/teacher-dashboard', [\App\Http\Controllers\Api\v1\JournalController::class, 'teacherDashboard']);
            Route::get('journals/today-subjects', [\App\Http\Controllers\Api\v1\JournalController::class, 'todaySubjects']);
            Route::apiResource('journals', \App\Http\Controllers\Api\v1\JournalController::class);

            // Student Attendance Custom Routes
            Route::get('student-attendances/teacher-data', [\App\Http\Controllers\Api\v1\StudentAttendanceController::class, 'getTeacherAttendanceData']);
            Route::post('student-attendances/save-class', [\App\Http\Controllers\Api\v1\StudentAttendanceController::class, 'saveClassAttendance']);
            Route::get('student-attendances/today-classes', [\App\Http\Controllers\Api\v1\StudentAttendanceController::class, 'getTodayClasses']);
            Route::apiResource('student-attendances', \App\Http\Controllers\Api\v1\StudentAttendanceController::class);
            // Teacher Attendance Custom Routes (Must be BEFORE resource route)
            Route::post('teacher-attendances/check-in', [\App\Http\Controllers\Api\v1\TeacherAttendanceController::class, 'checkIn']);
            Route::post('teacher-attendances/{id}/check-out', [\App\Http\Controllers\Api\v1\TeacherAttendanceController::class, 'checkOut']);
            Route::post('teacher-attendances/sick-leave', [\App\Http\Controllers\Api\v1\TeacherAttendanceController::class, 'createSickOrLeave']);
            Route::get('teacher-attendances/today/{teacher_id?}', [\App\Http\Controllers\Api\v1\TeacherAttendanceController::class, 'getTodayAttendance']);
            Route::get('teacher-attendances/history/{teacher_id?}', [\App\Http\Controllers\Api\v1\TeacherAttendanceController::class, 'getAttendanceHistory']);
            Route::get('teacher-attendances/monthly-report', [\App\Http\Controllers\Api\v1\TeacherAttendanceController::class, 'getMonthlyReport']);
            Route::get('teacher-attendances/teacher/{teacher_id}/date/{date}', [\App\Http\Controllers\Api\v1\TeacherAttendanceController::class, 'getByTeacherAndDate']);

            // Teacher Attendance Resource Route (Must be AFTER custom routes)
            Route::apiResource('teacher-attendances', \App\Http\Controllers\Api\v1\TeacherAttendanceController::class);
        });
    });
