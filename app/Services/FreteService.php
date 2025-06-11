<?php

namespace App\Services;

class FreteService
{
    public function calcularFrete(float $subtotal): float
    {
        if ($subtotal >= 200.00) {
            return 0.00; // Frete grátis
        }

        if ($subtotal >= 52.00 && $subtotal <= 166.59) {
            return 15.00;
        }

        return 20.00;
    }
}
