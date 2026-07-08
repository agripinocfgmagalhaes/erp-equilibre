<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FasePadrao extends Model
{
    protected $table = 'fases_padrao';
    protected $fillable = ['nome','macro_categoria','ordem'];
}
