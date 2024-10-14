<?php

namespace App\DTO;

class ValidateDto
{
    private bool $status = false;
    private array $errors = [];

    public function __construct(bool $status, array $errors)
    {
        $this->status = $status;
        $this->errors = $errors;
    }

    public function isStatus(): bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): void
    {
        $this->status = $status;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function setErrors(array $errors): void
    {
        $this->errors = $errors;
    }

}