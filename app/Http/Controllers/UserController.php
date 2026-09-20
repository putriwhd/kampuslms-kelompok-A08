<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(10);

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'nim_nip' => 'nullable|string|unique:users,nim_nip',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,dosen,mahasiswa',
        ]);

        $user = new User();

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->nim_nip = $validated['nim_nip'] ?? null;
        $user->password = Hash::make($validated['password']);

        // Role ditetapkan secara eksplisit,
        // bukan melalui mass assignment.
        $user->role = $validated['role'];

        $user->save();

        return redirect()
            ->route('users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'nim_nip' => 'nullable|string|unique:users,nim_nip,' . $user->id,
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:admin,dosen,mahasiswa',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->nim_nip = $validated['nim_nip'] ?? null;

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        // Role tetap ditetapkan secara eksplisit.
        $user->role = $validated['role'];

        $user->save();

        return redirect()
            ->route('users.index')
            ->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->taughtCourses()->exists()) {
            return redirect()
                ->route('users.index', ['as' => $request->query('as', 'admin')])
                ->with('error', 'Pengguna tidak dapat dihapus karena masih menjadi Dosen pada satu atau lebih mata kuliah.');
        }

        $user->delete();

        return redirect()
            ->route('users.index', ['as' => $request->query('as', 'admin')])
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}