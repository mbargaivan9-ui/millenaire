<?php

namespace App\DTOs\Admin;

class AttendanceDTO
{
    public function __construct(
        public ?int $id = null,
        public ?int $student_id = null,
        public ?\DateTimeInterface $date = null,
        public ?string $status = 'present',
        public ?string $notes = null,
        public ?string $justified_by = null,
        public ?string $student_name = null,
        public ?string $matricule = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            student_id: $data['student_id'] ?? null,
            date: isset($data['date']) ? new \DateTime($data['date']) : null,
            status: $data['status'] ?? 'present',
            notes: $data['notes'] ?? null,
            justified_by: $data['justified_by'] ?? null,
            student_name: $data['student_name'] ?? null,
            matricule: $data['matricule'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'date' => $this->date?->format('Y-m-d'),
            'status' => $this->status,
            'notes' => $this->notes,
            'justified_by' => $this->justified_by,
        ];
    }
}
