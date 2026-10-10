<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureAdmin($request);

        $search = $request->query('search');
        $search = is_string($search) ? trim($search) : '';

        $users = User::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('nim_nip', 'like', "%{$search}%");
                });
            })
            ->when(
                in_array($request->query('role'), ['admin', 'dosen', 'mahasiswa'], true),
                fn ($query) => $query->where('role', $request->query('role'))
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $role = $request->user()->role;

        return view('users.index', compact('users', 'role'));
    }

    public function create(Request $request)
    {
        $this->ensureAdmin($request);
        $role = $request->user()->role;

        return view('users.create', compact('role'));
    }

    public function store(Request $request)
    {
        $this->ensureAdmin($request);

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
        $user->role = $validated['role'];

        $user->save();

        return redirect()
            ->route('admin.users.index', ['as' => 'admin'])
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function show(Request $request, User $user)
    {
        $this->ensureAdmin($request);
        $role = $request->user()->role;

        return view('users.show', compact('user', 'role'));
    }

    public function edit(Request $request, User $user)
    {
        $this->ensureAdmin($request);
        $role = $request->user()->role;

        return view('users.edit', compact('user', 'role'));
    }

    public function update(Request $request, User $user)
    {
        $this->ensureAdmin($request);

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

        $user->role = $validated['role'];

        $user->save();

        return redirect()
            ->route('admin.users.index', ['as' => 'admin'])
            ->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        $this->ensureAdmin($request);

        if ($user->taughtCourses()->exists()) {
            return redirect()
                ->route('admin.users.index', ['as' => 'admin'])
                ->with('error', 'Pengguna tidak dapat dihapus karena masih menjadi Dosen pada satu atau lebih mata kuliah.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index', ['as' => 'admin'])
            ->with('success', 'Pengguna berhasil dihapus.');
    }

    private function ensureAdmin(Request $request): void
    {
        abort_unless($request->user()?->role === 'admin', 403);
    }
}