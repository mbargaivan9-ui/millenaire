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
        Schema::table('bulletins', function (Blueprint $table) {
            // OCR Zone Detection System columns
            $table->longText('ocr_zones')->nullable()->after('moyenne')
                ->comment('JSON array of detected OCR zones with coordinates and text');
            
            $table->longText('raw_text')->nullable()->after('ocr_zones')
                ->comment('Raw text extracted via OCR before processing');
            
            $table->timestamp('processed_at')->nullable()->after('raw_text')
                ->comment('Timestamp when OCR processing was completed');
            
            // Add index for faster queries
            $table->index('processed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bulletins', function (Blueprint $table) {
            $table->dropIndex(['processed_at']);
            $table->dropColumn(['ocr_zones', 'raw_text', 'processed_at']);
        });
    }
};
