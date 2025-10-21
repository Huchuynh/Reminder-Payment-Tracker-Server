<?php

namespace App\Dto\Auth;

class VerifyOtpRequestDto
{
    private string $email;
    private string $otp;

    public function __construct($email, $otp)
    {
        $this->email = $email;
        $this->otp = $otp;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getOtp(): string
    {
        return $this->otp;
    }

    public function setOtp(string $otp): void
    {
        $this->otp = $otp;
    }
}
