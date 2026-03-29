<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── Table principale des paiements Mobile Money ───────────────────────
        Schema::create('mobile_payments', function (Blueprint $table) {
            $table->id();

            // Références
            $table->string('transaction_ref')->unique();          // MC-XXXXXXXXXXXX
            $table->string('operator_ref')->nullable();           // Ref opérateur (Orange/MTN)
            $table->string('operator_txn_id')->nullable();        // ID de transaction final opérateur

            // Opérateur & montant
            $table->enum('operator', ['orange', 'mtn']);
            $table->string('phone', 20);                          // +237XXXXXXXXX
            $table->unsignedBigInteger('amount');                 // en XAF
            $table->unsignedBigInteger('fees')->default(0);       // Frais de service
            $table->unsignedBigInteger('total_amount');           // amount + fees
            $table->string('currency', 3)->default('XAF');

            // Statut
            $table->enum('status', ['pending', 'processing', 'success', 'failed', 'expired', 'cancelled'])
                  ->default('pending');
            $table->string('failure_reason')->nullable();

            // Contexte scolaire
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->foreignId('payer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->string('fee_type')->default('Frais scolaires');
            $table->text('description')->nullable();
            $table->string('tranche')->nullable();                // 1ère tranche, 2ème, etc.

            // Timestamps opérationnels
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at')->nullable();          // +10 min après initiation

            // Sandbox / Production
            $table->boolean('is_sandbox')->default(true);
            $table->json('api_request_log')->nullable();          // Log de l'appel API
            $table->json('api_response_log')->nullable();         // Log de la réponse API
            $table->json('webhook_payload')->nullable();          // Payload webhook reçu

            // Reçu
            $table->string('receipt_number')->nullable()->unique();
            $table->string('receipt_pdf_path')->nullable();

            $table->timestamps();

            // Index pour performance
            $table->index('status');
            $table->index('operator');
            $table->index('payer_id');
            $table->index('student_id');
            $table->index('initiated_at');
        });

        // ─── Table des notifications de paiement admin ─────────────────────────
        Schema::create('payment_admin_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('mobile_payments')->cascadeOnDelete();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_admin_notifications');
        Schema::dropIfExists('mobile_payments');
    }
};
