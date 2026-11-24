<?php
// Cabeçalho público no desenho da referência: menu à esquerda, ícones e "CARRINHO (n)" à direita.
$u = usuario_logado();
$linkConta = $u ? (eh_admin() ? url('dashboard') : url('pedido', 'meus')) : url('auth', 'form');
$rotuloConta = $u ? (eh_admin() ? 'Painel administrativo' : 'Meus pedidos') : 'Entrar na conta';
?>
<header class="topo" data-topo>
    <div class="topo-barra">
        <button class="topo-icone topo-menu-btn" type="button" data-menu-abrir aria-expanded="false" aria-controls="menu-movel" aria-label="Abrir menu">
            <?= icone('menu') ?>
        </button>

        <nav class="topo-nav" aria-label="Principal">
            <ul>
                <li><a href="<?= e(url('catalogo', 'index', ['ordenar' => 'recentes'])) ?>">Lançamentos</a></li>
                <li><a href="<?= e(url('catalogo')) ?>">Catálogo</a></li>
                <li class="tem-mega" data-mega-item>
                    <button type="button" class="topo-nav-btn" aria-expanded="false" aria-controls="mega-colecoes" data-mega-btn>Coleções</button>
                    <div class="mega" id="mega-colecoes" data-mega>
                        <div class="mega-dentro">
                            <div class="mega-familias">
                                <?php foreach ($familiasMenu as $f): ?>
                                    <a class="mega-familia" href="<?= e(url('catalogo', 'index', ['familia' => $f['slug']])) ?>">
                                        <span class="mega-foto"><img src="<?= e(imagemTamanho((string)$f['imagem_url'], 240, 300)) ?>" alt="" width="120" height="150" loading="lazy"></span>
                                        <span class="mega-nome"><?= e($f['nome']) ?></span>
                                        <span class="mega-qtd"><?= (int)$f['total'] ?> <?= (int)$f['total'] === 1 ? 'vela' : 'velas' ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                            <div class="mega-momentos">
                                <p class="mega-titulo">Para cada momento</p>
                                <?php foreach (momentos_lista() as $slug => $nome): ?>
                                    <a href="<?= e(url('catalogo', 'index', ['momento' => $slug])) ?>"><?= e($nome) ?></a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </li>
                <li><a href="index.php#inspiracao">Inspiração</a></li>
            </ul>
        </nav>

        <a class="topo-marca" href="index.php" aria-label="Lumière, página inicial">LUMIERE</a>

        <div class="topo-acoes">
            <button class="topo-icone" type="button" data-busca-abrir aria-label="Buscar velas"><?= icone('busca') ?></button>
            <a class="topo-icone so-desktop" href="<?= e($linkConta) ?>" aria-label="<?= e($rotuloConta) ?>" title="<?= e($rotuloConta) ?>"><?= icone('usuario') ?></a>
            <button class="topo-icone so-desktop" type="button" data-gaveta-abrir="favoritos" aria-label="Favoritos">
                <?= icone('coracao') ?><span class="topo-bolha" data-fav-qtd hidden>0</span>
            </button>
            <a class="topo-icone so-desktop" href="<?= e(url('catalogo')) ?>" aria-label="Loja"><?= icone('sacola') ?></a>
            <button class="topo-carrinho" type="button" data-gaveta-abrir="carrinho" aria-label="Abrir carrinho, <?= $qtdCarrinho ?> itens">
                <?= icone('sacola') ?><span><span class="topo-carrinho-txt">Carrinho </span>(<span data-carrinho-qtd><?= $qtdCarrinho ?></span>)</span>
            </button>
        </div>
    </div>
</header>

<div class="menu-movel" id="menu-movel" data-menu-movel hidden>
    <div class="menu-movel-topo">
        <span class="menu-movel-marca">LUMIERE</span>
        <button class="topo-icone" type="button" data-menu-fechar aria-label="Fechar menu"><?= icone('fechar') ?></button>
    </div>
    <nav aria-label="Menu do celular">
        <a href="<?= e(url('catalogo', 'index', ['ordenar' => 'recentes'])) ?>">Lançamentos</a>
        <a href="<?= e(url('catalogo')) ?>">Catálogo</a>
        <a href="index.php#familias">Coleções</a>
        <a href="index.php#inspiracao">Inspiração</a>
        <a href="<?= e($linkConta) ?>"><?= e($rotuloConta) ?></a>
        <button type="button" data-gaveta-abrir="favoritos">Favoritos</button>
        <a href="<?= e(url('contato', 'form')) ?>">Contato</a>
    </nav>
    <p class="menu-movel-nota">Frete grátis acima de <?= e(moeda(config('frete_gratis_acima'))) ?>.<br>5% de desconto pagando no PIX.</p>
</div>
