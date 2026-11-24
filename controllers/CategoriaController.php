<?php
// Painel: famílias olfativas.
class CategoriaController
{
    public function index(): void
    {
        exige_admin();
        renderiza('admin/categorias', [
            'titulo'     => 'Famílias olfativas',
            'secao'      => 'categoria',
            'categorias' => (new Categoria())->listarComContagem(),
        ], 'admin');
    }

    public function salvar(): void
    {
        exige_admin();
        $id = (int)($_POST['id'] ?? 0);
        $nome = texto_limpo($_POST['nome'] ?? '', 60);
        $desc = texto_limpo($_POST['descricao'] ?? '', 255);
        $img = trim((string)($_POST['imagem_url'] ?? ''));
        if ($img !== '' && !preg_match('#^https://#', $img)) {
            $img = '';
        }
        if (mb_strlen($nome) < 2) {
            flash('erro', 'Informe o nome da família.');
            redirecionar(url('categoria'));
        }
        try {
            (new Categoria())->salvar($id > 0 ? $id : null, $nome, $desc, mb_substr($img, 0, 500));
            flash('ok', $id > 0 ? 'Família atualizada.' : 'Família criada.');
        } catch (PDOException $e) {
            flash('erro', $e->getCode() === '23000' ? 'Já existe uma família com esse nome.' : 'Não foi possível salvar.');
        }
        redirecionar(url('categoria'));
    }

    public function alternar(): void
    {
        exige_admin();
        (new Categoria())->alternarAtivo((int)($_POST['id'] ?? 0), ($_POST['ativo'] ?? '0') === '1');
        flash('ok', 'Família atualizada.');
        redirecionar(url('categoria'));
    }
}
