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
        Schema::create('kpi_snapshots', function (Blueprint $table) {
            $table->id();
            $table->integer('total_students');
            $table->integer('total_teachers');
            $table->integer('total_classes');
            $table->decimal('payment_rate', 5, 2)->default(0);
            $table->integer('completed_bulletins');
            $table->integer('total_bulletins');
            $table->integer('active_alerts');
            $table->string('academic_year');
            $table->date('snapshot_date');
            $table->timestamps();

            $table->index(['academic_year', 'snapshot_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_snapshots');
    }
};
