<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index()
    {
        $this->authorizeSuperAdmin();

        $users = Admin::orderBy('id')->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $this->authorizeSuperAdmin();

        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $this->authorizeSuperAdmin();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'role' => ['required', Rule::in(['super_admin', 'admin'])],
        ]);

        Admin::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);

        return redirect()->route('users.index')->with('success', 'Admin user created.');
    }

    public function edit(Admin $user)
    {
        $this->authorizeSuperAdmin();

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, Admin $user)
    {
        $this->authorizeSuperAdmin();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:admins,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'role' => ['required', Rule::in(['super_admin', 'admin'])],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        // Prevent downgrading the only super admin account.
        if ($user->isDirty('role') && $user->role !== 'super_admin') {
            $remainingSuperAdmins = Admin::where('role', 'super_admin')
                ->where('id', '!=', $user->id)
                ->count();

            if ($remainingSuperAdmins === 0) {
                return back()->withErrors([
                    'role' => 'At least one super admin must remain in the system.',
                ])->withInput();
            }
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'Admin user updated.');
    }

    public function destroy(Admin $user)
    {
        $this->authorizeSuperAdmin();

        $currentUser = Auth::guard('admin')->user();

        if ($currentUser->id === $user->id) {
            return back()->withErrors([
                'user' => 'You cannot delete your own account from Users. Use Profile if needed.',
            ]);
        }

        if ($user->role === 'super_admin') {
            $remainingSuperAdmins = Admin::where('role', 'super_admin')
                ->where('id', '!=', $user->id)
                ->count();

            if ($remainingSuperAdmins === 0) {
                return back()->withErrors([
                    'user' => 'Cannot delete the only super admin.',
                ]);
            }
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Admin user deleted.');
    }

    protected function authorizeSuperAdmin(): void
    {
        $admin = Auth::guard('admin')->user();

        abort_unless($admin && $admin->role === 'super_admin', 403);
    }
}
