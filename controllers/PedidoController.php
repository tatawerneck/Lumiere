<?php
// Depois da compra: confirmação, PIX, "Meus pedidos" e detalhes.
class PedidoController
{
    // Quem pode ver o pedido: quem acabou de comprar nesta sessão, o cliente dono ou o admin.
    private function pedidoPermitido(string $codigo): ?array
    {
        $pedido = (new Pedido())->buscarPorCodigo($codigo);
        if (!$pedido) {
            return null;
        }
        $u = usuario_logado();
        $daSessao = in_array($codigo, (array)($_SESSION['pedidos_feitos'] ?? []), true);
        $dono = $u && $pedido['usuario_id'] !== null && (int)$pedido['usuario_id'] === (int)$u['id'];
        return ($daSessao || $dono || eh_admin()) ? $pedido : null;
    }

    public function confirmacao(): void
    {
        $codigo = preg_replace('/[^A-Z0-9]/', '', strtoupper((string)($_GET['codigo'] ?? '')));
        $pedido = $this->pedidoPermitido($codigo);
        if (!$pedido) {
            http_response_code(404);
            renderiza('erro', ['codigo' => 404, 'mensagem' => 'Pedido não encontrado.']);
            return;
        }
        $pix = null;
        if ($pedido['pagamento'] === 'pix' && $pedido['status'] === 'aguardando_pagamento') {
            $pix = Pix::payload((string)config('pix_chave'), (string)config('pix_nome'), (string)config('pix_cidade'),
                                (float)$pedido['total'], $pedido['codigo']);
        }
        renderiza('publico/pedido_confirmacao', [
            'titulo'  => 'Pedido ' . $pedido['codigo'] . ' · Lumière',
            'pedido'  => $pedido,
            'itens'   => (new Pedido())->itens((int)$pedido['id']),
            'nota'    => (new NotaFiscal())->doPedido((int)$pedido['id']),
            'pix'     => $pix,
            'scripts' => [asset('public/assets/js/vendor/qrcode.js'), asset('public/assets/js/pedido.js')],
        ]);
    }

    // Modo simulação: confirma o PIX como se o banco tivesse avisado o pagamento.
    public function simularPix(): void
    {
        $codigo = preg_replace('/[^A-Z0-9]/', '', strtoupper((string)($_POST['codigo'] ?? '')));
        $pedido = $this->pedidoPermitido($codigo);
        if (!$pedido || config('pagamento_modo') !== 'simulacao') {
            http_response_code(404);
            renderiza('erro', ['codigo' => 404, 'mensagem' => 'Pedido não encontrado.']);
            return;
        }
        if ((new Pedido())->marcarPago((int)$pedido['id'])) {
            $envio = NotaFiscal::emitirEEnviar((int)$pedido['id']);
            $_SESSION['envio_nota'][$codigo] = $envio['ok'];
            flash('ok', 'Pagamento PIX confirmado. A nota fiscal foi gerada.');
        }
        redirecionar(url('pedido', 'confirmacao', ['codigo' => $codigo]));
    }

    // Consulta de status (o QR Code fica verificando a cada poucos segundos).
    public function status(): void
    {
        $codigo = preg_replace('/[^A-Z0-9]/', '', strtoupper((string)($_GET['codigo'] ?? '')));
        $pedido = $this->pedidoPermitido($codigo);
        json_resposta(['status' => $pedido['status'] ?? null]);
    }

    public function meus(): void
    {
        exige_login();
        $u = usuario_logado();
        renderiza('cliente/meus_pedidos', [
            'titulo'  => 'Meus pedidos · Lumière',
            'pedidos' => (new Pedido())->doUsuario((int)$u['id']),
        ]);
    }
}
