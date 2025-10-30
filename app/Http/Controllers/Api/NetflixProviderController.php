<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class NetflixProviderController extends Controller
{
    public function inquiry($customerCode)
    {
        $path = database_path('fake/netflix.json');
        $data = json_decode(file_get_contents($path), true);

        $record = collect($data)->firstWhere('customer_code', $customerCode);

        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $record
        ]);
    }
}
