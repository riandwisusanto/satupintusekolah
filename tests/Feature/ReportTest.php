<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Journal;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentAttendanceDetail;
use App\Models\Subject;
use App\Models\TeacherAttendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Permission;
use App\Models\Role;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $teacher;
    protected $academicYear;
    protected $classroom;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup roles and permissions
        $role = Role::create(['name' => 'admin', 'label' => 'Admin']);
        $permissions = [
            'reports.teacher_attendance.view',
            'reports.teacher_journals.view',
            'reports.student_attendance.view'
        ];
        
        foreach ($permissions as $perm) {
            $permission = Permission::create(['name' => $perm, 'label' => $perm]);
            $role->permissions()->attach($permission->id);
        }

        // Create admin user
        $this->admin = User::factory()->create(['role_id' => $role->id]);

        // Create teacher role and user
        $teacherRole = Role::create(['name' => 'teacher', 'label' => 'Teacher']);
        $this->teacher = User::factory()->create([
            'name' => 'Test Teacher',
            'role_id' => $teacherRole->id
        ]);

        // Create common data
        $this->academicYear = AcademicYear::factory()->create(['name' => '2024/2025', 'active' => true]);
        $this->classroom = Classroom::factory()->create(['name' => 'X IPA 1']);
    }

    /**
     * Test Teacher Attendance Report
     */
    public function test_teacher_attendance_report_index()
    {
        // Create attendance data
        TeacherAttendance::create([
            'teacher_id' => $this->teacher->id,
            'date' => now()->format('Y-m-d'),
            'time_in' => '07:00:00',
            'status' => 'check_in'
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/reports/teacher-attendance');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['attendances' => ['data']]]);
    }

    public function test_teacher_attendance_summary_calculation()
    {
        // Create mixed attendance data
        TeacherAttendance::create([
            'teacher_id' => $this->teacher->id,
            'date' => now()->subDays(1)->format('Y-m-d'),
            'status' => 'check_in',
            'time_in' => '07:00:00'
        ]);
        
        TeacherAttendance::create([
            'teacher_id' => $this->teacher->id,
            'date' => now()->format('Y-m-d'),
            'status' => 'sick',
            'time_in' => null
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/reports/teacher-attendance/summary');

        $response->assertStatus(200)
            ->assertJsonPath('data.summary.total_records', 2)
            ->assertJsonPath('data.summary.present_days', 1)
            ->assertJsonPath('data.summary.sick_days', 1)
            ->assertJsonPath('data.summary.attendance_percentage', 50);
    }

    /**
     * Test Teacher Journal Report
     */
    public function test_teacher_journal_report_index()
    {
        Journal::create([
            'teacher_id' => $this->teacher->id,
            'class_id' => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
            'date' => now()->format('Y-m-d'),
            'theme' => 'Test Theme',
            'activity' => 'Test Activity',
            'active' => true
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/reports/teacher-journals');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['journals' => ['data']]]);
    }

    public function test_teacher_journal_filtering()
    {
        // Create journal for specific class
        Journal::create([
            'teacher_id' => $this->teacher->id,
            'class_id' => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
            'date' => now()->format('Y-m-d'),
            'theme' => 'Class A Journal',
            'active' => true
        ]);

        // Create another class
        $otherClass = Classroom::factory()->create(['name' => 'X IPA 2']);
        Journal::create([
            'teacher_id' => $this->teacher->id,
            'class_id' => $otherClass->id,
            'academic_year_id' => $this->academicYear->id,
            'date' => now()->format('Y-m-d'),
            'theme' => 'Class B Journal',
            'active' => true
        ]);

        // Filter by first class
        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/reports/teacher-journals?class_id=' . $this->classroom->id);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.journals.data')
            ->assertJsonPath('data.journals.data.0.theme', 'Class A Journal');
    }

    /**
     * Test Student Attendance Report
     */
    public function test_student_attendance_report_index()
    {
        $attendance = StudentAttendance::create([
            'date' => now()->format('Y-m-d'),
            'teacher_id' => $this->teacher->id,
            'academic_year_id' => $this->academicYear->id,
            'class_id' => $this->classroom->id,
        ]);

        $student = Student::factory()->create();
        
        StudentAttendanceDetail::create([
            'student_attendance_id' => $attendance->id,
            'student_id' => $student->id,
            'status' => 'hadir'
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/reports/student-attendance');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['attendances' => ['data']]]);
    }

    public function test_student_attendance_summary_division_by_zero_protection()
    {
        // No data created
        
        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/reports/student-attendance/summary');

        $response->assertStatus(200)
            ->assertJsonPath('data.summary.total_records', 0)
            ->assertJsonPath('data.summary.attendance_percentage', 0);
    }

    public function test_student_attendance_summary_calculation()
    {
        $attendance = StudentAttendance::create([
            'date' => now()->format('Y-m-d'),
            'teacher_id' => $this->teacher->id,
            'academic_year_id' => $this->academicYear->id,
            'class_id' => $this->classroom->id,
        ]);

        $student1 = Student::factory()->create();
        $student2 = Student::factory()->create();
        
        StudentAttendanceDetail::create([
            'student_attendance_id' => $attendance->id,
            'student_id' => $student1->id,
            'status' => 'hadir'
        ]);

        StudentAttendanceDetail::create([
            'student_attendance_id' => $attendance->id,
            'student_id' => $student2->id,
            'status' => 'alpa'
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/v1/reports/student-attendance/summary');

        $response->assertStatus(200)
            ->assertJsonPath('data.summary.present_count', 1)
            ->assertJsonPath('data.summary.absent_count', 1)
            ->assertJsonPath('data.summary.attendance_percentage', 50);
    }
}
