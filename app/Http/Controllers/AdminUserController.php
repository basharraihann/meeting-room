<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $filterRole = $request->query('role');

        $users = User::with(['roles', 'room'])
            ->when($q, fn($query) => $query->where('name', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%"))
            ->when($filterRole, fn($query) => $query->whereHas('roles', fn($r) => $r->where('name', $filterRole)))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $rooms = Room::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'rooms', 'roles', 'q', 'filterRole'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'exists:roles,name'],
            'room_id' => ['nullable', 'exists:rooms,id'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'room_id' => $data['room_id'] ?? null,
        ]);

        $user->assignRole($data['role']);

        return back()->with('status', "User {$user->name} berhasil ditambahkan.");
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate([
            'role' => ['required', 'exists:roles,name'],
        ]);

        $user->syncRoles([$request->role]);

        // Kalau bukan TU, hapus room assignment
        if ($request->role !== 'TU') {
            $user->update(['room_id' => null]);
        }

        return back()->with('status', "Role {$user->name} diubah ke {$request->role}.");
    }

    public function updateRoom(Request $request, User $user)
    {
        $request->validate([
            'room_id' => ['nullable', 'exists:rooms,id'],
        ]);

        $user->update(['room_id' => $request->room_id ?: null]);

        $roomName = $request->room_id
            ? Room::find($request->room_id)?->name
            : 'tidak ada';

        return back()->with('status', "Room {$user->name} diubah ke {$roomName}.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['msg' => 'Tidak bisa hapus akun sendiri.']);
        }

        $name = $user->name;
        $user->delete();

        return back()->with('status', "User {$name} berhasil dihapus.");
    }
}