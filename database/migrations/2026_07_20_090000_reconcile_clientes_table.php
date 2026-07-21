<?php
/**
 * Reconciliação: o banco de produção teve estes campos adicionados manualmente
 * (fora do Git) em algum momento. Esta migration é idempotente — só altera o
 * que ainda não existe, então roda sem erro tanto em produção (já tem tudo)
 * quanto em qualquer outro ambiente (banco novo, a partir da migration original).
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        Schema::table('clientes', function (Blueprint $table) {
            if (! Schema::hasColumn('clientes', 'whatsapp')) {
                $table->string('whatsapp', 20)->nullable()->after('telefone');
            }
            if (! Schema::hasColumn('clientes', 'renda_familiar')) {
                $table->decimal('renda_familiar', 10, 2)->nullable()->after('whatsapp');
            }
            if (! Schema::hasColumn('clientes', 'estado_civil')) {
                $table->enum('estado_civil', ['solteiro', 'casado', 'divorciado', 'viuvo', 'uniao_estavel'])->nullable()->after('renda_familiar');
            }
            if (! Schema::hasColumn('clientes', 'profissao')) {
                $table->string('profissao', 100)->nullable()->after('estado_civil');
            }
            if (! Schema::hasColumn('clientes', 'conjuge_nome')) {
                $table->string('conjuge_nome', 100)->nullable()->after('profissao');
            }
            if (! Schema::hasColumn('clientes', 'conjuge_cpf')) {
                $table->string('conjuge_cpf', 14)->nullable()->after('conjuge_nome');
            }
            if (! Schema::hasColumn('clientes', 'conjuge_renda')) {
                $table->decimal('conjuge_renda', 10, 2)->nullable()->after('conjuge_cpf');
            }
            if (! Schema::hasColumn('clientes', 'logradouro')) {
                $table->string('logradouro', 150)->nullable()->after('conjuge_renda');
            }
            if (! Schema::hasColumn('clientes', 'numero')) {
                $table->string('numero', 20)->nullable()->after('logradouro');
            }
            if (! Schema::hasColumn('clientes', 'complemento')) {
                $table->string('complemento', 100)->nullable()->after('numero');
            }
        });

        // Migra dados de 'endereco' livre para 'logradouro' antes de remover a coluna antiga
        if (Schema::hasColumn('clientes', 'endereco')) {
            DB::table('clientes')->whereNotNull('endereco')->orderBy('id')->chunk(200, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('clientes')->where('id', $row->id)->update(['logradouro' => $row->endereco]);
                }
            });
        }

        Schema::table('clientes', function (Blueprint $table) {
            if (Schema::hasColumn('clientes', 'celular')) {
                $table->dropColumn('celular');
            }
            if (Schema::hasColumn('clientes', 'endereco')) {
                $table->dropColumn('endereco');
            }
        });
    }

    public function down(): void {
        Schema::table('clientes', function (Blueprint $table) {
            if (! Schema::hasColumn('clientes', 'celular')) {
                $table->string('celular', 20)->nullable();
            }
            if (! Schema::hasColumn('clientes', 'endereco')) {
                $table->string('endereco', 200)->nullable();
            }
            $table->dropColumn(['whatsapp', 'renda_familiar', 'estado_civil', 'profissao', 'conjuge_nome', 'conjuge_cpf', 'conjuge_renda', 'logradouro', 'numero', 'complemento']);
        });
    }
};
