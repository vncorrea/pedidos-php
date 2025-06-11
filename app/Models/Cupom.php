<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cupom extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo',
        'valor_desconto',
        'valor_minimo',
        'data_inicio',
        'data_fim',
        'ativo'
    ];

    protected $casts = [
        'valor_desconto' => 'decimal:2',
        'valor_minimo' => 'decimal:2',
        'data_inicio' => 'date',
        'data_fim' => 'date',
        'ativo' => 'boolean'
    ];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }

    public function isValid()
    {
        return $this->ativo && 
               now()->between($this->data_inicio, $this->data_fim);
    }
}
