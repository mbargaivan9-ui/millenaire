<?php
declare(strict_types=1);

namespace App\DTOs;

final class CreateStudentDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public string $date_of_birth,
        public string $gender,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'],
            $data['email'],
            $data['password'],
            $data['date_of_birth'],
            $data['gender'],
        );
    }
}
