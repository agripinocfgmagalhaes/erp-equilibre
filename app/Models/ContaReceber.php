<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ContaReceber extends Model
{
    protected $table = 'contas_receber';
    protected $fillable = ['descricao','contrato_venda_id','cliente_id','plano_conta_id','conta_bancaria_id','projeto_id','valor','valor_recebido','data_vencimento','data_recebimento','status','observacoes'];
    protected $casts = ['valor' => 'decimal:2', 'valor_recebido' => 'decimal:2', 'data_vencimento' => 'date', 'data_recebimento' => 'date'];
    public function contratoVenda(): BelongsTo { return $this->belongsTo(ContratoVenda::class); }
    public function cliente(): BelongsTo { return $this->belongsTo(Cliente::class); }
    public function planoConta(): BelongsTo { return $this->belongsTo(PlanoConta::class); }
    public function contaBancaria(): BelongsTo { return $this->belongsTo(ContaBancaria::class); }
    public function projeto(): BelongsTo { return $this->belongsTo(Projeto::class); }
    public function darBaixa(float $valorRecebido, ?string $dataRecebimento = null, ?int $contaBancariaId = null): void
    {
        $contaBancariaId = $contaBancariaId ?? $this->conta_bancaria_id;
        $dataRecebimento = $dataRecebimento ?? now()->toDateString();
        $this->update(['valor_recebido' => $valorRecebido, 'data_recebimento' => $dataRecebimento, 'conta_bancaria_id' => $contaBancariaId, 'status' => $valorRecebido >= $this->valor ? 'recebido' : 'aberto']);
        if ($contaBancariaId) LancamentoBancario::registrarBaixa('conta_receber', $this->id, $contaBancariaId, 'entrada', $this->descricao, $valorRecebido, $dataRecebimento);
    }
    protected static function booted(): void
    {
        static::saving(function (ContaReceber $conta) {
            if ($conta->status === 'aberto' && $conta->data_vencimento && $conta->data_vencimento->isPast()) $conta->status = 'vencido';
        });
    }
}
