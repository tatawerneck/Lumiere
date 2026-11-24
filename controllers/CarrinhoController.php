<?php
// Carrinho: página completa e ações via fetch (adicionar, alterar, remover) que devolvem a gaveta atualizada.
class CarrinhoController
{
    public function index(): void
    {
        renderiza('publico/carrinho', [
            'titulo' => 'Seu carrinho · Lumière',
            'resumo' => Carrinho::resumo(),
        ]);
    }

    public function adicionar(): void
    {
        $id = (int)($_POST['produto_id'] ?? 0);
        $qtd = max(1, min(20, (int)($_POST['quantidade'] ?? 1)));
        $p = (new Produto())->buscarPublico($id);
        if (!$p) {
            $this->responder(false, 'Produto não encontrado.');
        }
        $noCarrinho = (int)(Carrinho::bruto()[$id] ?? 0);
        if ((int)$p['estoque'] < $noCarrinho + $qtd) {
            $disp = max(0, (int)$p['estoque'] - $noCarrinho);
            $this->responder(false, $disp > 0 ? "Só temos mais $disp unidade(s) de {$p['nome']}." : "{$p['nome']} está esgotada no momento.");
        }
        Carrinho::adicionar($id, $qtd);
        $this->responder(true, $p['nome'] . ' foi para o carrinho.', $p);
    }

    public function atualizar(): void
    {
        Carrinho::definir((int)($_POST['produto_id'] ?? 0), (int)($_POST['quantidade'] ?? 0));
        $this->responder(true, '');
    }

    public function remover(): void
    {
        Carrinho::remover((int)($_POST['produto_id'] ?? 0));
        $this->responder(true, 'Item removido.');
    }

    // Conteúdo da gaveta (GET), usado ao abrir a página e depois de cada ação.
    public function gaveta(): void
    {
        $this->responder(true, '');
    }

    private function responder(bool $ok, string $msg, ?array $produto = null): void
    {
        $resumo = Carrinho::resumo();
        if (!eh_ajax()) {
            flash($ok ? 'ok' : 'erro', $msg ?: 'Carrinho atualizado.');
            redirecionar(url('carrinho'));
        }
        ob_start();
        require RAIZ . '/views/partials/carrinho_conteudo.php';
        $html = (string)ob_get_clean();
        json_resposta([
            'ok'         => $ok,
            'mensagem'   => $msg,
            'quantidade' => $resumo['quantidade'],
            'html'       => $html,
            'produto'    => $produto ? ['nome' => $produto['nome'], 'imagem' => imagemTamanho(imagemProduto($produto), 160, 200)] : null,
        ], $ok ? 200 : 422);
    }
}
