<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class Sequencia extends Model
{
    protected $table = 'sequencias';

    protected $fillable = ['tipo', 'ano', 'valor'];

    protected $casts = ['ano' => 'integer', 'valor' => 'integer'];

    /**
     * Retorna o próximo número sequencial de forma atômica para o tipo/ano informado.
     */
    public static function proximo(string $tipo, int $ano): int
    {
        return DB::transaction(function () use ($tipo, $ano) {
            $seq = static::query()
                ->where('tipo', $tipo)
                ->where('ano', $ano)
                ->lockForUpdate()
                ->first();

            if ($seq === null) {
                try {
                    $seq = static::create(['tipo' => $tipo, 'ano' => $ano, 'valor' => 0]);
                } catch (QueryException) {
                    // Registro criado concorrentemente por outra transação — relê sob lock.
                    $seq = static::query()
                        ->where('tipo', $tipo)
                        ->where('ano', $ano)
                        ->lockForUpdate()
                        ->firstOrFail();
                }
            }

            $seq->increment('valor');

            return (int) $seq->valor;
        });
    }
}
