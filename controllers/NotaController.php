<?php
// Exibe a nota fiscal (para imprimir ou salvar em PDF pelo navegador).
class NotaController
{
    public function ver(): void
    {
        $codigo = preg_replace('/[^A-Z0-9]/', '', strtoupper((string)($_GET['codigo'] ?? '')));
        $pedidoModel = new Pedido();
        $pedido = $pedidoModel->buscarPorCodigo($codigo);
        $u = usuario_logado();
        $permitido = $pedido && (
            in_array($codigo, (array)($_SESSION['pedidos_feitos'] ?? []), true) || eh_admin()
            || ($u && $pedido['usuario_id'] !== null && (int)$pedido['usuario_id'] === (int)$u['id'])
        );
        $nota = $permitido ? (new NotaFiscal())->doPedido((int)$pedido['id']) : null;
        if (!$nota) {
            http_response_code(404);
            renderiza('erro', ['codigo' => 404, 'mensagem' => 'Nota fiscal não encontrada.']);
            return;
        }
        echo NotaFiscal::html($pedido, $pedidoModel->itens((int)$pedido['id']), $nota);
    }
}
