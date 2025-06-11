<?php

namespace App\Services;

use App\Models\Variacao;
use Illuminate\Support\Facades\Session;

class CarrinhoService
{
    private $freteService;

    public function __construct(FreteService $freteService)
    {
        $this->freteService = $freteService;
    }

    public function adicionar(Variacao $variacao, int $quantidade)
    {
        $carrinho = Session::get('carrinho', []);
        $id = $variacao->id_variacao;

        if (isset($carrinho[$id])) {
            $carrinho[$id]['quantidade'] += $quantidade;
        } else {
            $carrinho[$id] = [
                'id' => $variacao->id_variacao,
                'nome' => $variacao->produto->nome . ' - ' . $variacao->nome,
                'preco' => $variacao->produto->preco + $variacao->preco_adicional,
                'quantidade' => $quantidade
            ];
        }

        Session::put('carrinho', $carrinho);
    }

    public function remover(int $variacaoId)
    {
        $carrinho = Session::get('carrinho', []);
        unset($carrinho[$variacaoId]);
        Session::put('carrinho', $carrinho);
    }

    public function atualizar(int $variacaoId, int $quantidade)
    {
        $carrinho = Session::get('carrinho', []);
        if (isset($carrinho[$variacaoId])) {
            $carrinho[$variacaoId]['quantidade'] = $quantidade;
            Session::put('carrinho', $carrinho);
        }
    }

    public function getCarrinho()
    {
        return Session::get('carrinho', []);
    }

    public function getSubtotal()
    {
        $carrinho = $this->getCarrinho();
        $subtotal = 0;

        foreach ($carrinho as $item) {
            $subtotal += $item['preco'] * $item['quantidade'];
        }

        return $subtotal;
    }

    public function getFrete()
    {
        return $this->freteService->calcularFrete($this->getSubtotal());
    }

    public function getTotal()
    {
        return $this->getSubtotal() + $this->getFrete();
    }

    public function limpar()
    {
        Session::forget('carrinho');
    }
} 