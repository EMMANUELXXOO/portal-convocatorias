<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $users = User::when($q !== '', function ($qq) use ($q) {
                $qq->where('name', 'like', "%{$q}%")
                   ->orWhere('email','like', "%{$q}%");
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'q'));
    }

    public function create()
    {
        $user = new User(); // $user->exists === false
        // Reusa la misma vista que edit
        return view('admin.users.edit', compact('user'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:8'],
            'role'     => ['required','in:admin,subadmin,aspirante'],
        ]);

        $data['password'] = \Hash::make($data['password']);
        $user = User::create($data);

        audit_log('user.created', $user, ['role' => $user->role]);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Usuario creado.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email','max:255','unique:users,email,'.$user->id],
            'role'     => ['required','in:admin,subadmin,aspirante'],
            'password' => ['nullable','string','min:8'],
        ]);

        $before = $user->only(['name','email','role']);

        if (!empty($data['password'])) {
            $data['password'] = \Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        audit_log('user.updated', $user, [
            'before' => $before,
            'after'  => $user->only(['name','email','role']),
        ]);

        return back()->with('status', 'Usuario actualizado.');
    }

    public function destroy(User $user)
    {
        $id    = $user->id;
        $email = $user->email;

        $user->delete();

        audit_log('user.deleted', 'users', ['id' => $id, 'email' => $email]);

        return back()->with('status', 'Usuario eliminado.');
    }
}
