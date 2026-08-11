<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orcamento_itens', function (Blueprint $table) {
            if (! Schema::hasColumn('orcamento_itens', 'material_unitario')) {
                $table->decimal('material_unitario', 15, 2)->default(0)->after('valor_unitario');
                $table->decimal('mdo_unitario', 15, 2)->default(0)->after('material_unitario');
                $table->decimal('outros_unitario', 15, 2)->default(0)->after('mdo_unitario');
                $table->decimal('material_total', 15, 2)->default(0)->after('valor_total');
                $table->decimal('mdo_total', 15, 2)->default(0)->after('material_total');
                $table->decimal('outros_total', 15, 2)->default(0)->after('mdo_total');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orcamento_itens', function (Blueprint $table) {
            $table->dropColumn([
                'material_unitario', 'mdo_unitario', 'outros_unitario',
                'material_total', 'mdo_total', 'outros_total',
            ]);
        });
    }
};
