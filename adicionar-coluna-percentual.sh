#!/bin/bash
set -e
cd ~/domains/lavarel.equilibreconstrucoes.com.br/erp

cat > database/migrations/$(date +%Y_%m_%d_%H%M%S)_add_percentual_to_fases_obra_table.php << 'PHPEOF'
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('fases_obra', function (Blueprint $table) {
            if (! Schema::hasColumn('fases_obra', 'percentual')) {
                $table->decimal('percentual', 5, 2)->default(0)->after('ordem');
            }
        });
    }
    public function down(): void {
        Schema::table('fases_obra', function (Blueprint $table) {
            if (Schema::hasColumn('fases_obra', 'percentual')) {
                $table->dropColumn('percentual');
            }
        });
    }
};
PHPEOF

php artisan migrate --force
echo "Feito."
