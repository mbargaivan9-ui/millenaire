<?php

namespace App\DTOs\Admin;

class StudentDTO
{
    public function __construct(
        public ?int $id = null,
        public ?int $user_id = null,
        public ?int $classe_id = null,
        public ?string $matricule = null,
        public ?\DateTimeInterface $date_of_birth = null,
        public ?string $gender = null,
        public ?string $location = null,
        public ?string $phone = null,
        public ?string $financial_status = 'pending',
        public ?string $name = null,
        public ?string $email = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            user_id: $data['user_id'] ?? null,
            classe_id: $data['classe_id'] ?? null,
            matricule: $data['matricule'] ?? null,
            date_of_birth: isset($data['date_of_birth']) ? new \DateTime($data['date_of_birth']) : null,
            gender: $data['gender'] ?? null,
            location: $data['location'] ?? null,
            phone: $data['phone'] ?? null,
            financial_status: $data['financial_status'] ?? 'pending',
            name: $data['name'] ?? null,
            email: $data['email'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'classe_id' => $this->classe_id,
            'matricule' => $this->matricule,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'gender' => $this->gender,
            'location' => $this->location,
            'phone' => $this->phone,
            'financial_status' => $this->financial_status,
        ];
    }
}
