<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $roles = ['admin', 'teacher'];
        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role], ['label' => Str::of($role)->replace('_', ' ')->title()]);
        }

        $superadmin = Role::where('name', 'admin')->first();
        $allPermissions = Permission::pluck('id')->toArray();
        $superadmin->permissions()->sync($allPermissions);

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role_id' => Role::where('name', 'admin')->first()->id
        ]);

        $this->call([
            ConfigurationSettingSeeder::class,
            ClassroomSeeder::class,
            StudentSeeder::class,
            SubjectSeeder::class,
            TeacherSeeder::class,
            UpdateClassroomTeachersSeeder::class,
            ScheduleSeeder::class,
        ]);
    }
}
