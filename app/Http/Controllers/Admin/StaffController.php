<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\User;
use App\Support\Sso\AdminAccountResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    /**
     * Pre-filled on Add Staff. It's for the email + password login used
     * locally; with SSO on, staff sign in with Microsoft instead.
     */
    public const DEFAULT_PASSWORD = 'Rcmp@1234';

    public function index()
    {
        $pageTitle = 'Manage Lab Staff';

        // Super admin accounts are granted directly in the database (see
        // assignableRoleIdRule below) and stay invisible to everyone except
        // other super admins — lab staff shouldn't even know they exist.
        $staff = User::query()
            ->with('role')
            ->when(! auth()->user()->isSuperAdmin(), fn ($q) => $q->whereDoesntHave('role', fn ($r) => $r->where('name', 'super_admin')))
            ->orderBy('full_name')
            ->get();
        $roles = Role::all();

        $defaultPassword = self::DEFAULT_PASSWORD;

        return view('admin.staff.index', compact('pageTitle', 'staff', 'roles', 'defaultPassword'));
    }

    /**
     * Only the email, role, lab types and password are asked for. The staff
     * ID starts as a placeholder and the name blank; the first Microsoft
     * sign-in fills both in (ProfileLinker), the same way it links the oid.
     */
    public function store(Request $request)
    {
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:150', 'unique:users,email', function ($attribute, $value, $fail) {
                // With SSO on, an address outside UniKL could never sign in.
                if (config('sso.enabled') && ! AdminAccountResolver::isAllowedEmail($value)) {
                    $fail('Use a UniKL staff email address ('.implode(', ', config('sso.allowed_email_domains')).').');
                }
            }],
            'role_id' => $this->assignableRoleIdRule(),
            'lab_types' => ['nullable', 'array'],
            'lab_types.*' => ['in:research,csl,pharma'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $user = User::create([
            'staff_id' => User::newPendingStaffId(),
            'full_name' => '',
            'email' => $data['email'],
            'phone_number' => '',
            'role_id' => $data['role_id'],
            'lab_types' => User::normalizeLabTypes($data['lab_types'] ?? null),
            'password_hash' => Hash::make($data['password'] ?? self::DEFAULT_PASSWORD),
            'is_active' => true,
        ]);

        ActivityLog::record('staff', 'created', $user->staff_id, $user->displayName(), [
            'role' => ['—', Role::find($data['role_id'])?->label ?? '—'],
        ]);

        return back()->with('status', 'Staff account created.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            // Blank is allowed: a new account's name arrives with its first
            // Microsoft sign-in.
            'full_name' => ['nullable', 'string', 'max:150'],
            'role_id' => $this->assignableRoleIdRule($user),
            'lab_types' => ['nullable', 'array'],
            'lab_types.*' => ['in:research,csl,pharma'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $before = $user->getOriginal();

        $user->update([
            'full_name' => trim((string) ($data['full_name'] ?? '')),
            'role_id' => $data['role_id'],
            'lab_types' => User::normalizeLabTypes($data['lab_types'] ?? null),
            'is_active' => $request->boolean('is_active'),
        ]);

        $changes = ActivityLog::diff($before, $user->getChanges());

        // role_id on its own says nothing in a log — show the labels.
        if (isset($changes['role_id'])) {
            $labels = Role::pluck('label', 'id');
            $changes['role'] = [
                $labels[$before['role_id']] ?? $changes['role_id'][0],
                $labels[$user->role_id] ?? $changes['role_id'][1],
            ];
            unset($changes['role_id']);
        }

        // Same for the lab type list, which is stored as raw JSON.
        if (isset($changes['lab_types'])) {
            $changes['lab_types'] = [
                User::labelForLabTypes($before['lab_types'] ?? null) ?: '—',
                $user->labTypesLabel() ?: '—',
            ];
        }

        if ($changes) {
            ActivityLog::record('staff', 'updated', $user->staff_id, $user->displayName(), $changes);
        }

        return back()->with('status', 'Staff account updated.');
    }

    /**
     * The super_admin role is granted directly in the database only — it's
     * excluded from the Role dropdown and rejected here in case of a
     * tampered request. Editing an existing super admin's other fields is
     * still allowed as long as their role isn't being changed.
     */
    private function assignableRoleIdRule(?User $editingUser = null): array
    {
        return [
            'required',
            'exists:roles,id',
            function ($attribute, $value, $fail) use ($editingUser) {
                $alreadySuperAdmin = $editingUser?->role?->name === 'super_admin';
                if (! $alreadySuperAdmin && Role::find($value)?->name === 'super_admin') {
                    $fail('This role cannot be assigned here.');
                }
            },
        ];
    }
}
