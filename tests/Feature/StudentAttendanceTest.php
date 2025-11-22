<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Permission;
use App\Models\Role;

class StudentAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $teacher;
    protected $academicYear;
    protected $classroom;
    protected $student;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup roles and permissions
        $role = Role::create(['name' => 'admin', 'label' => 'Admin']);
        $permissions = [
            'student_attendances.create',
            'student_attendances.view',
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
        $this->student = Student::factory()->create(['name' => 'Test Student']);
    }

    public function test_can_save_class_attendance()
    {
        $response = $this->actingAs($this->teacher)
            ->postJson('/api/v1/student-attendances/save-class', [
                'teacher_id' => $this->teacher->id,
                'class_id' => $this->classroom->id,
                'academic_year_id' => $this->academicYear->id,
                'date' => now()->format('Y-m-d'),
                'students' => [
                    [
                        'student_id' => $this->student->id,
                        'status' => 'hadir',
                        'note' => null
                    ]
                ]
            ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('student_attendances', [
            'teacher_id' => $this->teacher->id,
            'class_id' => $this->classroom->id
        ]);

        $this->assertDatabaseHas('student_attendance_details', [
            'student_id' => $this->student->id,
            'status' => 'hadir'
        ]);
    }

    public function test_can_update_class_attendance()
    {
        // Create initial attendance
        $this->actingAs($this->teacher)
            ->postJson('/api/v1/student-attendances/save-class', [
                'teacher_id' => $this->teacher->id,
                'class_id' => $this->classroom->id,
                'academic_year_id' => $this->academicYear->id,
                'date' => now()->format('Y-m-d'),
                'students' => [
                    [
                        'student_id' => $this->student->id,
                        'status' => 'hadir',
                        'note' => null
                    ]
                ]
            ]);

        // Update attendance (change status to sick)
        $response = $this->actingAs($this->teacher)
            ->postJson('/api/v1/student-attendances/save-class', [
                'teacher_id' => $this->teacher->id,
                'class_id' => $this->classroom->id,
                'academic_year_id' => $this->academicYear->id,
                'date' => now()->format('Y-m-d'),
                'students' => [
                    [
                        'student_id' => $this->student->id,
                        'status' => 'sakit',
                        'note' => 'Flu'
                    ]
                ]
            ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('student_attendance_details', [
            'student_id' => $this->student->id,
            'status' => 'sakit',
            'note' => 'Flu'
        ]);
    }
}
