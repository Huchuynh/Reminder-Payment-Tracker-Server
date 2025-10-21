<?php
namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\GoogleLoginRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Models\Account;
use App\Services\AuthService;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    use ApiResponseTrait;

    protected const JWT_TTL = 999999;
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->email);

        if(isset($result['error'])) {
            return $this->responseError(
                $result['error'],
                Response::HTTP_BAD_REQUEST,
                $result['error']
            );
        }

        return $this->responseSuccess(
            $result,
            'Login successful. Please check your email for the OTP to verify your account.',
            Response::HTTP_OK
         );
    }

    public function loginGoogle(GoogleLoginRequest $request) {
        $result = $this->authService->loginGoogle($request->credentials);

        if(isset($result['error'])) {
            return $this->responseError(
                $result['error'],
                Response::HTTP_UNAUTHORIZED,
                $result['error']
            );
        }

        return $this->responseSuccess(
            $result,
            'Login successful.',
            Response::HTTP_OK
        );
    }

    public function register(RegisterRequest $request)
    {
        $result = $this->authService->register($request->full_name, $request->email);

        if(isset($result['error'])) {
            return $this->responseError($result['error'],
                 RESPONSE::HTTP_BAD_REQUEST,
                $result['error'],
            );
        }

        return $this->responseSuccess(
            $result,
            'Registration successful. Please check your email for the OTP to verify your account.',
            RESPONSE::HTTP_CREATED
         );
    }

    public function verifyOtp(VerifyOtpRequest $request) {
        $result = $this->authService->verifyOtp($request->email, $request->otp);

        if(isset($result['error'])) {
            return $this->responseError(
                $result['error'],
                RESPONSE::HTTP_BAD_REQUEST,
                $result['error'],
            );
        }

        return $this->responseSuccess(
            $result,
            'OTP verified successfully.',
            RESPONSE::HTTP_OK
         );
    }

    public function resendOtp(LoginRequest $request) {
        $result = $this->authService->sendEmailOtp($request->email);
        if(!$result) {
            return $this->responseError(
                'Fail to send OTP',
                RESPONSE::HTTP_BAD_REQUEST
            );
        }

        return $this->responseSuccess(
            $result,
            'OTP resent successfully.',
            RESPONSE::HTTP_OK
        );
    }

    public function logout()
    {
        $result = $this->authService->logout();

        if (isset($result['error'])) {
            return $this->responseError(
                $result['error'],
                Response::HTTP_BAD_REQUEST
            );
        }

        return $this->responseSuccess(
            null,
            $result['message'],
            Response::HTTP_OK
        )->withoutCookie('auth_token');
    }

    public function refresh()
    {
        return $this->responseSuccess(auth()->refresh(), 'Token refreshed successfully.');
    }
}

