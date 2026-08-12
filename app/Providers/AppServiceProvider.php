<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\Lab;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('admin.*', function ($view) {
            // Every admin.* page pays for this (the sidebar needs it on every
            // request), but the active lab/equipment list only actually changes
            // when an admin edits Manage Labs — cache it instead of re-querying
            // labs + lab_equipment on every single admin page view. Invalidated
            // explicitly by Admin\LabController on create/update/destroy.
            $view->with('adminLabsByType', Cache::remember('admin_labs_by_type', 600, function () {
                return Lab::with(['equipment' => fn ($q) => $q->orderBy('sort_order')])
                    ->where('status', 'active')->orderBy('name')->get()->groupBy('lab_type');
            }));
        });

        View::composer('layouts.admin', function ($view) {
            // One grouped query instead of three identical-shape COUNT()s.
            $pendingByType = Booking::where('status', 'pending')
                ->selectRaw('lab_type, count(*) as total')
                ->groupBy('lab_type')
                ->pluck('total', 'lab_type');

            $view->with('adminPendingByType', [
                'research' => (int) ($pendingByType['research'] ?? 0),
                'csl' => (int) ($pendingByType['csl'] ?? 0),
                'pharma' => (int) ($pendingByType['pharma'] ?? 0),
            ]);

            $view->with('adminRecentPending', Booking::where('status', 'pending')
                ->latest('submitted_at')
                ->take(8)
                ->get(['ref', 'applicant_name', 'lab_type', 'submitted_at']));
        });
    }
}
