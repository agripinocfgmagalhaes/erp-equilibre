<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void
    {
        Schema::table('funcionario_presencas', function (Blueprint $table) {
            $table->string('status')->nullable()->after('presente');
        });

        DB::table('funcionario_presencas')->where('presente', true)->update(['status' => 'integral']);
        DB::table('funcionario_presencas')->where('presente', false)->update(['status' => 'falta']);

        Schema::table('funcionario_presencas', function (Blueprint $table) {
            $table->dropColumn('presente');
        });
    }
    public function down(): void
    {
        Schema::table('funcionario_presencas', function (Blueprint $table) {
            $table->boolean('presente')->default(true)->after('data');
        });
        DB::table('funcionario_presencas')->where('status', 'integral')->update(['presente' => true]);
        DB::table('funcionario_presencas')->whereIn('status', ['falta', 'meia_manha', 'meia_tarde'])->update(['presente' => false]);
        Schema::table('funcionario_presencas', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
