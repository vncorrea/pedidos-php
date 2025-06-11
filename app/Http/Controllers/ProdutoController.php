<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Variacao;
use App\Models\Estoque;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $produtos = Produto::with(['variacoes.estoque'])->get();
        return view('produtos.index', compact('produtos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('produtos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'preco' => 'required|numeric|min:0',
            'descricao' => 'nullable|string',
            'variacoes' => 'required|array|min:1',
            'variacoes.*.nome' => 'required|string|max:255',
            'variacoes.*.preco_adicional' => 'required|numeric|min:0',
            'variacoes.*.quantidade' => 'required|integer|min:0'
        ]);

        $produto = Produto::create([
            'nome' => $request->nome,
            'preco' => $request->preco,
            'descricao' => $request->descricao
        ]);

        foreach ($request->variacoes as $variacaoData) {
            $variacao = $produto->variacoes()->create([
                'nome' => $variacaoData['nome'],
                'preco_adicional' => $variacaoData['preco_adicional']
            ]);

            $variacao->estoque()->create([
                'quantidade' => $variacaoData['quantidade']
            ]);
        }

        return redirect()->route('produtos.index')
            ->with('success', 'Produto criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Produto $produto)
    {
        $produto->load(['variacoes.estoque']);
        return view('produtos.show', compact('produto'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Produto $produto)
    {
        $produto->load(['variacoes.estoque']);
        return view('produtos.edit', compact('produto'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Produto $produto)
    {
        $request->validate([
            'nome' => 'required|string|max:255',
            'preco' => 'required|numeric|min:0',
            'descricao' => 'nullable|string',
            'variacoes' => 'required|array|min:1',
            'variacoes.*.id' => 'nullable|exists:variacoes,id',
            'variacoes.*.nome' => 'required|string|max:255',
            'variacoes.*.preco_adicional' => 'required|numeric|min:0',
            'variacoes.*.quantidade' => 'required|integer|min:0'
        ]);

        $produto->update([
            'nome' => $request->nome,
            'preco' => $request->preco,
            'descricao' => $request->descricao
        ]);

        // Atualizar ou criar variações
        foreach ($request->variacoes as $variacaoData) {
            if (isset($variacaoData['id'])) {
                $variacao = Variacao::find($variacaoData['id']);
                $variacao->update([
                    'nome' => $variacaoData['nome'],
                    'preco_adicional' => $variacaoData['preco_adicional']
                ]);
                $variacao->estoque()->update([
                    'quantidade' => $variacaoData['quantidade']
                ]);
            } else {
                $variacao = $produto->variacoes()->create([
                    'nome' => $variacaoData['nome'],
                    'preco_adicional' => $variacaoData['preco_adicional']
                ]);
                $variacao->estoque()->create([
                    'quantidade' => $variacaoData['quantidade']
                ]);
            }
        }

        return redirect()->route('produtos.index')
            ->with('success', 'Produto atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produto $produto)
    {
        $produto->delete();
        return redirect()->route('produtos.index')
            ->with('success', 'Produto excluído com sucesso!');
    }
}
