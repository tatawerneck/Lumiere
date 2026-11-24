<?php
// Finalização da compra: dados, entrega e pagamento (crédito, débito ou PIX).
// Modo simulação: cartões válidos são aprovados na hora; o número completo e o CVV nunca são gravados.
class CheckoutController
{
    private const UFS = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];

    public function index(): void
    {
        $resumo = Carrinho::resumo();
        if (!$resumo['itens']) {
            flash('erro', 'Seu carrinho está vazio. Escolha uma vela para continuar.');
            redirecionar(url('catalogo'));
        }
        $u = usuario_logado();
        $conta = $u ? (new Usuario())->buscarPorId((int)$u['id']) : null;
        renderiza('publico/checkout', [
            'titulo'  => 'Finalizar compra · Lumière',
            'resumo'  => $resumo,
            'conta'   => $conta,
            'ufs'     => self::UFS,
            'scripts' => [asset('public/assets/js/checkout.js')],
        ]);
    }

    public function finalizar(): void
    {
        $itens = Carrinho::itens();
        if (!$itens) {
            flash('erro', 'Seu carrinho está vazio.');
            redirecionar(url('catalogo'));
        }

        $d = [
            'nome'        => texto_limpo($_POST['nome'] ?? '', 80),
            'email'       => mb_strtolower(texto_limpo($_POST['email'] ?? '', 120)),
            'cpf'         => so_digitos($_POST['cpf'] ?? ''),
            'telefone'    => so_digitos($_POST['telefone'] ?? ''),
            'cep'         => so_digitos($_POST['cep'] ?? ''),
            'endereco'    => texto_limpo($_POST['endereco'] ?? '', 150),
            'numero'      => texto_limpo($_POST['numero'] ?? '', 10),
            'complemento' => texto_limpo($_POST['complemento'] ?? '', 60),
            'bairro'      => texto_limpo($_POST['bairro'] ?? '', 80),
            'cidade'      => texto_limpo($_POST['cidade'] ?? '', 80),
            'uf'          => strtoupper(texto_limpo($_POST['uf'] ?? '', 2)),
            'pagamento'   => (string)($_POST['pagamento'] ?? ''),
        ];
        stash_old($d);

        $erro = $this->validarDados($d);
        $cartao = null;
        if ($erro === null && in_array($d['pagamento'], ['credito', 'debito'], true)) {
            [$erro, $cartao] = $this->validarCartao($d['pagamento']);
        }
        if ($erro !== null) {
            flash('erro', $erro);
            redirecionar(url('checkout'));
        }

        $resumo = Carrinho::resumo($itens, $d['pagamento']);
        $parcelas = 1;
        if ($d['pagamento'] === 'credito') {
            $parcelas = max(1, min((int)($_POST['parcelas'] ?? 1), $this->maxParcelas($resumo['total'])));
        }

        $aprovado = $cartao !== null && $cartao['aprovado'];
        if ($cartao !== null && !$aprovado) {
            flash('erro', 'Pagamento recusado pela operadora do cartão. Confira os dados ou escolha outra forma de pagamento.');
            redirecionar(url('checkout'));
        }

        $u = usuario_logado();
        try {
            $pedido = (new Pedido())->criar($d + [
                'usuario_id'      => $u['id'] ?? null,
                'subtotal'        => $resumo['subtotal'],
                'desconto'        => $resumo['desconto'],
                'frete'           => $resumo['frete'],
                'total'           => $resumo['total'],
                'parcelas'        => $parcelas,
                'cartao_bandeira' => $cartao['bandeira'] ?? null,
                'cartao_final'    => $cartao['final'] ?? null,
                'status'          => $aprovado ? 'pago' : 'aguardando_pagamento',
                'pix_txid'        => null,
            ], $itens);
        } catch (RuntimeException $e) {
            flash('erro', $e->getMessage());
            redirecionar(url('carrinho'));
        }

        if ($u) {
            (new Usuario())->atualizarContato((int)$u['id'], $d['cpf'], $d['telefone']);
        }
        Carrinho::limpar();
        limpa_old();
        $_SESSION['pedidos_feitos'][] = $pedido['codigo'];

        if ($aprovado) {
            $envio = NotaFiscal::emitirEEnviar($pedido['id']);
            $_SESSION['envio_nota'][$pedido['codigo']] = $envio['ok'];
        }
        redirecionar(url('pedido', 'confirmacao', ['codigo' => $pedido['codigo']]));
    }

    private function maxParcelas(float $total): int
    {
        return max(1, min((int)config('parcelas_max'), (int)floor($total / (float)config('parcela_minima'))));
    }

    private function validarDados(array $d): ?string
    {
        if (mb_strlen($d['nome']) < 5 || !str_contains($d['nome'], ' ')) {
            return 'Informe seu nome completo.';
        }
        if (!filter_var($d['email'], FILTER_VALIDATE_EMAIL)) {
            return 'Informe um e-mail válido. É para ele que enviamos a nota fiscal.';
        }
        if (!cpf_valido($d['cpf'])) {
            return 'O CPF informado não é válido.';
        }
        if (strlen($d['telefone']) < 10 || strlen($d['telefone']) > 11) {
            return 'Informe um telefone com DDD.';
        }
        if (strlen($d['cep']) !== 8) {
            return 'Informe um CEP com 8 números.';
        }
        if ($d['endereco'] === '' || $d['numero'] === '' || $d['bairro'] === '' || $d['cidade'] === '') {
            return 'Preencha o endereço completo de entrega.';
        }
        if (!in_array($d['uf'], self::UFS, true)) {
            return 'Escolha o estado (UF).';
        }
        if (!in_array($d['pagamento'], ['credito', 'debito', 'pix'], true)) {
            return 'Escolha a forma de pagamento.';
        }
        return null;
    }

    /** @return array{0: ?string, 1: ?array} */
    private function validarCartao(string $tipo): array
    {
        $numero = so_digitos($_POST['cartao_numero'] ?? '');
        $nome = texto_limpo($_POST['cartao_nome'] ?? '', 60);
        $validade = so_digitos($_POST['cartao_validade'] ?? '');
        $cvv = so_digitos($_POST['cartao_cvv'] ?? '');

        if (strlen($numero) < 13 || strlen($numero) > 19 || !self::luhn($numero)) {
            return ['O número do cartão não é válido.', null];
        }
        if (mb_strlen($nome) < 4) {
            return ['Informe o nome como está impresso no cartão.', null];
        }
        if (strlen($validade) !== 4) {
            return ['Informe a validade no formato MM/AA.', null];
        }
        $mes = (int)substr($validade, 0, 2);
        $ano = 2000 + (int)substr($validade, 2, 2);
        $fimDoMes = mktime(23, 59, 59, $mes + 1, 0, $ano);
        if ($mes < 1 || $mes > 12 || $fimDoMes < time()) {
            return ['O cartão está vencido ou a validade não é válida.', null];
        }
        $bandeira = self::bandeira($numero);
        $tamCvv = $bandeira === 'Amex' ? 4 : 3;
        if (strlen($cvv) !== $tamCvv) {
            return ["O código de segurança (CVV) deve ter $tamCvv números.", null];
        }
        // Simulação: este número de teste é sempre recusado (para testar a mensagem de recusa).
        $aprovado = $numero !== '4000000000000002';
        return [null, ['bandeira' => $bandeira, 'final' => substr($numero, -4), 'aprovado' => $aprovado, 'tipo' => $tipo]];
    }

    private static function luhn(string $n): bool
    {
        $soma = 0;
        $alt = false;
        for ($i = strlen($n) - 1; $i >= 0; $i--) {
            $d = (int)$n[$i];
            if ($alt) {
                $d *= 2;
                if ($d > 9) {
                    $d -= 9;
                }
            }
            $soma += $d;
            $alt = !$alt;
        }
        return $soma % 10 === 0;
    }

    public static function bandeira(string $n): string
    {
        if (preg_match('/^(4011|4312|4389|4514|4576|5041|5066|5067|509|6277|6362|6363|650|6516|6550)/', $n)) return 'Elo';
        if (preg_match('/^(606282|3841)/', $n)) return 'Hipercard';
        if (preg_match('/^3[47]/', $n)) return 'Amex';
        if (preg_match('/^(5[1-5]|2(2[2-9]|[3-6]\d|7[01]|720))/', $n)) return 'Mastercard';
        if (preg_match('/^4/', $n)) return 'Visa';
        return 'Cartão';
    }
}
