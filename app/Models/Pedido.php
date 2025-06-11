<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'subtotal',
        'frete',
        'desconto',
        'total',
        'nome_cliente',
        'email_cliente',
        'cep',
        'endereco',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
        'id_cupom'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'frete' => 'decimal:2',
        'desconto' => 'decimal:2',
        'total' => 'decimal:2'
    ];

    public function items()
    {
        return $this->hasMany(PedidoItem::class, 'id_pedido', 'id_pedido');
    }

    public function cupom()
    {
        return $this->belongsTo(Cupom::class, 'id_cupom', 'id_cupom');
    }
}
