<?php
// Layout do painel administrativo: menu lateral azul-noite, área clara com cabeçalho e avisos.
// Variáveis: $titulo, $secao (item ativo do menu), $scripts.
$titulo  = $titulo ?? 'Painel';
$secao   = $secao ?? '';
$scripts = $scripts ?? [];
$u = usuario_logado();
$novasMensagens = (new Contato())->novos();
$aguardando = (int)(Database::conexao()->query("SELECT COUNT(*) FROM pedido WHERE status = 'aguardando_pagamento'")->fetchColumn());
$menu = [
    ['dashboard', url('dashboard'), 'casa', 'Dashboard', 0],
    ['vendas', url('vendas'), 'carrinho', 'Pedidos', $aguardando],
    ['notas', url('vendas', 'notas'), 'nota', 'Notas fiscais', 0],
    ['produto', url('produto'), 'sacola', 'Produtos', 0],
    ['categoria', url('categoria'), 'etiqueta', 'Famílias olfativas', 0],
    ['entrada', url('entrada'), 'entrada', 'Estoque', 0],
    ['cliente', url('cliente'), 'pessoas', 'Clientes', 0],
    ['contato', url('contato', 'listar'), 'carta', 'Mensagens', $novasMensagens],
    ['relatorio', url('relatorio'), 'grafico', 'Relatórios', 0],
    ['usuario', url('usuario'), 'chave', 'Acesso', 0],
];
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($titulo) ?> · Painel Lumière</title>
    <meta name="theme-color" content="#0F1B3D">
    <link rel="icon" href="public/assets/img/favicon/favicon.ico" sizes="any">
    <link rel="preload" href="public/assets/fonts/bodoni-moda-latin-opsz-normal.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="public/assets/fonts/hanken-grotesk-latin-wght-normal.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="<?= e(asset('public/assets/css/base.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('public/assets/css/admin.css')) ?>">
</head>
<body class="admin">
<a class="pular" href="#adm-conteudo">Pular para o conteúdo</a>

<aside class="adm-lateral" id="adm-lateral" data-adm-lateral>
    <a class="adm-marca" href="<?= e(url('dashboard')) ?>">
        <img src="public/assets/img/emblema-lumiere.png" alt="" width="120" height="108">
        <span class="adm-marca-nome">Lumière</span>
        <span class="adm-marca-sub">Velas aromáticas</span>
    </a>
    <nav class="adm-menu" aria-label="Painel">
        <?php foreach ($menu as [$chave, $href, $ic, $rotulo, $bolha]): ?>
            <a href="<?= e($href) ?>" class="<?= $secao === $chave ? 'ativo' : '' ?>" <?= $secao === $chave ? 'aria-current="page"' : '' ?>>
                <?= icone($ic, 22) ?><span><?= e($rotulo) ?></span>
                <?php if ($bolha > 0): ?><b class="adm-bolha" aria-label="<?= $bolha ?> pendentes"><?= $bolha ?></b><?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="adm-lateral-rodape">
        <span class="adm-filete" aria-hidden="true"></span>
        <p>Com cada vela,<br>um momento especial.</p>
        <a href="index.php" target="_blank" rel="noopener"><?= icone('olho', 16) ?> Ver a loja</a>
    </div>
</aside>

<div class="adm-principal">
    <header class="adm-topo">
        <button class="adm-menu-btn" type="button" data-adm-menu aria-controls="adm-lateral" aria-expanded="false" aria-label="Abrir menu"><?= icone('menu') ?></button>
        <div class="adm-titulo">
            <h1><?= e($titulo) ?></h1>
        </div>
        <div class="adm-usuario">
            <span>Olá, <strong><?= e(explode(' ', trim($u['nome'] ?? ''))[0]) ?></strong></span>
            <form method="post" action="<?= e(url('auth', 'sair')) ?>">
                <?= csrf_campo() ?>
                <button class="adm-sair" type="submit">Sair <?= icone('sair', 18) ?></button>
            </form>
        </div>
    </header>

    <main id="adm-conteudo" class="adm-conteudo" tabindex="-1">
        <?php foreach (flashes() as $aviso): ?>
            <div class="adm-aviso adm-aviso-<?= e($aviso['tipo']) ?>" role="status"><?= e($aviso['msg']) ?></div>
        <?php endforeach; ?>
        <?= $conteudo ?>
    </main>
</div>

<script src="<?= e(asset('public/assets/js/admin.js')) ?>" defer></script>
<?php foreach ($scripts as $s): if (str_contains($s, 'admin.js')) continue; ?>
    <script src="<?= e($s) ?>" defer></script>
<?php endforeach; ?>
</body>
</html>
