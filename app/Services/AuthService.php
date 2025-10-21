<?php

namespace App\Services;

use App\Models\Account;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Cookie;
use Google_Client;

class AuthService
{
    public function getAccountByEmail(string $email): ?Account
    {
        return Account::where('email', $email)->first() ?? null;
    }

    public function sendEmailOtp(string $email): bool
    {
        try {
            $otp = rand(100000, 999999);
            $expires = 15;
            Cache::put('otp_' . $email, $otp, now()->addMinutes($expires));

//            Mail::to($email)->send(new OtpMail($otp, $expires));
            \Log::info("Sending OTP to " . $email . "with OTP: " . $otp);

            return true;
        } catch (\Throwable $e) {
            Cache::forget('otp_' . $email);
            \Log::error("Fail to send OTP: {$e->getMessage()}");
            return false;
        }
    }

    public function login(string $email) {
        try {
            $account = $this->getAccountByEmail($email);

            if($account) {
                $sent = $this->sendEmailOtp($email);
                if (!$sent) {
                    throw new \Exception('Failed to send OTP email.');
                }
                return ['account' => $account];
            }

            return ['error' => 'Account not found.'];
        } catch (\Throwable $th) {
            \Log::error("Login failed: {$th->getMessage()}");
            return ['error' => 'Login failed. Please try again.'];
        }

    }

    public function loginGoogle(string $credentials) {
        $client = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);

        $payload = $client->verifyIdToken($credentials);

        if (!$payload) {
            return ['error' => 'Invalid token'];
        }

        \Log::info($payload);
        // Tạo hoặc cập nhật user
        $account = Account::updateOrCreate(
            ['email' => $payload['email']],
            [
                'full_name' => $payload['name'],
                'avatar' => $payload['picture'],
                'password' => bcrypt(Str::random(16)),
                'role' => 'user',
            ]
        );

        $token = JWTAuth::fromUser($account);

        return compact('account', 'token');
    }

    public function register(string $full_name, string $email)
    {
        try {
            $account = DB::transaction(function () use ($full_name, $email) {

                $newAccount = Account::create([
                    'full_name' => $full_name,
                    'email' => $email,
                    'password' => bcrypt(Str::random(16)), // Mật khẩu tạm thời
                    'avatar' => env('APP_DEFAULT_AVATAR'),
                ]);

                $sent = $this->sendEmailOtp($newAccount->email);

                if (!$sent) {
                    throw new \Exception('Failed to send OTP email.');
                }

                return $newAccount;
            });

            return ['account' => $account];

        } catch (\Throwable $e) {
            \Log::error("Registration failed: {$e->getMessage()}");
            return ['error' => 'Registration failed. Please try again.'];
        }
    }

    public function verifyOtp(string $email, string $providedOtp)
    {
        try {
            $cacheKey = 'otp_' . $email;

            if (!Cache::has($cacheKey) || (int)Cache::get($cacheKey) !== (int)$providedOtp) {
                return ['error' => 'Failed to verify OTP. Please try again.'];
            }

            $account = $this->getAccountByEmail($email);
            if(!$account) {
                return ['error' => 'Account not found.'];
            }

            $token = JWTAuth::fromUser($account);

            Cache::forget($cacheKey);

            return compact('token', 'account');
        } catch (\Throwable $e) {
            \Log::error("Failed to verify OTP: " . $e->getMessage());
            return ['error' => 'Failed to verify OTP. Please try again.'];
        }
    }

    public function logout()
    {
        try {
            $token = JWTAuth::getToken();

            if ($token) {
                JWTAuth::invalidate($token);
            }

            Cookie::queue(Cookie::forget('auth_token'));

            return ['message' => 'Logged out successfully'];
        } catch (\Throwable $e) {
            \Log::error("Failed to sign out user: " . $e->getMessage());
            return ['error' => 'Failed to sign out user'];
        }
    }
}
