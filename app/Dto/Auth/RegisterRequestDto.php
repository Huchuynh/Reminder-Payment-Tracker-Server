<?php

namespace App\Dto\Auth;

class RegisterRequestDto
{
    private string $full_name;
    private string $email;

    /**
     * @param string $full_name
     * @param string $email
     */
    public function __construct(string $full_name, string $email)
    {
        $this->full_name = $full_name;
        $this->email = $email;
    }

    public function getFullName(): string
    {
        return $this->full_name;
    }

    public function setFullName(string $full_name): void
    {
        $this->full_name = $full_name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
}
