<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id('id_pedido');
            $table->string('status')->default('pendente');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('frete', 10, 2);
            $table->decimal('desconto', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('nome_cliente');
            $table->string('email_cliente');
            $table->string('cep');
            $table->string('endereco');
            $table->string('numero');
            $table->string('complemento')->nullable();
            $table->string('bairro');
            $table->string('cidade');
            $table->string('estado');
            $table->foreignId('id_cupom')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
