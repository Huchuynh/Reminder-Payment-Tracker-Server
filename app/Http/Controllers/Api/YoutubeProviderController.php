<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class YoutubeProviderController extends Controller
{
    public function inquiry($customerCode)
    {
        $path = database_path('fake/youtube.json');
        $data = json_decode(file_get_contents($path), true);

        $record = collect($data)->firstWhere('customer_id', $customerCode);
        
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
