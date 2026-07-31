<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ConfiguracaoInter extends Model
{
    protected $table = 'configuracoes_inter';
    protected $fillable = ['ambiente', 'client_id', 'client_secret', 'conta_corrente', 'cedente_cnpj', 'cert_path', 'key_path'];
    protected $casts = ['client_secret' => 'encrypted'];

    public static function atual(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }

    public function certFullPath(): ?string
    {
        return $this->cert_path ? Storage::disk('local')->path($this->cert_path) : null;
    }

    public function keyFullPath(): ?string
    {
        return $this->key_path ? Storage::disk('local')->path($this->key_path) : null;
    }

    public function configurado(): bool
    {
        return $this->client_id && $this->client_secret && $this->cert_path && $this->key_path;
    }
}
