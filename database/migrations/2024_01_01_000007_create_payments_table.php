<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 12, 0)->unsigned();
            $table->string('type')->default('tuition'); // tuition, exam, activity, other
            $table->string('status')->default('pending'); // pending, completed, overdue, cancelled
            $table->string('payment_method')->nullable(); // cash, bank_transfer, mobile_money, check
            $table->string('reference')->nullable();
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('student_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
