<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\User;
use App\Support\Sso\AdminAccountResolver;
use App\Support\Sso\MicrosoftDirectory;
use App\Support\Sso\SsoException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class StaffController extends Controller
{
    /**
     * Every new account starts with this. With SSO on nobody types it — they
     * sign in with Microsoft — it only matters for the local password login.
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
     * Add Staff's "Find" button: fills the blank staff ID, name and phone
     * boxes from the directory so the admin can check them before saving.
     * Optional — store() does its own lookup either way.
     */
    public function lookup(Request $request, MicrosoftDirectory $directory)
    {
        abort_unless(config('sso.enabled'), 404);

        $request->validate(['email' => $this->directoryEmailRule()]);

        try {
            return response()->json($this->findNewStaff($directory, $request->input('email')));
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->validator->errors()->first()], 422);
        }
    }

    public function store(Request $request, MicrosoftDirectory $directory)
    {
        $shared = [
            'role_id' => $this->assignableRoleIdRule(),
            'lab_types' => ['nullable', 'array'],
            'lab_types.*' => ['in:research,csl,pharma'],
        ];

        if (config('sso.enabled')) {
            // Staff ID, name and phone are optional: whatever the admin types
            // is kept, and anything left blank comes from the directory.
            $data = $request->validate($shared + [
                'email' => $this->directoryEmailRule(),
                'staff_id' => ['nullable', 'string', 'max:20'],
                'full_name' => ['nullable', 'string', 'max:150'],
                'phone_number' => ['nullable', 'string', 'max:30'],
            ]);

            $person = $this->findNewStaff($directory, $data['email'], $data);

            foreach (['staff_id' => 'staff ID', 'full_name' => 'name'] as $field => $label) {
                if ($person[$field] === '') {
                    throw ValidationException::withMessages([$field => "The UniKL directory has no {$label} for this person — enter it by hand."]);
                }
            }
        } else {
            // Local / Docker, where there's no directory to ask.
            $person = $request->validate($shared + [
                'staff_id' => ['required', 'string', 'max:20', 'unique:users,staff_id'],
                'full_name' => ['required', 'string', 'max:150'],
                'email' => ['required', 'email', 'max:150', 'unique:users,email'],
                'phone_number' => ['nullable', 'string', 'max:30'],
            ]);
            $data = $person;
        }

        $user = User::create([
            'staff_id' => $person['staff_id'],
            'oid' => $person['oid'] ?? null,
            'full_name' => $person['full_name'],
            'email' => $person['email'],
            'phone_number' => $person['phone_number'] ?? '',
            'role_id' => $data['role_id'],
            'lab_types' => User::normalizeLabTypes($data['lab_types'] ?? null),
            'password_hash' => Hash::make(self::DEFAULT_PASSWORD),
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
            'lab_types' => ['nullable', 'array'],
            'lab_types.*' => ['in:research,csl,pharma'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $before = $user->getOriginal();

        $user->update([
            'full_name' => $data['full_name'],
            'phone_number' => $data['phone_number'] ?? '',
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
            ActivityLog::record('staff', 'updated', $user->staff_id, $user->full_name, $changes);
        }

        return back()->with('status', 'Staff account updated.');
    }

    private function directoryEmailRule(): array
    {
        return [
            'required', 'email', 'max:150',
            function ($attribute, $value, $fail) {
                if (! AdminAccountResolver::isAllowedEmail($value)) {
                    $fail('Use a UniKL staff email address ('.implode(', ', config('sso.allowed_email_domains')).').');
                }
            },
        ];
    }

    /**
     * The directory's record for this email, with any staff ID, name or phone
     * the admin typed in place of the directory's. Refused if the person
     * already has an account under any of their email, staff ID or Microsoft
     * identity.
     *
     * @throws ValidationException
     */
    private function findNewStaff(MicrosoftDirectory $directory, string $email, array $typed = []): array
    {
        try {
            $person = $directory->findStaff($email);
        } catch (SsoException $e) {
            Log::warning('Staff directory lookup failed: '.$e->getMessage(), $e->context());

            throw ValidationException::withMessages(['email' => $e->userMessage()]);
        }

        foreach (['staff_id', 'full_name', 'phone_number'] as $field) {
            $person[$field] = trim((string) ($typed[$field] ?? '')) ?: $person[$field];
        }
        $person['full_name'] = mb_substr($person['full_name'], 0, 150);
        $person['phone_number'] = mb_substr($person['phone_number'], 0, 30);

        if (mb_strlen($person['staff_id']) > 20) {
            throw ValidationException::withMessages(['staff_id' => "The directory's staff ID for this person ({$person['staff_id']}) is longer than 20 characters — enter it by hand."]);
        }

        $existing = User::query()
            ->where('oid', $person['oid'])
            ->orWhere(DB::raw('LOWER(TRIM(email))'), $person['email'])
            ->orWhere(DB::raw('LOWER(TRIM(email))'), strtolower(trim($email)))
            ->when($person['staff_id'] !== '', fn ($q) => $q->orWhere('staff_id', $person['staff_id']))
            ->first();

        if ($existing) {
            throw ValidationException::withMessages(['email' => "{$existing->full_name} already has an account (Staff ID {$existing->staff_id})."]);
        }

        return $person;
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
