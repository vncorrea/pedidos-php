<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PedidoItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_pedido',
        'id_variacao',
        'quantidade',
        'preco_unitario',
        'subtotal'
    ];

    protected $casts = [
        'preco_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2'
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'id_pedido', 'id_pedido');
    }

    public function variacao()
    {
        return $this->belongsTo(Variacao::class, 'id_variacao', 'id_variacao');
    }
}
