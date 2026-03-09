<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Ajoute les colonnes manquantes pour le workflow de validation hiérarchique complet:
     * - term & sequence (pour remplacer trimester si nécessaire)
     * - statuts: draft, submitted, validated, published
     * - verification_token pour QR code de vérification
     * - validated_by, rejection_reason pour le workflow
     * - Appréciations et notes par matière
     */
    public function up(): void
    {
        Schema::table('bulletins', function (Blueprint $table) {
            // Ajouter les colonnes si elles n'existent pas
            if (!Schema::hasColumn('bulletins', 'term')) {
                $table->integer('term')->default(1)->after('trimester')->comment('Période (1, 2, 3)');
            }
            
            if (!Schema::hasColumn('bulletins', 'sequence')) {
                $table->integer('sequence')->default(1)->after('term')->comment('Séquence dans la période');
            }
            
            if (!Schema::hasColumn('bulletins', 'status')) {
                $table->string('status')->default('draft')->change();
            }
            
            if (!Schema::hasColumn('bulletins', 'verification_token')) {
                $table->string('verification_token')->unique()->nullable()->after('status')->comment('Token pour vérifier l\'authenticité du bulletin via /verify/{token}');
            }
            
            if (!Schema::hasColumn('bulletins', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('published_at')->comment('Date de soumission par l\'enseignant');
            }
            
            if (!Schema::hasColumn('bulletins', 'validated_at')) {
                $table->timestamp('validated_at')->nullable()->after('submitted_at')->comment('Date de validation par le censeur/prof principal');
            }
            
            if (!Schema::hasColumn('bulletins', 'validated_by')) {
                $table->foreignId('validated_by')->nullable()->after('validated_at')->constrained('users')->nullOnDelete()->comment('Utilisateur qui a validé');
            }
            
            if (!Schema::hasColumn('bulletins', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('validated_by')->comment('Raison du rejet (si rejeté)');
            }
            
            if (!Schema::hasColumn('bulletins', 'published_by')) {
                $table->foreignId('published_by')->nullable()->after('rejection_reason')->constrained('users')->nullOnDelete()->comment('Admin/Super Admin qui a publié');
            }
            
            if (!Schema::hasColumn('bulletins', 'moyenne')) {
                $table->decimal('moyenne', 5, 2)->nullable()->after('published_by')->comment('Moyenne générale calculée');
            }
            
            if (!Schema::hasColumn('bulletins', 'rang')) {
                $table->integer('rang')->nullable()->after('moyenne')->comment('Rang de l\'élève dans sa classe');
            }
            
            if (!Schema::hasColumn('bulletins', 'appreciation')) {
                $table->string('appreciation')->nullable()->after('rang')->comment('Appréciation globale (Insuffisant, Assez Bien, Bien, Très Bien, Excellent)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bulletins', function (Blueprint $table) {
            $columns = [
                'term',
                'sequence',
                'verification_token',
                'submitted_at',
                'validated_at',
                'validated_by',
                'rejection_reason',
                'published_by',
                'moyenne',
                'rang',
                'appreciation',
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('bulletins', $column)) {
                    $table->dropColumn($column);
                }
            }
            
            if (Schema::hasColumn('bulletins', 'validated_by')) {
                $table->dropForeignKeyIfExists(['validated_by']);
            }
            if (Schema::hasColumn('bulletins', 'published_by')) {
                $table->dropForeignKeyIfExists(['published_by']);
            }
        });
    }
};
