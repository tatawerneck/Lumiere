<?php
// Gavetas laterais (carrinho e favoritos), busca em tela cheia e aviso flutuante.
$resumo = Carrinho::resumo();
?>
<div class="gaveta-fundo" data-gaveta-fundo hidden></div>

<aside class="gaveta" id="gaveta-carrinho" data-gaveta="carrinho" role="dialog" aria-modal="true" aria-labelledby="gaveta-carrinho-titulo" hidden>
    <header class="gaveta-topo">
        <h2 id="gaveta-carrinho-titulo">Seu carrinho</h2>
        <button class="topo-icone" type="button" data-gaveta-fechar aria-label="Fechar carrinho"><?= icone('fechar') ?></button>
    </header>
    <div class="gaveta-corpo" data-carrinho-conteudo>
        <?php include RAIZ . '/views/partials/carrinho_conteudo.php'; ?>
    </div>
</aside>

<aside class="gaveta" id="gaveta-favoritos" data-gaveta="favoritos" role="dialog" aria-modal="true" aria-labelledby="gaveta-fav-titulo" hidden>
    <header class="gaveta-topo">
        <h2 id="gaveta-fav-titulo">Favoritos</h2>
        <button class="topo-icone" type="button" data-gaveta-fechar aria-label="Fechar favoritos"><?= icone('fechar') ?></button>
    </header>
    <div class="gaveta-corpo">
        <ul class="gaveta-lista" data-favoritos-lista></ul>
        <div class="gaveta-vazia" data-favoritos-vazio>
            <?= icone('coracao', 34) ?>
            <p>Toque no coração de uma vela para guardá-la aqui.</p>
            <a class="botao botao-linha" href="<?= e(url('catalogo')) ?>">Ver as 20 velas</a>
        </div>
    </div>
</aside>

<div class="busca" data-busca role="dialog" aria-modal="true" aria-label="Buscar velas" hidden>
    <div class="busca-dentro">
        <form class="busca-form" action="index.php" method="get" role="search">
            <input type="hidden" name="controller" value="catalogo">
            <label class="sr" for="busca-campo">Buscar por nome, nota ou família</label>
            <?= icone('busca', 26) ?>
            <input id="busca-campo" type="search" name="busca" placeholder="Lavanda, baunilha, madeira..." autocomplete="off" maxlength="60" data-busca-campo>
            <button class="topo-icone" type="button" data-busca-fechar aria-label="Fechar busca"><?= icone('fechar') ?></button>
        </form>
        <div class="busca-resultados" data-busca-resultados aria-live="polite"></div>
        <div class="busca-atalhos" data-busca-atalhos>
            <p class="busca-titulo">Buscas frequentes</p>
            <div class="busca-chips">
                <?php foreach (['Lavanda', 'Baunilha', 'Sândalo', 'Rosas', 'Café', 'Cítrico'] as $termo): ?>
                    <a class="chip" href="<?= e(url('catalogo', 'index', ['busca' => $termo])) ?>"><?= e($termo) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="toast" data-toast role="status" aria-live="polite" hidden>
    <img class="toast-img" alt="" width="44" height="55" data-toast-img hidden>
    <p data-toast-txt></p>
    <button type="button" class="toast-acao" data-gaveta-abrir="carrinho" data-toast-acao hidden>Ver carrinho</button>
</div>
