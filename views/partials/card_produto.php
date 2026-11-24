<?php
// Cartão de produto (vitrines, carrossel, abas e relacionados). Recebe $p.
$idP = (int)$p['id'];
$linkCard = url('catalogo', 'produto', ['id' => $idP]);
$img1 = imagemTamanho(imagemProduto($p), 600, 750);
$img2 = imagemTamanho(imagem2Produto($p), 600, 750);
$desc = desconto_pct($p);
$esgotada = (int)$p['estoque'] <= 0;
$dadosFav = [
    'id'    => $idP,
    'nome'  => $p['nome'],
    'preco' => moeda($p['preco']),
    'img'   => imagemTamanho(imagemProduto($p), 160, 200),
    'url'   => $linkCard,
];
?>
<article class="card" data-card>
    <div class="card-midia">
        <a href="<?= e($linkCard) ?>" tabindex="-1" aria-hidden="true">
            <img class="card-img" src="<?= e($img1) ?>" alt="" width="600" height="750" loading="lazy" decoding="async">
            <?php if ($img2 !== $img1): ?>
                <img class="card-img-2" src="<?= e($img2) ?>" alt="" width="600" height="750" loading="lazy" decoding="async">
            <?php endif; ?>
        </a>
        <div class="card-selos">
            <?php if ($esgotada): ?>
                <span class="selo selo-esgotado">Esgotada</span>
            <?php elseif ($desc): ?>
                <span class="selo selo-desc">−<?= $desc ?>%</span>
            <?php elseif (!empty($p['novo'])): ?>
                <span class="selo">Novo</span>
            <?php endif; ?>
        </div>
        <button class="card-fav" type="button" data-fav='<?= e(json_encode($dadosFav, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>'
                aria-pressed="false" aria-label="Guardar <?= e($p['nome']) ?> nos favoritos">
            <?= icone('coracao', 20) ?>
        </button>
    </div>

    <div class="card-info">
        <p class="card-familia"><?= e($p['categoria_nome'] ?? '') ?></p>
        <h3 class="card-nome"><a href="<?= e($linkCard) ?>"><?= e($p['nome']) ?></a></h3>
        <p class="card-essencia"><?= e(primeira_frase($p['descricao'])) ?></p>
        <p class="card-quando"><?= icone('relogio', 14) ?><span><?= e(primeira_frase($p['quando_usar'])) ?></span></p>

        <div class="card-rodape">
            <div class="card-preco">
                <?php if ($desc): ?><s><?= e(moeda($p['preco_antigo'])) ?></s><?php endif; ?>
                <strong><?= e(moeda($p['preco'])) ?></strong>
                <small><?= e(moeda(preco_pix((float)$p['preco']))) ?> no PIX</small>
            </div>
            <form class="card-add" method="post" action="<?= e(url('carrinho', 'adicionar')) ?>" data-add-carrinho>
                <?= csrf_campo() ?>
                <input type="hidden" name="produto_id" value="<?= $idP ?>">
                <input type="hidden" name="quantidade" value="1">
                <button class="card-sacola" type="submit" <?= $esgotada ? 'disabled' : '' ?>
                        aria-label="<?= $esgotada ? e($p['nome']) . ' esgotada' : 'Adicionar ' . e($p['nome']) . ' ao carrinho' ?>">
                    <?= icone('sacola', 20) ?>
                </button>
            </form>
        </div>
    </div>
</article>
