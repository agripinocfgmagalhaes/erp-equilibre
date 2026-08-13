<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
class AdmissaoLink extends Model
{
    protected $table = 'admissao_links';
    protected $fillable = ['token','projeto_id','ativo','expira_em'];
    protected $casts = ['ativo' => 'boolean', 'expira_em' => 'date'];
    protected static function booted(): void
    {
        static::creating(function (self $link) {
            if (!$link->token) $link->token = Str::random(40);
        });
    }
    public function projeto(): BelongsTo { return $this->belongsTo(Projeto::class); }
    public function valido(): bool
    {
        if (!$this->ativo) return false;
        if ($this->expira_em && $this->expira_em->isPast()) return false;
        return true;
    }
    public function url(): string { return url("/admissao/{$this->token}"); }
}
