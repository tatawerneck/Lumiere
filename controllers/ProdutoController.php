<?php
// Painel: cadastro de velas (com foto enviada ou link de foto), publicação e exclusão.
class ProdutoController
{
    public function index(): void
    {
        exige_admin();
        $busca = texto_limpo($_GET['busca'] ?? '', 60);
        $status = in_array($_GET['status'] ?? '', ['ativos', 'inativos', 'estoque_baixo'], true) ? $_GET['status'] : 'todos';
        renderiza('admin/produtos', [
            'titulo'   => 'Produtos',
            'secao'    => 'produto',
            'produtos' => (new Produto())->listarAdmin($busca, $status),
            'busca'    => $busca,
            'status'   => $status,
        ], 'admin');
    }

    public function novo(): void
    {
        exige_admin();
        $this->form(null);
    }

    public function editar(): void
    {
        exige_admin();
        $p = (new Produto())->buscarPorId((int)($_GET['id'] ?? 0));
        if (!$p) {
            flash('erro', 'Produto não encontrado.');
            redirecionar(url('produto'));
        }
        $this->form($p);
    }

    private function form(?array $p): void
    {
        renderiza('admin/produto_form', [
            'titulo'     => $p ? 'Editar vela' : 'Nova vela',
            'secao'      => 'produto',
            'p'          => $p,
            'categorias' => (new Categoria())->listarComContagem(),
            'scripts'    => [asset('public/assets/js/admin.js')],
        ], 'admin');
    }

    public function salvar(): void
    {
        exige_admin();
        $id = (int)($_POST['id'] ?? 0);
        $nome = texto_limpo($_POST['nome'] ?? '', 100);
        $momentos = array_values(array_intersect((array)($_POST['momentos'] ?? []), array_keys(momentos_lista())));
        $urlOk = fn($u) => ($u = trim((string)$u)) !== '' && filter_var($u, FILTER_VALIDATE_URL) && preg_match('#^https://#', $u) ? mb_substr($u, 0, 500) : null;
        $produtos = new Produto();
        $slug = Produto::gerarSlug($nome);
        if (!$produtos->slugDisponivel($slug, $id ?: null)) {
            $slug .= '-' . random_int(10, 99);
        }
        $preco = valor_decimal($_POST['preco'] ?? '', 100000);
        $dados = [
            'categoria_id'  => (int)($_POST['categoria_id'] ?? 0),
            'nome'          => $nome,
            'slug'          => $slug,
            'notas_topo'    => texto_limpo($_POST['notas_topo'] ?? '', 150) ?: null,
            'notas_coracao' => texto_limpo($_POST['notas_coracao'] ?? '', 150) ?: null,
            'notas_fundo'   => texto_limpo($_POST['notas_fundo'] ?? '', 150) ?: null,
            'descricao'     => texto_limpo($_POST['descricao'] ?? '', 1500) ?: null,
            'quando_usar'   => texto_limpo($_POST['quando_usar'] ?? '', 1500) ?: null,
            'momentos'      => implode(',', $momentos),
            'peso_g'        => max(1, min(5000, (int)($_POST['peso_g'] ?? 200))),
            'queima_h'      => max(1, min(500, (int)($_POST['queima_h'] ?? 45))),
            'preco'         => $preco,
            'preco_antigo'  => valor_decimal($_POST['preco_antigo'] ?? '', 100000),
            'estoque'       => max(0, (int)($_POST['estoque'] ?? 0)),
            'destaque'      => isset($_POST['destaque']),
            'novo'          => isset($_POST['novo']),
            'ativo'         => isset($_POST['ativo']),
            'imagem_url'    => $urlOk($_POST['imagem_url'] ?? ''),
            'imagem2_url'   => $urlOk($_POST['imagem2_url'] ?? ''),
        ];
        stash_old($_POST, ['csrf']);
        $voltar = $id > 0 ? url('produto', 'editar', ['id' => $id]) : url('produto', 'novo');

        if (mb_strlen($nome) < 2) {
            flash('erro', 'Informe o nome da vela.');
            redirecionar($voltar);
        }
        if (!(new Categoria())->buscarPorId($dados['categoria_id'])) {
            flash('erro', 'Escolha a família olfativa.');
            redirecionar($voltar);
        }
        if ($preco === null || $preco <= 0) {
            flash('erro', 'Informe um preço maior que zero.');
            redirecionar($voltar);
        }
        if ($dados['preco_antigo'] !== null && $dados['preco_antigo'] <= $preco) {
            $dados['preco_antigo'] = null;
        }
        if ($id > 0 && !$produtos->buscarPorId($id)) {
            flash('erro', 'Produto não encontrado.');
            redirecionar(url('produto'));
        }

        $produtoId = $produtos->salvar($dados, $id > 0 ? $id : null);
        limpa_old();
        if (!empty($_POST['remover_foto'])) {
            foreach (['jpg', 'png', 'webp'] as $ext) {
                @unlink(RAIZ . '/uploads/produtos/' . $produtoId . '.' . $ext);
            }
            $produtos->definirImagem($produtoId, null);
        }
        $erroImagem = $this->salvarImagem($produtoId);
        if ($erroImagem !== null) {
            flash('erro', 'Vela salva, mas a foto não foi aceita: ' . $erroImagem);
            redirecionar(url('produto', 'editar', ['id' => $produtoId]));
        }
        flash('ok', $id > 0 ? 'Vela atualizada.' : 'Vela cadastrada.');
        redirecionar(url('produto'));
    }

    public function alternar(): void
    {
        exige_admin();
        $id = (int)($_POST['id'] ?? 0);
        $ativo = ($_POST['ativo'] ?? '0') === '1';
        if ($id > 0) {
            (new Produto())->alternarAtivo($id, $ativo);
            flash('ok', $ativo ? 'Vela publicada na loja.' : 'Vela ocultada da loja.');
        }
        redirecionar(url('produto'));
    }

    public function excluir(): void
    {
        exige_admin();
        $id = (int)($_POST['id'] ?? 0);
        $produtos = new Produto();
        if (!$produtos->buscarPorId($id)) {
            flash('erro', 'Produto não encontrado.');
            redirecionar(url('produto'));
        }
        if ($produtos->referencias($id) > 0) {
            flash('erro', 'Esta vela já aparece em pedidos. Oculte-a em vez de excluir.');
            redirecionar(url('produto'));
        }
        $produtos->excluir($id);
        foreach (['jpg', 'png', 'webp'] as $ext) {
            @unlink(RAIZ . '/uploads/produtos/' . $id . '.' . $ext);
        }
        flash('ok', 'Vela excluída.');
        redirecionar(url('produto'));
    }

    // Valida pelo conteúdo real do arquivo (não só pela extensão) e guarda como {id}.{ext}.
    private function salvarImagem(int $id): ?string
    {
        $f = $_FILES['imagem'] ?? null;
        if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($f['error'] !== UPLOAD_ERR_OK) {
            return 'falha no envio.';
        }
        if ($f['size'] > (int)config('upload_max_bytes')) {
            return 'o arquivo passa de 3 MB.';
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
        $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
        if ($ext === null || @getimagesize($f['tmp_name']) === false) {
            return 'envie uma imagem JPG, PNG ou WebP.';
        }
        $dir = RAIZ . '/uploads/produtos';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        foreach (['jpg', 'png', 'webp'] as $antiga) {
            @unlink($dir . '/' . $id . '.' . $antiga);
        }
        if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $id . '.' . $ext)) {
            return 'não foi possível salvar o arquivo.';
        }
        (new Produto())->definirImagem($id, $ext);
        return null;
    }
}
