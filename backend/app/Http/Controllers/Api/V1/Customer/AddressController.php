<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses = Address::where('user_id', $request->user()->id)
            ->orderByDesc('is_default')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Danh sach dia chi',
            'data'    => $addresses,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label'      => 'nullable|string|max:50',
            'full_name'  => 'required|string|max:255',
            'phone'      => 'required|string|max:20',
            'street'     => 'required|string',
            'ward'       => 'required|string|max:100',
            'district'   => 'required|string|max:100',
            'province'   => 'required|string|max:100',
            'is_default' => 'nullable|boolean',
        ]);

        $data['user_id'] = $request->user()->id;

        if (!empty($data['is_default'])) {
            Address::where('user_id', $request->user()->id)->update(['is_default' => false]);
        }

        // First address is always default
        if (Address::where('user_id', $request->user()->id)->count() === 0) {
            $data['is_default'] = true;
        }

        $address = Address::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Them dia chi thanh cong',
            'data'    => $address,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $address = Address::where('user_id', $request->user()->id)->findOrFail($id);

        $data = $request->validate([
            'label'      => 'nullable|string|max:50',
            'full_name'  => 'sometimes|required|string|max:255',
            'phone'      => 'sometimes|required|string|max:20',
            'street'     => 'sometimes|required|string',
            'ward'       => 'sometimes|required|string|max:100',
            'district'   => 'sometimes|required|string|max:100',
            'province'   => 'sometimes|required|string|max:100',
            'is_default' => 'nullable|boolean',
        ]);

        if (!empty($data['is_default'])) {
            Address::where('user_id', $request->user()->id)->update(['is_default' => false]);
        }

        $address->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Cap nhat dia chi thanh cong',
            'data'    => $address,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $address = Address::where('user_id', $request->user()->id)->findOrFail($id);
        $wasDefault = $address->is_default;
        $address->delete();

        // If we deleted the default address, make another one default
        if ($wasDefault) {
            $next = Address::where('user_id', $request->user()->id)->first();
            $next?->update(['is_default' => true]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Xoa dia chi thanh cong',
            'data'    => null,
        ]);
    }
}
