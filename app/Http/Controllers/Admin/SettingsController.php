<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\Maintenance;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $pageTitle = 'System Settings';

        $settings = [
            'maintenance_mode' => Maintenance::isActive(),
            'maintenance_allow_internal' => Maintenance::allowsInternal(),
            'maintenance_title' => Maintenance::title(),
            'maintenance_message' => Maintenance::message(),
        ];

        return view('admin.settings.index', compact('pageTitle', 'settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'maintenance_title' => ['required', 'string', 'max:150'],
            'maintenance_message' => ['required', 'string', 'max:2000'],
        ]);

        Setting::set('maintenance_mode', $request->boolean('maintenance_mode') ? '1' : '0');
        Setting::set('maintenance_allow_internal', $request->boolean('maintenance_allow_internal') ? '1' : '0');
        Setting::set('maintenance_title', $data['maintenance_title']);
        Setting::set('maintenance_message', $data['maintenance_message']);

        return back()->with('status', 'Settings updated.');
    }
}
