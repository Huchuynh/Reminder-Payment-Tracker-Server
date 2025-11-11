<?php

namespace Database\Factories;

use App\Enums\AlertChannels;
use App\Enums\SubscriptionStatus;
use App\Models\Account;
use App\Models\Service;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = \Carbon\Carbon::createFromTimestamp(rand(strtotime('2025-01-01'), strtotime('2027-01-01')));
        $end = $start->copy()->addDays(rand(15, 60)); // end_date luôn sau start_date

        $now = \Carbon\Carbon::now();

        // Tính trạng thái dựa vào ngày
        if ($now->lt($start)) {
            $status = SubscriptionStatus::PAID->value; // chưa bắt đầu nhưng đã thanh toán
        } elseif ($now->between($start, $end)) {
            $daysLeft = $now->diffInDays($end);
            if ($daysLeft <= 7) {
                $status = SubscriptionStatus::EXPIRING->value; // sắp hết hạn
            } else {
                $status = SubscriptionStatus::ACTIVE->value; // đang hoạt động
            }
        } else {
            $status = SubscriptionStatus::EXPIRED->value; // đã hết hạn
        }

        // Random thêm khả năng bị hủy (CANCELED) với tỉ lệ nhỏ
        if (rand(1, 20) === 1) { // 5% khả năng
            $status = SubscriptionStatus::CANCELED->value;
        }

        return [
            'account_id' => $this->faker->optional()->randomElement(Account::pluck('id')),
            'service_id' => $this->faker->optional()->randomElement(Service::pluck('id')),
            'start_date' => $start,
            'end_date' => $end,
            'status' => $status,
            'plan' => $this->faker->randomElement(['basic', 'standard', 'premium']),
            'price' => $this->faker->randomFloat(0, 10000, 500000),
            'notes' => $this->faker->paragraph(),
            'reminder_channels' => $channels = fake()->randomElements(
                array_map(fn ($c) => $c->value, AlertChannels::cases()),
                rand(0, 2)
            ),
            'alert_thresholds' => count($channels) ? fake()->randomElement([1, 3, 5, 7]) : null,
            'reminder_frequency' => count($channels) ? fake()->randomElement([6, 12, 24, 48]) : null,
            'last_reminded_at' => $this->faker->optional()->dateTimeBetween('-7 days', 'now'),
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ];
    }
}
