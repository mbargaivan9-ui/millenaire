<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\User;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $students = User::where('role', 'student')->get();

        if ($students->isEmpty()) {
            $this->command->warn('Veuillez d\'abord exécuter le UserSeeder');
            return;
        }

        $statuses = ['pending', 'completed', 'overdue'];
        $types = ['tuition', 'materials', 'uniform', 'activity'];
        $methods = ['cash', 'mtn_mobile', 'orange_money', 'bank_transfer'];
        $amount = 100000; // CFA

        foreach ($students as $student) {
            // Create 3-6 payments per student
            for ($i = 0; $i < rand(3, 6); $i++) {
                $status = $statuses[array_rand($statuses)];
                
                Payment::create([
                    'student_id' => $student->id,
                    'amount' => $amount,
                    'type' => $types[array_rand($types)],
                    'status' => $status,
                    'payment_method' => $methods[array_rand($methods)],
                    'reference' => 'PAY-' . strtoupper(uniqid()),
                    'description' => 'Paiement scolaire',
                    'due_date' => now()->addMonths(rand(1, 3)),
                    'paid_at' => $status === 'completed' ? now()->subDays(rand(1, 30)) : null
                ]);
            }
        }
    }
}
