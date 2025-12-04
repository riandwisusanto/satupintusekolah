<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\ConfigurationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        try {
            $setting = ConfigurationSetting::where('name', $name)->first();

            if (!$setting) {
                return apiResponse('success', null, 200);
            }

            return apiResponse('success', $setting->value, 200);
        } catch (\Exception $e) {
            return apiResponse($e->getMessage(), null, 500);
        }
    }

    public function get(string $name)
    {
        try {
            $setting = ConfigurationSetting::where('name', $name)->first();

            if (!$setting) {
                return apiResponse('success', null, 200);
            }

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

            if ($request->hasFile('value')) {
                if ($setting->value && Storage::disk('public')->exists($setting->value)) {
                    Storage::disk('public')->delete($setting->value);
                }

                $setting->value = $request->file('value')->store('config', 'public');
            } else {
                $setting->value = $request->value;
            }
            $setting->save();

            return apiResponse('Success', $setting->value, 200);
        } catch (\Exception $e) {
            return apiResponse($e->getMessage(), null, 500);
        }
    }
}
