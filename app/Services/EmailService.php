<?php

namespace App\Services;

use App\Models\Pedido;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    public function enviarConfirmacaoPedido(Pedido $pedido)
    {
        Mail::send('emails.pedido-confirmado', ['pedido' => $pedido], function ($message) use ($pedido) {
            $message->to($pedido->email_cliente)
                   ->subject('Pedido #' . $pedido->id_pedido . ' - Confirmado');
        });
    }
} 