<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\User;

/**
 * Booking maintenance mode: when active, the public booking form is closed
 * to guests. Backed by the `settings` key/value table (see Setting) so the
 * Super Admin Settings page can flip it live, with no deploy required.
 */
class Maintenance
{
    public static function isActive(): bool
    {
        return Setting::get('maintenance_mode', '0') === '1';
    }

    public static function allowsInternal(): bool
    {
        return Setting::get('maintenance_allow_internal', '1') === '1';
    }

    public static function title(): string
    {
        return Setting::get('maintenance_title') ?: 'Booking Temporarily Unavailable';
    }

    public static function message(): string
    {
        return Setting::get('maintenance_message')
            ?: 'The booking system is currently undergoing maintenance. Please check back later, or contact the lab office directly for urgent requests.';
    }

    /**
     * This app has no separate login for normal bookers — every authenticated
     * session belongs to a lab_staff/super_admin account, so "is logged in"
     * already means "is internal" for the purpose of this check.
     */
    public static function blocksBooking(?User $user): bool
    {
        if (! static::isActive()) {
            return false;
        }

        if ($user && static::allowsInternal()) {
            return false;
        }

        return true;
    }
}
