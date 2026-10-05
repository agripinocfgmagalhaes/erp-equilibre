<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE contas_receber MODIFY status ENUM('aberto','recebido','vencido','cancelado','parcial') NOT NULL DEFAULT 'aberto'");
    }
    public function down(): void
    {
        DB::statement("UPDATE contas_receber SET status = 'aberto' WHERE status = 'parcial'");
        DB::statement("ALTER TABLE contas_receber MODIFY status ENUM('aberto','recebido','vencido','cancelado') NOT NULL DEFAULT 'aberto'");
    }
};
