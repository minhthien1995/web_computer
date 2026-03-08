<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles')->withCount(['orders', 'repairOrders']);

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%");
            });
        }

        $users = $query->latest()->paginate(20)->withQueryString();

        return response()->json([
            'success' => true,
            'data'    => $users,
            'message' => 'OK',
        ]);
    }

    public function show($id)
    {
        $user = User::with(['addresses', 'roles'])->withCount(['orders', 'repairOrders'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $user,
            'message' => 'OK',
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'  => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'role'  => 'sometimes|in:customer,technician,admin',
        ]);

        // Handle role change separately
        if (isset($validated['role'])) {
            $newRole = $validated['role'];
            unset($validated['role']);

            $user->syncRoles([$newRole]);
        }

        if (!empty($validated)) {
            $user->update($validated);
        }

        $user->load('roles');

        return response()->json([
            'success' => true,
            'data'    => $user,
            'message' => 'User updated',
        ]);
    }
}
