<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Funcionario extends Model
{
    protected $table = 'funcionarios';
    protected $fillable = ['nome','cpf','telefone','chave_pix','tipo_chave_pix','funcao','data_entrada','data_saida','status','foto_documento_path','projeto_id','comentarios'];
    protected $casts = ['data_entrada' => 'date', 'data_saida' => 'date'];
    public function projeto(): BelongsTo { return $this->belongsTo(Projeto::class); }
    public function diarias(): HasMany { return $this->hasMany(FuncionarioDiaria::class); }
    public function diariaAtual(): ?FuncionarioDiaria
    {
        return $this->diarias()->whereNull('vigente_ate')->latest('vigente_desde')->first()
            ?? $this->diarias()->latest('vigente_desde')->first();
    }
    public function scopePendentes($query) { return $query->where('status', 'pendente'); }
    public function scopeAtivos($query) { return $query->where('status', 'ativo'); }
}
