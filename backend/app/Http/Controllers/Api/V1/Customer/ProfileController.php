<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Thong tin ca nhan',
            'data'    => $request->user()->load('roles'),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name'   => 'sometimes|required|string|max:255',
            'phone'  => 'sometimes|nullable|string|max:20',
            'avatar' => 'sometimes|nullable|url',
        ]);

        $request->user()->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Cap nhat thong tin thanh cong',
            'data'    => $request->user()->fresh()->load('roles'),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => 'required|string',
            'new_password'     => ['required', 'string', 'min:8', 'confirmed', Password::defaults()],
        ]);

        $user = $request->user();

        if (!Hash::check($data['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mat khau hien tai khong dung',
                'data'    => null,
            ], 422);
        }

        $user->update(['password' => Hash::make($data['new_password'])]);

        return response()->json([
            'success' => true,
            'message' => 'Doi mat khau thanh cong',
            'data'    => null,
        ]);
    }
}
