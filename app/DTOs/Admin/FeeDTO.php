<?php

namespace App\DTOs\Admin;

class ScheduleDTO
{
    public function __construct(
        public ?int $id = null,
        public ?int $classe_id = null,
        public ?int $subject_id = null,
        public ?int $teacher_id = null,
        public ?string $day_of_week = null,
        public ?string $start_time = null,
        public ?string $end_time = null,
        public ?string $room = null,
        public ?string $classe_name = null,
        public ?string $subject_name = null,
        public ?string $teacher_name = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            classe_id: $data['classe_id'] ?? null,
            subject_id: $data['subject_id'] ?? null,
            teacher_id: $data['teacher_id'] ?? null,
            day_of_week: $data['day_of_week'] ?? null,
            start_time: $data['start_time'] ?? null,
            end_time: $data['end_time'] ?? null,
            room: $data['room'] ?? null,
            classe_name: $data['classe_name'] ?? null,
            subject_name: $data['subject_name'] ?? null,
            teacher_name: $data['teacher_name'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'classe_id' => $this->classe_id,
            'subject_id' => $this->subject_id,
            'teacher_id' => $this->teacher_id,
            'day_of_week' => $this->day_of_week,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'room' => $this->room,
        ];
    }
}
