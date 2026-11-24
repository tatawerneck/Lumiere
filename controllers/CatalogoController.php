<?php
// Vitrine pública: lista com filtros, página do produto e sugestões da busca.
class CatalogoController
{
    public function index(): void
    {
        $filtros = [
            'busca'      => texto_limpo($_GET['busca'] ?? '', 60),
            'familia'    => preg_replace('/[^a-z0-9-]/', '', (string)($_GET['familia'] ?? '')),
            'momento'    => preg_replace('/[^a-z]/', '', (string)($_GET['momento'] ?? '')),
            'preco_max'  => valor_decimal($_GET['preco_max'] ?? '', 100000),
            'disponivel' => !empty($_GET['disponivel']),
            'promocao'   => !empty($_GET['promocao']),
            'ordenar'    => in_array($_GET['ordenar'] ?? '', Produto::ordenacoesPermitidas(), true) ? $_GET['ordenar'] : 'destaques',
        ];
        $pagina = max(1, (int)($_GET['pagina'] ?? 1));
        $porPagina = (int)config('por_pagina');
        $resultado = (new Produto())->buscarLoja($filtros, $pagina, $porPagina);
        $totalPaginas = max(1, (int)ceil($resultado['total'] / $porPagina));

        renderiza('publico/catalogo', [
            'titulo'       => 'Coleção de velas aromáticas · Lumière',
            'metaDesc'     => 'Conheça as 20 velas aromáticas Lumière: florais, amadeiradas, cítricas, gourmand e mais. Filtre por família e por momento.',
            'filtros'      => $filtros,
            'produtos'     => $resultado['itens'],
            'total'        => $resultado['total'],
            'pagina'       => min($pagina, $totalPaginas),
            'totalPaginas' => $totalPaginas,
            'familias'     => (new Categoria())->listarAtivas(),
            'scripts'      => [asset('public/assets/js/catalogo.js')],
        ]);
    }

    public function produto(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $model = new Produto();
        $p = $model->buscarPublico($id);
        if (!$p) {
            http_response_code(404);
            renderiza('erro', ['codigo' => 404, 'mensagem' => 'Essa vela não está disponível.']);
            return;
        }
        renderiza('publico/produto', [
            'titulo'      => $p['nome'] . ' · Vela aromática Lumière',
            'metaDesc'    => mb_substr((string)$p['descricao'], 0, 155),
            'p'           => $p,
            'relacionados'=> $model->relacionados((int)$p['id'], (int)$p['categoria_id'], 4),
            'scripts'     => [asset('public/assets/js/produto.js')],
        ]);
    }

    // Sugestões para a busca do cabeçalho (JSON).
    public function sugestoes(): void
    {
        $termo = texto_limpo($_GET['q'] ?? '', 40);
        if (mb_strlen($termo) < 2) {
            json_resposta(['itens' => []]);
        }
        $res = (new Produto())->buscarLoja(['busca' => $termo], 1, 6);
        json_resposta(['itens' => array_map(fn($p) => [
            'nome'    => $p['nome'],
            'familia' => $p['categoria_nome'],
            'preco'   => moeda($p['preco']),
            'imagem'  => imagemTamanho(imagemProduto($p), 160, 200),
            'url'     => url('catalogo', 'produto', ['id' => (int)$p['id']]),
        ], $res['itens']), 'total' => $res['total']]);
    }
}
