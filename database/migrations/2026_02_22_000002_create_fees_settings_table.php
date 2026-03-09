<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('fees_settings')) {
            Schema::create('fees_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('cascade');
                $table->decimal('amount', 10, 2)->comment('Fee amount');
                $table->string('currency')->default('FCFA');
                $table->string('academic_year')->nullable();
                $table->text('description')->nullable();
                $table->date('payment_deadline')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('installments')->default(1)->comment('Number of installments');
                $table->decimal('late_fine_amount', 10, 2)->default(0);
                $table->boolean('apply_late_fine')->default(false);
                $table->decimal('discount_percentage', 5, 2)->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fees_settings');
    }
};
