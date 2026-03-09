<?php

/**
 * PaymentsExport — Export Excel des Paiements
 *
 * Phase 10 — Section 11.2
 * Utilise maatwebsite/excel
 *
 * @package App\Exports
 */

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function query()
    {
        return Payment::with('student.user', 'student.classe')
            ->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return [
            'N° Transaction',
            'Opérateur',
            'Numéro Téléphone',
            'Élève',
            'Matricule',
            'Classe',
            'Montant (XAF)',
            'Statut',
            'Date',
            'Payé par',
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->transaction_ref ?? '—',
            ucfirst($payment->operator),
            $payment->phone_number,
            $payment->student?->user?->name ?? '—',
            $payment->student?->matricule ?? '—',
            $payment->student?->classe?->name ?? '—',
            number_format($payment->amount, 0, ',', ' '),
            match($payment->status) {
                'success' => 'Succès',
                'pending' => 'En attente',
                'failed'  => 'Échoué',
                default   => $payment->status,
            },
            $payment->created_at?->format('d/m/Y H:i'),
            $payment->payer_name ?? '—',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '0D9488']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }
}
