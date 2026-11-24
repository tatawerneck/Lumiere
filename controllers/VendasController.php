<?php
// Painel: pedidos (vendas do site), status, confirmação de PIX e notas fiscais.
class VendasController
{
    public function index(): void
    {
        exige_admin();
        $f = [
            'status' => in_array($_GET['status'] ?? '', ['aguardando_pagamento', 'pago', 'enviado', 'entregue', 'cancelado'], true) ? $_GET['status'] : '',
            'busca'  => texto_limpo($_GET['busca'] ?? '', 60),
            'de'     => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($_GET['de'] ?? '')) ? $_GET['de'] : '',
            'ate'    => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($_GET['ate'] ?? '')) ? $_GET['ate'] : '',
        ];
        $pedido = new Pedido();
        renderiza('admin/pedidos', [
            'titulo'   => 'Pedidos',
            'secao'    => 'vendas',
            'filtros'  => $f,
            'pedidos'  => $pedido->listar($f),
            'contagem' => $pedido->contarPorStatus(),
        ], 'admin');
    }

    public function ver(): void
    {
        exige_admin();
        $pedidoModel = new Pedido();
        $p = $pedidoModel->buscarPorId((int)($_GET['id'] ?? 0));
        if (!$p) {
            flash('erro', 'Pedido não encontrado.');
            redirecionar(url('vendas'));
        }
        renderiza('admin/pedido_ver', [
            'titulo' => 'Pedido ' . $p['codigo'],
            'secao'  => 'vendas',
            'p'      => $p,
            'itens'  => $pedidoModel->itens((int)$p['id']),
            'nota'   => (new NotaFiscal())->doPedido((int)$p['id']),
        ], 'admin');
    }

    public function status(): void
    {
        exige_admin();
        $id = (int)($_POST['id'] ?? 0);
        $novo = (string)($_POST['status'] ?? '');
        $pedidoModel = new Pedido();
        $antes = $pedidoModel->buscarPorId($id);
        try {
            $pedidoModel->alterarStatus($id, $novo);
            $msg = 'Status alterado para "' . status_pedido($novo) . '".';
            // Ao confirmar o pagamento pelo painel, a nota é emitida e enviada.
            if ($antes && $antes['status'] === 'aguardando_pagamento' && $novo === 'pago') {
                $envio = NotaFiscal::emitirEEnviar($id);
                $msg .= $envio['ok'] ? ' Nota fiscal emitida e enviada por e-mail.' : ' Nota fiscal emitida. ' . $envio['info'];
            }
            flash('ok', $msg);
        } catch (Throwable $e) {
            flash('erro', $e->getMessage());
        }
        redirecionar(url('vendas', 'ver', ['id' => $id]));
    }

    public function reenviarNota(): void
    {
        exige_admin();
        $id = (int)($_POST['id'] ?? 0);
        $p = (new Pedido())->buscarPorId($id);
        if (!$p || !in_array($p['status'], ['pago', 'enviado', 'entregue'], true)) {
            flash('erro', 'A nota só é emitida para pedidos pagos.');
            redirecionar(url('vendas', 'ver', ['id' => $id]));
        }
        $envio = NotaFiscal::emitirEEnviar($id);
        flash($envio['ok'] ? 'ok' : 'erro', $envio['ok'] ? 'Nota fiscal enviada para ' . $p['email'] . '.' : $envio['info']);
        redirecionar(url('vendas', 'ver', ['id' => $id]));
    }

    public function notas(): void
    {
        exige_admin();
        renderiza('admin/notas', [
            'titulo' => 'Notas fiscais',
            'secao'  => 'notas',
            'notas'  => (new NotaFiscal())->listar(),
            'smtpOk' => (string)config('smtp_senha') !== '',
        ], 'admin');
    }
}
