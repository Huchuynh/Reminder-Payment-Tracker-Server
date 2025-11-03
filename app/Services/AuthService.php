<?php

namespace App\Services;

use App\Enums\AccountRole;
use App\Models\Account;
use Google_Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function getAccountByEmail(string $email): Account
    {
        return Account::where('email', $email)->firstOrFail();
    }

    public function sendEmailOtp(string $email)
    {
        $otp = rand(100000, 999999);
        $expires = 15;
        try {
            Cache::put('otp_' . $email, $otp, now()->addMinutes($expires));
//            Mail::to($email)->send(new OtpMail($otp, $expires));
            \Log::info("Sending OTP to " . $email . " with OTP: " . $otp);

            return ["message" => "OTP sent to " . $email . " with OTP: " . $otp];
        } catch (\Exception $e) {
            Cache::forget('otp_' . $email);
            \Log::error("Fail to send OTP: {$e->getMessage()}");
            throw new \Exception("Fail to send OTP: {$e->getMessage()}");
        }
    }

    public function login(string $email)
    {
        try {
            $account = $this->getAccountByEmail($email);
            $this->sendEmailOtp($email);
            return $account;
        } catch (\Throwable $e) {
            \Log::error("Login failed: {$e->getMessage()}");
            throw new \Exception("Login failed: {$e->getMessage()}");
        }
    }

    public function loginGoogle(string $credentials)
    {
        try {
            $client = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);

            $payload = $client->verifyIdToken($credentials);

            if (!$payload) throw new \Exception("Invalid token");

            $account = Account::updateOrCreate(
                ['email' => $payload['email']],
                [
                    'full_name' => $payload['name'],
                    'avatar' => $payload['picture'],
                    'password' => bcrypt(Str::random(16)),
                    'role' => AccountRole::USER,
                ]
            );

            $token = JWTAuth::fromUser($account);

            return compact('account', 'token');
        } catch (\Throwable $e) {
            \Log::error("Login with google failed: {$e->getMessage()}");
            throw new \Exception("Login with google failed: {$e->getMessage()}");
        }

    }

    public function register(array $request)
    {
        try {
            $account = DB::transaction(function () use ($request) {

                $newAccount = Account::create([
                    'full_name' => $request['full_name'],
                    'email' => $request['email'],
                    'password' => bcrypt(Str::random(16)),
                    'avatar' => env('APP_DEFAULT_AVATAR'),
                ]);

                $this->sendEmailOtp($newAccount->email);

                return $newAccount;
            });

            return $account;

        } catch (\Throwable $e) {
            \Log::error("Registration failed: {$e->getMessage()}");
            throw new \Exception("Registration failed: {$e->getMessage()}");
        }
    }

    public function verifyOtp(array $request)
    {
        try {
            $cacheKey = 'otp_' . $request['email'];

            if (!Cache::has($cacheKey) || (int)Cache::get($cacheKey) !== (int)$request['otp']) {
                throw new \Exception("Invalid OTP");
            }

            $account = $this->getAccountByEmail($request['email']);

            $token = JWTAuth::fromUser($account);

            $account->update(['fcm_token' => $request['fcm_token']]);

            Cache::forget($cacheKey);

            return compact('token', 'account');
        } catch (\Throwable $e) {
            \Log::error("Failed to verify OTP: " . $e->getMessage());
            throw new \Exception("Failed to verify OTP: {$e->getMessage()}");
        }
    }

    public function logout(Account $account)
    {
        try {
            $token = JWTAuth::getToken();

            if (!$token) throw new \Exception("Invalid token");

            JWTAuth::invalidate($token);

            $account->update(['fcm_token' => null]);

            Cookie::queue(Cookie::forget('auth_token'));

            return ['message' => 'Logged out successfully'];
        } catch (\Throwable $e) {
            \Log::error("Failed to sign out user: " . $e->getMessage());
            throw new \Exception("Failed to sign out user: {$e->getMessage()}");
        }
    }
}
