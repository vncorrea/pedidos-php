<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Variacao;
use App\Models\Cupom;
use App\Services\CarrinhoService;
use App\Services\CepService;
use App\Services\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    private $carrinhoService;
    private $cepService;
    private $emailService;

    public function __construct(
        CarrinhoService $carrinhoService,
        CepService $cepService,
        EmailService $emailService
    ) {
        $this->carrinhoService = $carrinhoService;
        $this->cepService = $cepService;
        $this->emailService = $emailService;
    }

    public function index()
    {
        $pedidos = Pedido::with(['items.variacao.produto'])->get();
        return view('pedidos.index', compact('pedidos'));
    }

    public function show(Pedido $pedido)
    {
        $pedido->load(['items.variacao.produto', 'cupom']);
        return view('pedidos.show', compact('pedido'));
    }

    public function carrinho()
    {
        $carrinho = $this->carrinhoService->getCarrinho();
        $subtotal = $this->carrinhoService->getSubtotal();
        $frete = $this->carrinhoService->getFrete();
        $total = $this->carrinhoService->getTotal();

        return view('pedidos.carrinho', compact('carrinho', 'subtotal', 'frete', 'total'));
    }

    public function adicionarAoCarrinho(Request $request)
    {
        $request->validate([
            'variacao_id' => 'required|exists:variacoes,id',
            'quantidade' => 'required|integer|min:1'
        ]);

        $variacao = Variacao::with('estoque')->findOrFail($request->variacao_id);

        if ($variacao->estoque->quantidade < $request->quantidade) {
            return back()->with('error', 'Quantidade indisponível em estoque.');
        }

        $this->carrinhoService->adicionar($variacao, $request->quantidade);

        return redirect()->route('carrinho')
            ->with('success', 'Produto adicionado ao carrinho!');
    }

    public function removerDoCarrinho(Request $request)
    {
        $request->validate([
            'variacao_id' => 'required|exists:variacoes,id'
        ]);

        $this->carrinhoService->remover($request->variacao_id);

        return redirect()->route('carrinho')
            ->with('success', 'Produto removido do carrinho!');
    }

    public function atualizarCarrinho(Request $request)
    {
        $request->validate([
            'variacao_id' => 'required|exists:variacoes,id',
            'quantidade' => 'required|integer|min:1'
        ]);

        $variacao = Variacao::with('estoque')->findOrFail($request->variacao_id);

        if ($variacao->estoque->quantidade < $request->quantidade) {
            return back()->with('error', 'Quantidade indisponível em estoque.');
        }

        $this->carrinhoService->atualizar($request->variacao_id, $request->quantidade);

        return redirect()->route('carrinho')
            ->with('success', 'Carrinho atualizado!');
    }

    public function checkout()
    {
        $carrinho = $this->carrinhoService->getCarrinho();
        
        if (empty($carrinho)) {
            return redirect()->route('carrinho')
                ->with('error', 'Seu carrinho está vazio!');
        }

        $subtotal = $this->carrinhoService->getSubtotal();
        $frete = $this->carrinhoService->getFrete();
        $total = $this->carrinhoService->getTotal();

        return view('pedidos.checkout', compact('carrinho', 'subtotal', 'frete', 'total'));
    }

    public function finalizarPedido(Request $request)
    {
        $request->validate([
            'nome_cliente' => 'required|string|max:255',
            'email_cliente' => 'required|email|max:255',
            'cep' => 'required|string|max:9',
            'endereco' => 'required|string|max:255',
            'numero' => 'required|string|max:20',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'required|string|max:255',
            'cidade' => 'required|string|max:255',
            'estado' => 'required|string|max:2',
            'cupom_codigo' => 'nullable|string|exists:cupons,codigo'
        ]);

        $carrinho = $this->carrinhoService->getCarrinho();
        
        if (empty($carrinho)) {
            return redirect()->route('carrinho')
                ->with('error', 'Seu carrinho está vazio!');
        }

        try {
            DB::beginTransaction();

            // Verificar estoque
            foreach ($carrinho as $item) {
                $variacao = Variacao::with('estoque')->find($item['id']);
                if ($variacao->estoque->quantidade < $item['quantidade']) {
                    throw new \Exception("Quantidade indisponível para {$variacao->produto->nome} - {$variacao->nome}");
                }
            }

            // Calcular totais
            $subtotal = $this->carrinhoService->getSubtotal();
            $frete = $this->carrinhoService->getFrete();
            $desconto = 0;

            // Validar e aplicar cupom
            if ($request->cupom_codigo) {
                $cupom = Cupom::where('codigo', $request->cupom_codigo)->first();
                if ($cupom && $cupom->isValid() && $subtotal >= $cupom->valor_minimo) {
                    $desconto = $cupom->valor_desconto;
                }
            }

            $total = $subtotal + $frete - $desconto;

            // Criar pedido
            $pedido = Pedido::create([
                'status' => 'pendente',
                'subtotal' => $subtotal,
                'frete' => $frete,
                'desconto' => $desconto,
                'total' => $total,
                'nome_cliente' => $request->nome_cliente,
                'email_cliente' => $request->email_cliente,
                'cep' => $request->cep,
                'endereco' => $request->endereco,
                'numero' => $request->numero,
                'complemento' => $request->complemento,
                'bairro' => $request->bairro,
                'cidade' => $request->cidade,
                'estado' => $request->estado,
                'cupom_id' => $cupom->id ?? null
            ]);

            // Criar itens do pedido e atualizar estoque
            foreach ($carrinho as $item) {
                $variacao = Variacao::with('estoque')->find($item['id']);
                
                $pedido->items()->create([
                    'variacao_id' => $variacao->id,
                    'quantidade' => $item['quantidade'],
                    'preco_unitario' => $item['preco'],
                    'subtotal' => $item['preco'] * $item['quantidade']
                ]);

                $variacao->estoque->decrement('quantidade', $item['quantidade']);
            }

            DB::commit();

            // Enviar e-mail de confirmação
            $this->emailService->enviarConfirmacaoPedido($pedido);

            // Limpar carrinho
            $this->carrinhoService->limpar();

            return redirect()->route('pedidos.show', $pedido)
                ->with('success', 'Pedido realizado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao finalizar pedido: ' . $e->getMessage());
        }
    }

    public function consultarCep(string $cep)
    {
        $endereco = $this->cepService->consultar($cep);
        
        if (!$endereco) {
            return response()->json(['error' => 'CEP não encontrado'], 404);
        }

        return response()->json($endereco);
    }

    public function webhook(Request $request)
    {
        $request->validate([
            'pedido_id' => 'required|exists:pedidos,id',
            'status' => 'required|string|in:pendente,aprovado,cancelado,entregue'
        ]);

        $pedido = Pedido::findOrFail($request->pedido_id);

        if ($request->status === 'cancelado') {
            // Restaurar estoque
            foreach ($pedido->items as $item) {
                $item->variacao->estoque->increment('quantidade', $item->quantidade);
            }
            $pedido->delete();
        } else {
            $pedido->update(['status' => $request->status]);
        }

        return response()->json(['message' => 'Status atualizado com sucesso']);
    }
}
