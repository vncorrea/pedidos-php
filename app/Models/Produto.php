<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Produto extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'preco',
        'descricao',
        'ativo'
    ];

    protected $casts = [
        'preco' => 'decimal:2',
        'ativo' => 'boolean'
    ];

    public function variacoes()
    {
        return $this->hasMany(Variacao::class, 'id_produto', 'id_produto');
    }
}
