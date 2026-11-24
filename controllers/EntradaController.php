<?php
// Painel: entradas de estoque.
class EntradaController
{
    public function index(): void
    {
        exige_admin();
        renderiza('admin/entradas', [
            'titulo'   => 'Estoque',
            'secao'    => 'entrada',
            'entradas' => (new Entrada())->listarRecentes(60),
            'produtos' => (new Produto())->listarAdmin(),
        ], 'admin');
    }

    public function salvar(): void
    {
        exige_admin();
        $produtoId = (int)($_POST['produto_id'] ?? 0);
        $qtd = (int)($_POST['quantidade'] ?? 0);
        $custo = valor_decimal($_POST['custo_unitario'] ?? '', 100000);
        $data = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($_POST['data'] ?? '')) ? $_POST['data'] : date('Y-m-d');
        $obs = texto_limpo($_POST['observacao'] ?? '', 255) ?: null;
        if (!(new Produto())->buscarPorId($produtoId) || $qtd < 1 || $qtd > 100000) {
            flash('erro', 'Escolha a vela e uma quantidade maior que zero.');
            redirecionar(url('entrada'));
        }
        (new Entrada())->registrar($produtoId, $qtd, $custo, $data, $obs);
        flash('ok', "Entrada de $qtd unidade(s) registrada.");
        redirecionar(url('entrada'));
    }
}
