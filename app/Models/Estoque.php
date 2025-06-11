<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Estoque extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_variacao',
        'quantidade'
    ];

    public function variacao()
    {
        return $this->belongsTo(Variacao::class, 'id_variacao', 'id_variacao');
    }
}
