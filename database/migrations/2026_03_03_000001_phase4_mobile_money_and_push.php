<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration Phase 4
 * - payments (Mobile Money CamPay)
 * - payment_receipts (Reçu + QR Code)
 * - push_subscriptions (Web Push notifications)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Table payments ──
        if (! Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();

                // Qui paie
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();

                // Montant & type
                $table->decimal('amount', 12, 2)->comment('Montant en XAF');
                $table->string('payment_type')->default('frais_scolarite')
                    ->comment('frais_scolarite|frais_inscription|cantine|transport|autre');
                $table->text('description')->nullable();

                // Mobile Money
                $table->string('phone', 20)->comment('Numéro de téléphone du payeur');
                $table->string('reference', 50)->unique()->comment('Référence interne MIL-XXXXXX');
                $table->string('campay_reference', 100)->nullable()->unique()->comment('Référence CamPay');
                $table->string('ussd_code', 100)->nullable()->comment('Code USSD retourné par CamPay');
                $table->enum('operator', ['mtn', 'orange', 'unknown'])->default('unknown');

                // Statut
                $table->enum('status', ['pending', 'completed', 'failed', 'cancelled'])->default('pending');
                $table->text('failure_reason')->nullable();
                $table->timestamp('completed_at')->nullable();

                $table->timestamps();

                $table->index(['user_id', 'status'], 'idx_payment_user_status');
                $table->index(['student_id', 'status'], 'idx_payment_student_status');
                $table->index(['status', 'created_at'], 'idx_payment_status_date');
                $table->index('campay_reference', 'idx_campay_ref');
            });
        }

        // ── Table payment_receipts ──
        if (! Schema::hasTable('payment_receipts')) {
            Schema::create('payment_receipts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payment_id')->unique()->constrained('payments')->cascadeOnDelete();
                $table->string('receipt_number', 30)->unique();
                $table->string('verification_token', 64)->unique()->comment('Token pour le QR Code');
                $table->string('verify_url')->nullable();
                $table->text('qr_code_svg')->nullable()->comment('SVG du QR code');
                $table->timestamp('generated_at')->nullable();
                $table->timestamps();

                $table->index('verification_token', 'idx_receipt_token');
            });
        }

        // ── Table push_subscriptions ──
        if (! Schema::hasTable('push_subscriptions')) {
            Schema::create('push_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->text('endpoint')->comment('URL du service push du navigateur');
                $table->text('p256dh')->comment('Clé publique ECDH');
                $table->text('auth')->comment('Secret d\'authentification');
                $table->string('user_agent')->nullable();
                $table->timestamps();

                $table->index('user_id', 'idx_push_user');
                // Pas d'index unique sur endpoint car trop long pour MySQL
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
        Schema::dropIfExists('payment_receipts');
        Schema::dropIfExists('payments');
    }
};
