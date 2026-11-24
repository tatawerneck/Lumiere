<?php
// Página do produto. Recebe $p e $relacionados.
$idP = (int)$p['id'];
$fotos = array_values(array_unique([imagemProduto($p), imagem2Produto($p)]));
$desc = desconto_pct($p);
$estoque = (int)$p['estoque'];
$momentos = array_filter(explode(',', (string)$p['momentos']));
$dadosFav = ['id' => $idP, 'nome' => $p['nome'], 'preco' => moeda($p['preco']),
             'img' => imagemTamanho(imagemProduto($p), 160, 200), 'url' => url('catalogo', 'produto', ['id' => $idP])];

// Dados estruturados para o Google (produto com oferta em reais).
$jsonLd = [
    '@context'    => 'https://schema.org',
    '@type'       => 'Product',
    'name'        => 'Vela aromática ' . $p['nome'],
    'image'       => array_map(fn($f) => str_starts_with($f, 'http') ? $f : url_absoluta($f), $fotos),
    'description' => $p['descricao'],
    'brand'       => ['@type' => 'Brand', 'name' => 'Lumière'],
    'category'    => 'Velas aromáticas > ' . $p['categoria_nome'],
    'weight'      => ['@type' => 'QuantitativeValue', 'value' => (int)$p['peso_g'], 'unitCode' => 'GRM'],
    'offers'      => [
        '@type'         => 'Offer',
        'priceCurrency' => 'BRL',
        'price'         => number_format((float)$p['preco'], 2, '.', ''),
        'availability'  => $estoque > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        'url'           => url_absoluta(url('catalogo', 'produto', ['id' => $idP])),
    ],
];
?>
<div class="wrap">
    <ol class="trilha pagina-cabeca-trilha" aria-label="Você está em">
        <li><a href="index.php">Início</a></li>
        <li><a href="<?= e(url('catalogo')) ?>">Coleção</a></li>
        <li><a href="<?= e(url('catalogo', 'index', ['familia' => $p['categoria_slug']])) ?>"><?= e($p['categoria_nome']) ?></a></li>
        <li aria-current="page"><?= e($p['nome']) ?></li>
    </ol>
</div>

