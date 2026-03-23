<?php
// ═══════════════════════════════════════════════════════════
// database/seeders/GradeScaleSeeder.php
// Barème par défaut à exécuter après migration
// Commande : php artisan db:seed --class=GradeScaleSeeder
// ═══════════════════════════════════════════════════════════

namespace Database\Seeders;

use App\Models\GradeScale;
use Illuminate\Database\Seeder;

class GradeScaleSeeder extends Seeder
{
    public function run(): void
    {
        GradeScale::truncate();

        $scales = [
            ['min_value' =>  0,     'max_value' =>  9.99,  'label' => 'Insuffisant',  'color_hex' => '#EF4444', 'sort_order' => 0],
            ['min_value' => 10,     'max_value' => 12.99,  'label' => 'Assez Bien',   'color_hex' => '#F97316', 'sort_order' => 1],
            ['min_value' => 13,     'max_value' => 15.99,  'label' => 'Bien',          'color_hex' => '#22C55E', 'sort_order' => 2],
            ['min_value' => 16,     'max_value' => 18.99,  'label' => 'Très Bien',     'color_hex' => '#3B82F6', 'sort_order' => 3],
            ['min_value' => 19,     'max_value' => 20.00,  'label' => 'Excellent',     'color_hex' => '#8B5CF6', 'sort_order' => 4],
        ];

        foreach ($scales as $scale) {
            GradeScale::create(array_merge($scale, ['is_active' => true]));
        }

        $this->command->info('✅ Barème d\'appréciation créé (5 niveaux).');
    }
}
