<?php

/**
 * StudentsExport — Export Excel des Élèves
 *
 * @package App\Exports
 */

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class StudentsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function query()
    {
        return Student::with('user', 'classe', 'guardian.user')->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'Matricule', 'Nom complet', 'Email', 'Classe', 'Section',
            'Date de naissance', 'Sexe', 'Tuteur', 'Tél. Tuteur',
            'Dernier paiement', 'Statut paiement', 'Date inscription',
        ];
    }

    public function map($student): array
    {
        $lastPayment = $student->payments()->latest()->first();
        return [
            $student->matricule,
            $student->user->name ?? '—',
            $student->user->email ?? '—',
            $student->classe?->name ?? '—',
            $student->classe?->section ?? '—',
            $student->date_of_birth?->format('d/m/Y') ?? '—',
            $student->gender ?? '—',
            $student->guardian?->user?->name ?? '—',
            $student->guardian?->user?->phone ?? '—',
            $lastPayment?->created_at?->format('d/m/Y') ?? '—',
            $lastPayment?->status ?? 'Aucun',
            $student->created_at?->format('d/m/Y') ?? '—',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0D9488']],
            ],
        ];
    }
}