<article class="wrap produto" data-produto-pagina>
    <div class="produto-galeria" data-galeria>
        <?php if (count($fotos) > 1): ?>
            <div class="produto-miniaturas" role="group" aria-label="Fotos do produto">
                <?php foreach ($fotos as $i => $f): ?>
                    <button type="button" data-foto="<?= e(imagemTamanho($f, 1000, 1250)) ?>" aria-current="<?= $i === 0 ? 'true' : 'false' ?>" aria-label="Ver foto <?= $i + 1 ?>">
                        <img src="<?= e(imagemTamanho($f, 168, 210)) ?>" alt="" width="84" height="105" loading="lazy">
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="produto-foto">
            <img src="<?= e(imagemTamanho($fotos[0], 1000, 1250)) ?>" alt="Vela aromática <?= e($p['nome']) ?>" width="1000" height="1250" fetchpriority="high" data-foto-principal>
            <div class="card-selos">
                <?php if ($estoque <= 0): ?><span class="selo selo-esgotado">Esgotada</span>
                <?php elseif ($desc): ?><span class="selo selo-desc">−<?= $desc ?>%</span>
                <?php elseif (!empty($p['novo'])): ?><span class="selo">Novo</span><?php endif; ?>
            </div>
        </div>
    </div>

    <div class="produto-info">
        <a class="produto-familia" href="<?= e(url('catalogo', 'index', ['familia' => $p['categoria_slug']])) ?>">Família <?= e(mb_strtolower($p['categoria_nome'])) ?></a>
        <h1><?= e($p['nome']) ?></h1>
        <p class="produto-essencia"><?= e($p['descricao']) ?></p>

        <div class="produto-preco">
            <?php if ($desc): ?><s>De <?= e(moeda($p['preco_antigo'])) ?></s><?php endif; ?>
            <span class="valor"><?= e(moeda($p['preco'])) ?></span>
            <span class="pix"><?= icone('pix', 16) ?> <?= e(moeda(preco_pix((float)$p['preco']))) ?> no PIX (5% de desconto)</span>
            <span class="parcela">ou <?= e(parcelamento((float)$p['preco'])) ?> no cartão</span>
        </div>

        <form class="produto-comprar" method="post" action="<?= e(url('carrinho', 'adicionar')) ?>" data-add-carrinho data-abrir-gaveta>
            <?= csrf_campo() ?>
            <input type="hidden" name="produto_id" value="<?= $idP ?>">
            <div class="qtd" data-stepper>
                <button type="button" data-menos aria-label="Diminuir quantidade"><?= icone('menos', 16) ?></button>
                <label class="sr" for="qtd-produto">Quantidade</label>
                <input id="qtd-produto" type="number" name="quantidade" value="1" min="1" max="<?= max(1, min(20, $estoque)) ?>" inputmode="numeric">
                <button type="button" data-mais aria-label="Aumentar quantidade"><?= icone('mais', 16) ?></button>
            </div>
            <button class="botao botao-grande" type="submit" <?= $estoque <= 0 ? 'disabled' : '' ?>>
                <?= $estoque > 0 ? 'Adicionar ao carrinho' : 'Esgotada' ?>
            </button>
            <button class="botao-fav" type="button" data-fav='<?= e(json_encode($dadosFav, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?>' aria-pressed="false" aria-label="Guardar nos favoritos">
                <?= icone('coracao', 22) ?><span class="fav-txt" aria-hidden="true">Guardar nos favoritos</span>
            </button>
        </form>

        <?php if ($estoque <= 0): ?>
            <p class="produto-estoque zero"><?= icone('alerta', 16) ?> Esgotada no momento. Volte em breve.</p>
        <?php elseif ($estoque <= (int)config('estoque_baixo')): ?>
            <p class="produto-estoque baixo"><?= icone('alerta', 16) ?> Últimas <?= $estoque ?> unidades.</p>
        <?php else: ?>
            <p class="produto-estoque"><?= icone('check', 16) ?> Em estoque, enviamos em até 2 dias úteis.</p>
        <?php endif; ?>

        <div class="quando">
            <?= icone('relogio', 22) ?>
            <div><strong>Quando usar</strong><?= e($p['quando_usar']) ?></div>
        </div>

        <dl class="piramide" aria-label="Notas olfativas">
            <div><dt>Topo</dt><dd><?= e($p['notas_topo']) ?></dd></div>
            <div><dt>Coração</dt><dd><?= e($p['notas_coracao']) ?></dd></div>
            <div><dt>Fundo</dt><dd><?= e($p['notas_fundo']) ?></dd></div>
        </dl>

        <ul class="ficha">
            <li><?= icone('caixa', 16) ?> <?= (int)$p['peso_g'] ?> g</li>
            <li><?= icone('chama', 16) ?> até <?= (int)$p['queima_h'] ?> horas de queima</li>
            <li><?= icone('folha', 16) ?> cera vegetal de soja</li>
        </ul>

        <?php if ($momentos): ?>
            <div class="momentos-chips" aria-label="Combina com">
                <?php foreach ($momentos as $m): if (!isset(momentos_lista()[$m])) continue; ?>
                    <a class="chip" href="<?= e(url('catalogo', 'index', ['momento' => $m])) ?>"><?= e(momentos_lista()[$m]) ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="produto-abas">
            <div class="abas" role="tablist" aria-label="Mais informações">
                <button type="button" role="tab" class="aba" id="aba-cuidados" aria-controls="painel-cuidados" aria-selected="true" tabindex="0">Cuidados</button>
                <button type="button" role="tab" class="aba" id="aba-entrega" aria-controls="painel-entrega" aria-selected="false" tabindex="-1">Entrega</button>
                <button type="button" role="tab" class="aba" id="aba-pagamento" aria-controls="painel-pagamento" aria-selected="false" tabindex="-1">Pagamento</button>
                <span class="abas-indicador" aria-hidden="true" data-abas-indicador></span>
            </div>
            <div role="tabpanel" id="painel-cuidados" aria-labelledby="aba-cuidados">
                <p>Na primeira vez, deixe acesa até a cera derreter de borda a borda. Corte o pavio em 5 mm antes de cada uso e não deixe acesa por mais de 4 horas seguidas.</p>
                <p>Mantenha longe de cortinas, crianças e animais. Nunca deixe a vela acesa sem ninguém por perto.</p>
            </div>
            <div role="tabpanel" id="painel-entrega" aria-labelledby="aba-entrega" hidden>
                <p>Enviamos para todo o Brasil em até 2 dias úteis após a confirmação do pagamento. Frete fixo de <?= e(moeda(config('frete_fixo'))) ?> e grátis acima de <?= e(moeda(config('frete_gratis_acima'))) ?>.</p>
            </div>
            <div role="tabpanel" id="painel-pagamento" aria-labelledby="aba-pagamento" hidden>
                <p>Cartão de crédito em até <?= (int)config('parcelas_max') ?>x sem juros (parcela mínima de <?= e(moeda(config('parcela_minima'))) ?>), cartão de débito ou PIX com 5% de desconto. A nota fiscal chega no seu e-mail assim que o pagamento é confirmado.</p>
            </div>
        </div>
    </div>
</article>

<?php if ($relacionados): ?>
    <section class="wrap relacionados" aria-labelledby="rel-titulo">
        <h2 id="rel-titulo" data-revelar>Combina com esta vela</h2>
        <div class="grade-cards">
            <?php foreach ($relacionados as $p): ?>
                <?php include RAIZ . '/views/partials/card_produto.php'; ?>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
