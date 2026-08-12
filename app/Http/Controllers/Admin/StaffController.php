<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
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

        return view('admin.staff.index', compact('pageTitle', 'staff', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'staff_id' => ['required', 'string', 'max:20', 'unique:users,staff_id'],
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'role_id' => $this->assignableRoleIdRule(),
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'staff_id' => $data['staff_id'],
            'full_name' => $data['full_name'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'] ?? '',
            'role_id' => $data['role_id'],
            'password_hash' => Hash::make($data['password']),
            'is_active' => true,
        ]);

        ActivityLog::record('staff', 'created', $user->staff_id, $user->full_name, [
            'role' => ['—', Role::find($data['role_id'])?->label ?? '—'],
        ]);

        return back()->with('status', 'Staff account created.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'role_id' => $this->assignableRoleIdRule($user),
            'is_active' => ['nullable', 'boolean'],
        ]);

        $before = $user->getOriginal();

        $user->update([
            'full_name' => $data['full_name'],
            'phone_number' => $data['phone_number'] ?? '',
            'role_id' => $data['role_id'],
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

        if ($changes) {
            ActivityLog::record('staff', 'updated', $user->staff_id, $user->full_name, $changes);
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
