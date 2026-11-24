<?php
// Layout público: cabeçalho, conteúdo, rodapé, gavetas (carrinho e favoritos) e busca.
// Variáveis opcionais: $titulo, $metaDesc, $paginaHome, $scripts, $jsonLd, $classeCorpo.
$titulo     = $titulo ?? 'Lumière Velas Aromáticas';
$metaDesc   = $metaDesc ?? 'Velas aromáticas de cera vegetal feitas à mão. Pague no PIX com desconto ou em até 6x sem juros.';
$paginaHome = $paginaHome ?? false;
$scripts    = $scripts ?? [];
$qtdCarrinho = Carrinho::quantidadeTotal();
$familiasMenu = array_values(array_filter($familiasMenu ?? (new Categoria())->listarAtivas(), fn($f) => (int)$f['total'] > 0));
?>
<!doctype html>
<html lang="pt-BR" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo) ?></title>
    <meta name="description" content="<?= e($metaDesc) ?>">
    <meta name="theme-color" content="#F1E8DC">
    <meta name="csrf" content="<?= e(csrf_token()) ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Lumière Velas Aromáticas">
    <meta property="og:title" content="<?= e($titulo) ?>">
    <meta property="og:description" content="<?= e($metaDesc) ?>">
    <meta property="og:locale" content="pt_BR">
    <link rel="icon" href="public/assets/img/favicon/favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="public/assets/img/favicon/icon-32.png">
    <link rel="apple-touch-icon" href="public/assets/img/favicon/apple-touch-icon.png">
    <link rel="preload" href="public/assets/fonts/bodoni-moda-latin-opsz-normal.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="public/assets/fonts/hanken-grotesk-latin-wght-normal.woff2" as="font" type="font/woff2" crossorigin>
    <?php if ($paginaHome): ?>
        <link rel="preload" href="public/assets/img/hero-vela.webp" as="image" type="image/webp" fetchpriority="high">
    <?php endif; ?>
    <link rel="preconnect" href="https://images.unsplash.com">
    <link rel="stylesheet" href="<?= e(asset('public/assets/css/base.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('public/assets/css/site.css')) ?>">
    <script>document.documentElement.classList.replace('no-js', 'js');</script>
    <?php if (!empty($jsonLd)): ?>
        <script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
    <?php endif; ?>
</head>
<body class="<?= $paginaHome ? 'pagina-home' : 'pagina-interna' ?> <?= e($classeCorpo ?? '') ?>">
<a class="pular" href="#conteudo">Pular para o conteúdo</a>

<?php include RAIZ . '/views/partials/topo_publico.php'; ?>

<main id="conteudo" tabindex="-1">
    <?php foreach (flashes() as $aviso): ?>
        <div class="aviso aviso-<?= e($aviso['tipo']) ?>" role="status"><?= e($aviso['msg']) ?></div>
    <?php endforeach; ?>
    <?= $conteudo ?>
</main>

<?php include RAIZ . '/views/partials/rodape_publico.php'; ?>
<?php include RAIZ . '/views/partials/gavetas.php'; ?>

<script src="public/assets/js/vendor/gsap.min.js" defer></script>
<script src="public/assets/js/vendor/ScrollTrigger.min.js" defer></script>
<script src="public/assets/js/vendor/lenis.min.js" defer></script>
<script src="<?= e(asset('public/assets/js/site.js')) ?>" defer></script>
<?php foreach ($scripts as $s): ?>
    <script src="<?= e($s) ?>" defer></script>
<?php endforeach; ?>
</body>
</html>
