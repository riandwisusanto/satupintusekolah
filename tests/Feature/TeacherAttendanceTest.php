<?php

namespace Tests\Feature;

use App\Models\TeacherAttendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Permission;
use App\Models\Role;

class TeacherAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $teacher;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup roles and permissions
        $role = Role::create(['name' => 'admin', 'label' => 'Admin']);
        $permissions = [
            'attendance.teacher.check_in',
            'attendance.teacher.check_out',
            'attendance.teacher.absence',
            'attendance.teacher.view',
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
    }

    public function test_teacher_can_check_in()
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/teacher-attendances/check-in', [
                'teacher_id' => $this->teacher->id,
                'date' => now()->format('Y-m-d'),
                'time_in' => '07:00',
                'status' => 'check_in'
            ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('teacher_attendances', [
            'teacher_id' => $this->teacher->id,
            'status' => 'check_in'
        ]);
    }

    public function test_teacher_can_check_out()
    {
        $attendance = TeacherAttendance::create([
            'teacher_id' => $this->teacher->id,
            'date' => now()->format('Y-m-d'),
            'time_in' => '07:00',
            'status' => 'check_in'
        ]);

        $response = $this->actingAs($this->admin)
            ->postJson("/api/v1/teacher-attendances/{$attendance->id}/check-out", [
                'time_out' => '14:00'
            ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('teacher_attendances', [
            'id' => $attendance->id,
            'time_out' => '14:00',
            'status' => 'check_out'
        ]);
    }

    public function test_admin_can_create_sick_leave()
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/teacher-attendances/sick-leave', [
                'teacher_id' => $this->teacher->id,
                'date' => now()->format('Y-m-d'),
                'status' => 'sick',
                'notes' => 'Flu'
            ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('teacher_attendances', [
            'teacher_id' => $this->teacher->id,
            'status' => 'sick',
            'note' => 'Flu',
            'time_in' => null,
            'time_out' => null
        ]);
    }


}
