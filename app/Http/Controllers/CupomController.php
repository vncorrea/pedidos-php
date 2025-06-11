<?php

namespace App\Http\Controllers;

use App\Models\Cupom;
use Illuminate\Http\Request;

class CupomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cupons = Cupom::all();
        return view('cupons.index', compact('cupons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('cupons.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:255|unique:cupons',
            'valor_desconto' => 'required|numeric|min:0',
            'valor_minimo' => 'required|numeric|min:0',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after:data_inicio',
            'ativo' => 'boolean'
        ]);

        Cupom::create($request->all());

        return redirect()->route('cupons.index')
            ->with('success', 'Cupom criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Cupom $cupom)
    {
        return view('cupons.show', compact('cupom'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cupom $cupom)
    {
        return view('cupons.edit', compact('cupom'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cupom $cupom)
    {
        $request->validate([
            'codigo' => 'required|string|max:255|unique:cupons,codigo,' . $cupom->id,
            'valor_desconto' => 'required|numeric|min:0',
            'valor_minimo' => 'required|numeric|min:0',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after:data_inicio',
            'ativo' => 'boolean'
        ]);

        $cupom->update($request->all());

        return redirect()->route('cupons.index')
            ->with('success', 'Cupom atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cupom $cupom)
    {
        $cupom->delete();
        return redirect()->route('cupons.index')
            ->with('success', 'Cupom excluído com sucesso!');
    }
}
