<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NetflixProviderController extends Controller
{
    public function inquiry(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $path = database_path('fake/netflix.json');
        $data = json_decode(file_get_contents($path), true);

        $record = collect($data)->firstWhere('account.email', $validated['email']);

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

    public function renew(Request $request)
    {
        $validated = $request->validate([
            'subscription_id' => 'required|string',
        ]);


    }

    public function handlePayment(Request $request)
    {
        $validated = $request->validate([
            'subscription_id' => 'required|string',
            'amount' => 'required|numeric',
            
        ]);


    }
}
