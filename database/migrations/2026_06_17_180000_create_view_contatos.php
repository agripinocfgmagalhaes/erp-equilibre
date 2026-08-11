<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void {
        $isMysql = DB::connection()->getDriverName() === 'mysql';
        $uid = $isMysql ? "CONCAT('cliente-', id)" : "'cliente-' || id";
        DB::statement('DROP VIEW IF EXISTS view_contatos');
        DB::statement("CREATE VIEW view_contatos AS
            SELECT {$uid} AS uid, id AS contato_id, 'cliente' AS contato_tipo, nome, telefone, cpf AS cpf_cnpj FROM clientes WHERE ativo = 1
            UNION ALL SELECT ".(str_replace('cliente-', 'corretor-', $uid)).", id, 'corretor', nome, telefone, cpf_cnpj FROM corretores WHERE ativo = 1
            UNION ALL SELECT ".(str_replace('cliente-', 'fornecedor-', $uid)).", id, 'fornecedor', nome, telefone, cnpj FROM fornecedores WHERE ativo = 1
            UNION ALL SELECT ".(str_replace('cliente-', 'prestador-', $uid)).", id, 'prestador', nome, telefone, cpf_cnpj FROM prestadores WHERE ativo = 1");
    }
    public function down(): void { DB::statement('DROP VIEW IF EXISTS view_contatos'); }
};
