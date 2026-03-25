<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        // Load all settings into an associative array for easy access in Blade
        $settings = Setting::all()->pluck('value', 'key');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        // Handle regular text/select settings
        if ($request->has('settings')) {
            foreach ($request->settings as $key => $value) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
                // Invalidate cache independently for each setting
                Cache::forget('setting:'.$key);
            }
        }

        // Handle specific Sandbox Toggle
        $sandboxMode = $request->has('sandbox_mode_enabled') ? '1' : '0';
        Setting::updateOrCreate(['key' => 'sandbox_mode'], ['value' => $sandboxMode]);
        Cache::forget('setting:sandbox_mode');

        // Handle File Upload for Logo
        if ($request->hasFile('site_logo')) {
            $request->validate([
                'site_logo' => 'image|mimes:jpeg,png,jpg,svg|max:2048'
            ]);
            
            $path = $request->file('site_logo')->store('settings', 'public');
            
            $oldLogo = Setting::where('key', 'site_logo')->value('value');
            if ($oldLogo && str_starts_with($oldLogo, '/storage/settings/')) {
                $oldPath = str_replace('/storage/', '', $oldLogo);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            Setting::updateOrCreate(
                ['key' => 'site_logo'],
                ['value' => '/storage/' . $path]
            );
            Cache::forget('setting:site_logo');
        }

        return back()->with('success', 'Les paramètres du site ont été enregistrés avec succès.');
    }
}
