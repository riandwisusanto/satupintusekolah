<?php

namespace Database\Seeders;

use App\Models\ConfigurationSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConfigurationSettingSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database with default configuration settings.
     */
    public function run(): void
    {
        $defaultSettings = [
            [
                'name' => 'school_name',
                'value' => 'AdaJago',
            ],
            [
                'name' => 'school_logo',
                'value' => 'logo-jago.png',
            ],
        ];

        foreach ($defaultSettings as $setting) {
            ConfigurationSetting::updateOrCreate(
                ['name' => $setting['name']],
                ['value' => $setting['value']]
            );
        }

        $this->command->info('Default configuration settings created successfully!');
    }
}
