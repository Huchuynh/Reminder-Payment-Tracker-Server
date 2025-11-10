<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class YoutubeProviderController extends Controller
{
    private $path;
    private $data;

    public function __construct()
    {
        $this->path = database_path('fake/youtube.json');
        $this->data = json_decode(file_get_contents($this->path), true);
    }

    public function inquiry(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $record = collect($this->data)->firstWhere('account.email', $validated['email']);

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

    public function renew(Request $request, string $id)
    {
        \Log::info("renew: " . $id);
        $validated = $request->validate([
            'api_subscription_id' => 'required|string',
            'amount' => 'required|numeric',
            'renewal_date' => 'required|date',
            'callback_url' => 'required|url',
            'redirect_url' => 'required|url',
        ]);


        foreach ($this->data as &$item) {
            if ($item['subscription_id'] === $validated['api_subscription_id']) {
                $item['status'] = 'ACTIVE';
                $item['expiry_time'] = $validated['renewal_date'];
                break;
            }
        }

        file_put_contents($this->path, json_encode($this->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $paymentData = [
            'amount' => $validated['amount'],
            'status' => 'SUCCESS',
            'payment_date' => now(),
        ];

        $client = new Client();

        try {
            $response = $client->post($validated['callback_url'] . "/{$id}", [
                'json' => $paymentData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to call callback API',
                'message' => $e->getMessage(),
            ], 500);
        }

        return redirect($validated['redirect_url']);
    }
}
