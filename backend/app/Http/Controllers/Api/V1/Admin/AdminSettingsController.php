<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->groupBy('group');

        return response()->json([
            'success' => true,
            'data'    => $settings,
            'message' => 'OK',
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings'       => 'required|array',
            'settings.*.key'   => 'required|string',
            'settings.*.value' => 'nullable',
        ]);

        $updated = [];

        foreach ($request->settings as $item) {
            $setting = Setting::where('key', $item['key'])->first();

            if ($setting) {
                $value = is_array($item['value']) ? json_encode($item['value']) : $item['value'];
                $setting->update(['value' => $value]);
                $updated[] = $setting->fresh();
            }
        }

        return response()->json([
            'success' => true,
            'data'    => $updated,
            'message' => 'Settings updated',
        ]);
    }
}
