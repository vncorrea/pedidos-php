<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Variacao extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_produto',
        'nome',
        'preco_adicional',
        'ativo'
    ];

    protected $casts = [
        'preco_adicional' => 'decimal:2',
        'ativo' => 'boolean'
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'id_produto', 'id_produto');
    }

    public function estoque()
    {
        return $this->hasOne(Estoque::class, 'id_variacao', 'id_variacao');
    }

    public function pedidoItems()
    {
        return $this->hasMany(PedidoItem::class, 'id_variacao', 'id_variacao');
    }
}
