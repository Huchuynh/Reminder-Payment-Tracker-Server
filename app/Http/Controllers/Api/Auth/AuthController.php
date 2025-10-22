<?php
namespace App\Http\Controllers\Api\Auth;

use App\Dto\Auth\RegisterRequestDto;
use App\Dto\Auth\VerifyOtpRequestDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\GoogleLoginRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Services\AuthService;
use App\Traits\ApiResponseTrait;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    use ApiResponseTrait;
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request)
    {
        try {
            $result = $this->authService->login($request->email);
            return $this->responseSuccess(
                $result,
                'Login successful. Please check your email for the OTP to verify your account.',
            );
        } catch (\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }

    public function loginGoogle(GoogleLoginRequest $request) {
        try {
            $result = $this->authService->loginGoogle($request->credentials);
            return $this->responseSuccess(
                $result,
                'Login successful.',
            );
        } catch(\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }

    }

    public function register(RegisterRequest $request)
    {
        try {
            $registerRequestDto = new RegisterRequestDto($request->full_name, $request->email);
            $result = $this->authService->register($registerRequestDto);

            return $this->responseCreateSuccess(
                $result,
                'Registration successful. Please check your email for the OTP to verify your account.',
            );
        } catch(\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }

    public function verifyOtp(VerifyOtpRequest $request) {
        try {
            $verifyOtpRequestDto = new VerifyOtpRequestDto($request->email, $request->otp);

            $result = $this->authService->verifyOtp($verifyOtpRequestDto);

            return $this->responseSuccess(
                $result,
                'OTP verified successfully.',
            );
        } catch(\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }

    public function resendOtp(LoginRequest $request) {
        try {
            $result = $this->authService->sendEmailOtp($request->email);

            return $this->responseSuccess(
                null,
                $result['message'],
            );
        } catch (\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }

    public function logout()
    {
        try {
            $result = $this->authService->logout();

            return $this->responseSuccess(
                null,
                $result['message'],
            )->withoutCookie('auth_token');
        } catch(\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }

    }

    public function refresh()
    {
        try {
            return $this->responseSuccess(auth()->refresh(), 'Token refreshed successfully.');
        } catch (\Throwable $e) {
            return $this->handleExceptionResponse($e);
        }
    }
}

