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
            Route::get('schedules/teacher/today', [\App\Http\Controllers\Api\v1\ScheduleController::class, 'getTeacherTodaySchedules']);
            Route::get('schedules/teacher', [\App\Http\Controllers\Api\v1\ScheduleController::class, 'getTeacherSchedules']);
            Route::apiResource('schedules', \App\Http\Controllers\Api\v1\ScheduleController::class);

            // Teacher Journals (Updated to use Journal model)
            Route::apiResource('teacher-journals', \App\Http\Controllers\Api\v1\TeacherJournalController::class);
            Route::get('teacher-journals/teacher/{teacherId}', [\App\Http\Controllers\Api\v1\TeacherJournalController::class, 'getByTeacher']);
            Route::get('teacher-journals/class/{classId}', [\App\Http\Controllers\Api\v1\TeacherJournalController::class, 'getByClass']);
            Route::get('teacher-journals/date/{date}', [\App\Http\Controllers\Api\v1\TeacherJournalController::class, 'getByDate']);

            // New API routes for updated models
            Route::apiResource('academic-years', \App\Http\Controllers\Api\v1\AcademicYearController::class);
            Route::get('academic-years/active', [\App\Http\Controllers\Api\v1\AcademicYearController::class, 'getActive']);
            Route::post('academic-years/{id}/set-active', [\App\Http\Controllers\Api\v1\AcademicYearController::class, 'setActive']);

            Route::apiResource('student-class-histories', \App\Http\Controllers\Api\v1\StudentClassHistoryController::class);
            Route::get('student-class-histories/student/{studentId}', [\App\Http\Controllers\Api\v1\StudentClassHistoryController::class, 'getByStudent']);
            Route::get('student-class-histories/class/{classId}', [\App\Http\Controllers\Api\v1\StudentClassHistoryController::class, 'getByClass']);
            Route::get('student-class-histories/academic-year/{academicYearId}', [\App\Http\Controllers\Api\v1\StudentClassHistoryController::class, 'getByAcademicYear']);
            Route::get('student-class-histories/current/{studentId}', [\App\Http\Controllers\Api\v1\StudentClassHistoryController::class, 'getCurrentClass']);

            Route::apiResource('teacher-attendances', \App\Http\Controllers\Api\v1\TeacherAttendanceController::class);
            Route::get('teacher-attendances/teacher/{teacherId}', [\App\Http\Controllers\Api\v1\TeacherAttendanceController::class, 'getByTeacher']);
            Route::get('teacher-attendances/date/{date}', [\App\Http\Controllers\Api\v1\TeacherAttendanceController::class, 'getByDate']);
            Route::get('teacher-attendances/today', [\App\Http\Controllers\Api\v1\TeacherAttendanceController::class, 'getTodayAttendance']);
            Route::post('teacher-attendances/check-in', [\App\Http\Controllers\Api\v1\TeacherAttendanceController::class, 'checkIn']);
            Route::post('teacher-attendances/{id}/check-out', [\App\Http\Controllers\Api\v1\TeacherAttendanceController::class, 'checkOut']);
        });
    });
