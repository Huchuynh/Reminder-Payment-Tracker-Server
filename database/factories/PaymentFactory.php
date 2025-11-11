<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $statuses = ['pending', 'success', 'failed'];

        // random thời gian đã thanh toán trong 6 tháng gần đây
        $paidAt = $this->faker->optional()->dateTimeBetween('-6 months', 'now');

        return [
            'subscription_id' => Subscription::factory(),
            'amount' => $this->faker->randomFloat(0, 10000, 500000),
            'method' => 'momo',
            'status' => $this->faker->randomElement($statuses),
            'transaction_ref' => strtoupper('TXN-'.$this->faker->unique()->bothify('??######')),
            'paid_at' => $paidAt,
        ];
    }
}
