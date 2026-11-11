<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index()
    {
        $actor = auth()->user();

        return view('admin.users.index', [
            'users' => User::orderByRaw("case role when 'super_admin' then 0 when 'owner' then 1 else 2 end")->orderBy('name')->get(),
            'actor' => $actor,
        ]);
    }

    public function create()
    {
        return view('admin.users.form', [
            'user' => new User(['role' => User::ROLE_STAFF, 'is_active' => true]),
            'roles' => $this->assignableRoles(),
        ]);
    }

    public function store(Request $request)
    {
        $roles = array_keys($this->assignableRoles());

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'role' => ['required', 'in:'.implode(',', $roles)],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $user = User::create($data + ['is_active' => true]);
        AuditLog::record('user.created', $user, ['role' => $user->role]);

        return redirect()->route('admin.users.index')->with('success', 'Akun '.$user->name.' dibuat.');
    }

    public function edit(User $user)
    {
        $this->authorizeManage($user);

        return view('admin.users.form', [
            'user' => $user,
            'roles' => $this->assignableRoles(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeManage($user);
        $roles = array_keys($this->assignableRoles());

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:120', 'unique:users,email,'.$user->id],
            'role' => ['required', 'in:'.implode(',', $roles)],
            'password' => ['nullable', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $isActive = $request->boolean('is_active');
        $actor = $request->user();

        if ($user->id === $actor->id && (! $isActive || $data['role'] !== $user->role)) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan atau mengubah peran akun Anda sendiri.')->withInput();
        }
        if ($user->isSuperAdmin() && ($data['role'] !== User::ROLE_SUPER_ADMIN || ! $isActive)
            && User::where('role', User::ROLE_SUPER_ADMIN)->where('is_active', true)->where('id', '!=', $user->id)->doesntExist()) {
            return back()->with('error', 'Harus ada minimal satu Super Admin aktif.')->withInput();
        }

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $roleBefore = $user->role;
        $activeBefore = $user->is_active;
        $user->update($data + ['is_active' => $isActive]);

        AuditLog::record('user.updated', $user, array_filter([
            'role' => $roleBefore !== $user->role ? [$roleBefore, $user->role] : null,
            'is_active' => $activeBefore !== $user->is_active ? [$activeBefore, $user->is_active] : null,
            'password_reset' => isset($data['password']) ? true : null,
        ]));

        return redirect()->route('admin.users.index')->with('success', 'Akun '.$user->name.' diperbarui.');
    }

    /** Halaman ganti kata sandi akun sendiri. */
    public function account()
    {
        return view('admin.account');
    }

    public function updateAccount(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $user = $request->user();
        if (! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Kata sandi saat ini salah.']);
        }

        $user->update(['password' => $data['password']]);

        return back()->with('success', 'Kata sandi berhasil diganti.');
    }

    /** Peran yang boleh diberikan: Super Admin semua peran, Pemilik hanya Staf. */
    private function assignableRoles(): array
    {
        return auth()->user()->isSuperAdmin()
            ? User::ROLES
            : [User::ROLE_STAFF => User::ROLES[User::ROLE_STAFF]];
    }

    private function authorizeManage(User $target): void
    {
        $actor = auth()->user();

        if ($actor->isSuperAdmin()) {
            return;
        }

        // Pemilik hanya mengelola akun Staf.
        abort_unless($target->role === User::ROLE_STAFF, 403);
    }
}
