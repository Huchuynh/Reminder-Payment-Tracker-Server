<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function handleCallbackFromYoutube(array $data, string $subscription_id)
    {
        try {
            return DB::transaction(function () use ($data, $subscription_id) {
                return Payment::create([
                    'subscription_id' => $subscription_id,
                    'amount' => $data['amount'],
                    'status' => 'success',
                    'paid_at' => $data['payment_date'],
                ]);
            });
        } catch (\Throwable $e) {
            \Log::error("Failed to create service: " . $e->getMessage());
            throw new \Exception("Failed to create service. " . $e->getMessage());
        }
    }
}

