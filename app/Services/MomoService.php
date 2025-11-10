<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Support\Facades\Http;

class MomoService
{
    protected string $endpoint;
    protected string $partnerCode;
    protected string $accessKey;
    protected string $secretKey;
    protected string $redirectUrl;
    protected string $ipnUrl;

    public function __construct()
    {
        $this->endpoint = config('services.momo.endpoint');
        $this->partnerCode = config('services.momo.partner_code');
        $this->accessKey = config('services.momo.access_key');
        $this->secretKey = config('services.momo.secret_key');
        $this->redirectUrl = config('services.momo.redirect_url');
        $this->ipnUrl = config('services.momo.notify_url');
    }

    public function createPayment(array $data)
    {
        Payment::create([
            "subscription_id" => $data['subscription_id'],
            "amount" => $data['amount'],
            "method" => "momo",
        ]);

        $requestId = time() . "";
        $orderIdUnique = $data['subscription_id'] . "_" . time();
        $amount = (int)$data['amount'];
        $requestType = "payWithATM";
        $orderInfo = "Payment#{$data['subscription_id']}";

        $rawHash = implode('&', [
            "accessKey={$this->accessKey}",
            "amount={$amount}",
            "extraData={$data['renewal_date']}",
            "ipnUrl={$this->ipnUrl}/{$data['subscription_id']}",
            "orderId={$orderIdUnique}",
            "orderInfo={$orderInfo}",
            "partnerCode={$this->partnerCode}",
            "redirectUrl={$this->redirectUrl}",
            "requestId={$requestId}",
            "requestType={$requestType}"
        ]);

        $signature = hash_hmac("sha256", $rawHash, $this->secretKey);

        $data = [
            'partnerCode' => $this->partnerCode,
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderIdUnique,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $this->redirectUrl,
            'ipnUrl' => $this->ipnUrl . "/" . $data['subscription_id'],
            'lang' => 'vi',
            'orderExpireTime' => 15,
            'extraData' => $data['renewal_date'],
            'requestType' => $requestType,
            'signature' => $signature,
            'autoCapture' => true,
        ];

        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->post($this->endpoint, $data);

        return $response->json();
    }

    public function notify(array $data, $subscriptionId)
    {
        try {
            $payment = Payment::where('subscription_id', $subscriptionId)->first();

            $payment->update([
                'status' => $data['resultCode'] == 0 ? PaymentStatus::SUCCESS : PaymentStatus::FAILED,
                'amount' => $data['amount'],
                'paid_at' => now()
            ]);

            $subscription = Subscription::findOrFail($subscriptionId);
            if ($data['extraData'])
                $subscription->update([
                    'status' => SubscriptionStatus::ACTIVE,
                    'end_date' => $data['extraData']
                ]);
            else
                $subscription->update([
                    'status' => SubscriptionStatus::PAID,
                ]);
            return $payment;
        } catch (\Throwable $e) {
            \Log::error("Failed to handle callback from momo: " . $e->getMessage());
            throw new \Exception("Failed to handle callback from momo. " . $e->getMessage());
        }
    }

    public function verifySignature(array $data): bool
    {
        $rawHash = implode('&', [
            "accessKey={$this->accessKey}",
            "amount={$data['amount']}",
            "extraData={$data['extraData']}",
            "message={$data['message']}",
            "orderId={$data['orderId']}",
            "orderInfo={$data['orderInfo']}",
            "orderType={$data['orderType']}",
            "partnerCode={$data['partnerCode']}",
            "payType={$data['payType']}",
            "requestId={$data['requestId']}",
            "responseTime={$data['responseTime']}",
            "resultCode={$data['resultCode']}",
            "transId={$data['transId']}",
        ]);

        $expectedSignature = hash_hmac("sha256", $rawHash, $this->secretKey);

        return $expectedSignature === $data['signature'];
    }
}
