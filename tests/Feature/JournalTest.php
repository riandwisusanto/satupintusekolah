<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Journal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Permission;
use App\Models\Role;

class JournalTest extends TestCase
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
            'journals.view',
            'journals.create',
            'journals.edit',
            'journals.delete',
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

    public function test_can_create_journal()
    {
        $response = $this->actingAs($this->admin)
            ->postJson('/api/v1/journals', [
                'teacher_id' => $this->teacher->id,
                'class_id' => $this->classroom->id,
                'academic_year_id' => $this->academicYear->id,
                'date' => now()->format('Y-m-d'),
                'theme' => 'Test Theme',
                'activity' => 'Test Activity',
                'subject_ids' => [\App\Models\Subject::factory()->create()->id],
                'active' => true
            ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('journals', [
            'theme' => 'Test Theme',
            'activity' => 'Test Activity'
        ]);
    }

    public function test_can_update_journal()
    {
        $journal = Journal::create([
            'teacher_id' => $this->teacher->id,
            'class_id' => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
            'date' => now()->format('Y-m-d'),
            'theme' => 'Old Theme',
            'activity' => 'Old Activity',
            'active' => true
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson("/api/v1/journals/{$journal->id}", [
                'teacher_id' => $this->teacher->id,
                'class_id' => $this->classroom->id,
                'academic_year_id' => $this->academicYear->id,
                'date' => now()->format('Y-m-d'),
                'theme' => 'New Theme',
                'activity' => 'New Activity',
                'subject_ids' => [\App\Models\Subject::factory()->create()->id],
                'active' => true
            ]);

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('journals', [
            'id' => $journal->id,
            'theme' => 'New Theme'
        ]);
    }

    public function test_can_delete_journal()
    {
        $journal = Journal::create([
            'teacher_id' => $this->teacher->id,
            'class_id' => $this->classroom->id,
            'academic_year_id' => $this->academicYear->id,
            'date' => now()->format('Y-m-d'),
            'theme' => 'To Delete',
            'activity' => 'To Delete',
            'active' => true
        ]);

        $response = $this->actingAs($this->admin)
            ->deleteJson("/api/v1/journals/{$journal->id}");

        $response->assertStatus(200);
        
        $this->assertDatabaseMissing('journals', [
            'id' => $journal->id
        ]);
    }
}
