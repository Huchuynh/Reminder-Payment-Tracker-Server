<?php

namespace Tests\Unit\Momo;

use App\Enums\PaymentStatus;
use App\Enums\SubscriptionHistoryAction;
use App\Enums\SubscriptionStatus;
use App\Models\Account;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Subscription;
use App\Services\MomoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;


class MomoServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MomoService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MomoService();
    }

    public function test_create_payment_success()
    {
        $subscription = Subscription::factory()->create([
            'account_id' => Account::factory()->create(),
            'service_id' => Service::factory()->create(),
        ]);
        $data = [
            'subscription_id' => $subscription->id,
            'amount' => 100000,
            'renewal_date' => now()->addMonth()->toDateString(),
        ];

        Http::fake([
            '*' => Http::response(['payUrl' => 'https://momo.fake'], 200)
        ]);

        $response = $this->service->createPayment($data);

        $this->assertArrayHasKey('payUrl', $response);
        $this->assertDatabaseHas('payments', [
            'subscription_id' => $subscription->id,
            'amount' => 100000,
            'method' => 'momo',
        ]);
    }

    public function test_notify_success()
    {
        $subscription = Subscription::factory()->create([
            'account_id' => Account::factory()->create(),
            'service_id' => Service::factory()->create(),
        ]);
        $payment = Payment::factory()->create(['subscription_id' => $subscription->id]);

        $data = [
            'resultCode' => 0,
            'amount' => 200000,
            'extraData' => now()->addMonth()->toDateString(),
        ];

        $updatedPayment = $this->service->notify($data, $subscription->id);

        $this->assertEquals(PaymentStatus::SUCCESS, $updatedPayment->status);
        $this->assertDatabaseHas('subscription_history', [
            'subscription_id' => $subscription->id,
            'action' => SubscriptionHistoryAction::RENEWED,
        ]);
        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'status' => SubscriptionStatus::ACTIVE,
            'end_date' => $data['extraData'],
        ]);
    }

    public function test_notify_throws_exception()
    {
        $this->expectException(\Exception::class);

        $this->service->notify(['resultCode' => 0, 'amount' => 1000, 'extraData' => now()], 'invalid-subscription');
    }

    public function test_verify_signature_success()
    {
        $subscription = Subscription::factory()->create([
            'account_id' => Account::factory()->create(),
            'service_id' => Service::factory()->create(),
        ]);

        $paymentData = [
            'amount' => 100000,
            'extraData' => now()->toDateString(),
            'message' => 'test',
            'orderId' => 'order123',
            'orderInfo' => 'Payment#' . $subscription->id,
            'orderType' => 'payWithATM',
            'partnerCode' => config('services.momo.partner_code'),
            'payType' => 'ATM',
            'requestId' => 'req123',
            'responseTime' => now()->timestamp,
            'resultCode' => 0,
            'transId' => 12345,
        ];

        $rawHash = implode('&', [
            "accessKey=" . config('services.momo.access_key'),
            "amount={$paymentData['amount']}",
            "extraData={$paymentData['extraData']}",
            "message={$paymentData['message']}",
            "orderId={$paymentData['orderId']}",
            "orderInfo={$paymentData['orderInfo']}",
            "orderType={$paymentData['orderType']}",
            "partnerCode={$paymentData['partnerCode']}",
            "payType={$paymentData['payType']}",
            "requestId={$paymentData['requestId']}",
            "responseTime={$paymentData['responseTime']}",
            "resultCode={$paymentData['resultCode']}",
            "transId={$paymentData['transId']}",
        ]);

        $paymentData['signature'] = hash_hmac('sha256', $rawHash, config('services.momo.secret_key'));

        $this->assertTrue($this->service->verifySignature($paymentData));
    }

    public function test_verify_signature_fails()
    {
        $paymentData = [
            'amount' => 1000,
            'extraData' => now()->toDateString(),
            'message' => 'test',
            'orderId' => 'order123',
            'orderInfo' => 'Payment#1',
            'orderType' => 'payWithATM',
            'partnerCode' => config('services.momo.partner_code'),
            'payType' => 'ATM',
            'requestId' => 'req123',
            'responseTime' => now()->timestamp,
            'resultCode' => 0,
            'transId' => 12345,
            'signature' => 'invalidsignature',
        ];

        $this->assertFalse($this->service->verifySignature($paymentData));
    }
}
