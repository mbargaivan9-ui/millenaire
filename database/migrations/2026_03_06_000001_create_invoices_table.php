<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
                $table->string('reference')->unique();
                $table->string('fee_type')->default('Frais scolaires');
                $table->decimal('amount_due', 12, 2);
                $table->decimal('amount_paid', 12, 2)->default(0);
                $table->boolean('is_paid')->default(false);
                $table->date('due_date')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->string('academic_year')->nullable();
                $table->integer('term')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index(['student_id', 'is_paid']);
                $table->index('due_date');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
