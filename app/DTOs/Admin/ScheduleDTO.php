<?php

namespace App\DTOs\Admin;

class FeeDTO
{
    public function __construct(
        public ?int $id = null,
        public ?string $name = null,
        public ?float $amount = null,
        public ?string $description = null,
        public ?\DateTimeInterface $due_date = null,
        public ?bool $is_mandatory = false,
        public ?string $status = 'active'
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'] ?? null,
            amount: $data['amount'] ? (float)$data['amount'] : null,
            description: $data['description'] ?? null,
            due_date: isset($data['due_date']) ? new \DateTime($data['due_date']) : null,
            is_mandatory: (bool)($data['is_mandatory'] ?? false),
            status: $data['status'] ?? 'active'
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'amount' => $this->amount,
            'description' => $this->description,
            'due_date' => $this->due_date?->format('Y-m-d'),
            'is_mandatory' => $this->is_mandatory,
            'status' => $this->status,
        ];
    }
}
