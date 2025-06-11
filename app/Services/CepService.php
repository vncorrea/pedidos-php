<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CepService
{
    public function consultar(string $cep)
    {
        $cep = preg_replace('/[^0-9]/', '', $cep);
        
        if (strlen($cep) !== 8) {
            return null;
        }

        $response = Http::get("https://viacep.com.br/ws/{$cep}/json/");

        if ($response->successful() && !isset($response->json()['erro'])) {
            return $response->json();
        }

        return null;
    }
} 