<?php

namespace App\Http\Controllers\Admin;

use App\Domain\User\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class AdminUserController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $role = $request->query('role');
        $status = $request->query('status');
        $search = $request->query('search');

        $query = User::query();

        if ($role) {
            $query->where('role', $role);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        $metrics = [
            'total_users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
            'suspended_users' => User::where('status', 'suspended')->count(),
            'banned_users' => User::where('status', 'banned')->count(),
        ];

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'metrics' => $metrics,
            'filters' => [
                'role' => $role,
                'status' => $status,
                'search' => $search,
            ],
        ]);
    }

    public function toggleStatus(int $id, Request $request): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:active,suspended,banned'],
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'status' => $request->input('status'),
        ]);

        return redirect()->back()->with('success', "Status user {$user->name} berhasil diperbarui.");
    }

    public function changeRole(int $id, Request $request): RedirectResponse
    {
        $request->validate([
            'role' => ['required', 'string', 'in:user,publisher,admin'],
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'role' => $request->input('role'),
        ]);

        return redirect()->back()->with('success', "Role user {$user->name} berhasil diubah.");
    }
}
