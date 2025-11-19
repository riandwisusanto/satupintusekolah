<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\ConfigurationSetting;
use Illuminate\Http\Request;

class ConfigurationSettingController extends Controller
{
    public function index()
    {
        try {
            $settings = ConfigurationSetting::all();
            return apiResponse('success', $settings, 200);
        } catch (\Exception $e) {
            return apiResponse($e->getMessage(), null, 500);
        }
    }

    public function show(string $name)
    {
        return ConfigurationSetting::where('name', $name)->first();
    }

    public function get(string $name)
    {
        try {
            $setting = ConfigurationSetting::where('name', $name)->first();

            return apiResponse('success', $setting->value, 200);
        } catch (\Exception $e) {
            return apiResponse($e->getMessage(), null, 500);
        }
    }

    public function update(Request $request, string $name)
    {
        try {
            $setting = ConfigurationSetting::where('name', $name)->first();
            if (!$setting) {
                $setting = new ConfigurationSetting();
                $setting->name = $name;
            }
            $setting->value = $request->value;
            $setting->save();

            return apiResponse('Success', $setting, 200);
        } catch (\Exception $e) {
            return apiResponse($e->getMessage(), null, 500);
        }
    }
}
