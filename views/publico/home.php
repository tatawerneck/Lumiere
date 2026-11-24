<?php
// Página inicial. Recebe $destaques, $porMomento, $familias.
$us = fn(string $id, int $w, int $h): string => "https://images.unsplash.com/photo-$id?auto=format&fit=crop&w=$w&h=$h&q=80";
$descMomentos = [
    'relaxar'    => 'Para desacelerar no fim do dia e dormir melhor.',
    'concentrar' => 'Para estudar, trabalhar em casa e manter o foco.',
    'romantico'  => 'Para jantares a dois e noites especiais.',
    'receber'    => 'Para a casa estar perfumada quando a visita chegar.',
    'energizar'  => 'Para manhãs mais leves e ambientes arejados.',
    'aconchego'  => 'Para tardes de chuva, manta e uma xícara de chá.',
];
// Posições de cada letra (unidades da fonte Bodoni Moda) para o letreiro gigante caber na largura da tela.
$letras = [['L', -62], ['U', 1145], ['M', 2597], ['I', 4342], ['E', 5116], ['R', 6353], ['E', 7796]];
?>

<!-- ============ HERO ============ -->
<section class="hero" data-hero aria-labelledby="hero-titulo">
    <svg class="hero-marca" viewBox="0 0 6476 1520" aria-hidden="true" focusable="false" data-hero-marca>
        <defs><clipPath id="hero-clip"><rect x="-200" y="-400" width="7000" height="1920"/></clipPath></defs>
        <g clip-path="url(#hero-clip)">
            <g transform="scale(0.724 1)">
                <?php foreach ($letras as [$l, $x]): ?>
                    <text x="<?= $x ?>" y="1500" class="hero-letra"><?= $l ?></text>
                <?php endforeach; ?>
            </g>
        </g>
    </svg>

    <picture class="hero-vela" data-hero-vela>
        <source srcset="public/assets/img/hero-vela.webp" type="image/webp">
        <img src="public/assets/img/hero-vela.png" alt="Vela Lumière Jardim de Outono em pote de vidro âmbar, acesa, sobre uma pedra e um tecido de linho"
             width="907" height="560" fetchpriority="high" decoding="async">
    </picture>

    <div class="hero-texto" data-hero-texto>
        <p class="hero-sobre">Para Momentos Aconchegantes</p>
        <h1 id="hero-titulo">O calor em cada detalhe</h1>
        <p class="hero-desc">Coleção de fragrâncias em cera vegetal para a sua casa.</p>
        <a class="botao botao-grande" href="<?= e(url('catalogo')) ?>">Ver coleção</a>
    </div>
</section>

<!-- ============ FAIXA CORRENDO ============ -->
<div class="faixa" aria-hidden="true" data-faixa>
    <div class="faixa-trilho">
        <?php for ($r = 0; $r < 2; $r++): ?>
            <span>Cera vegetal de soja</span><?= icone('chama', 18) ?>
            <span>Pavio de algodão</span><?= icone('chama', 18) ?>
            <span>Até 65 horas de queima</span><?= icone('chama', 18) ?>
            <span>Feitas à mão em pequenos lotes</span><?= icone('chama', 18) ?>
            <span>Frete grátis acima de <?= e(moeda(config('frete_gratis_acima'))) ?></span><?= icone('chama', 18) ?>
            <span>5% de desconto no PIX</span><?= icone('chama', 18) ?>
        <?php endfor; ?>
    </div>
</div>

<!-- ============ MAIS AMADOS (carrossel) ============ -->
<section class="secao" aria-labelledby="amados-titulo">
    <div class="wrap">
        <header class="secao-topo">
            <h2 id="amados-titulo" data-revelar>Mais amados</h2>
            <div class="secao-topo-acoes">
                <a class="link-seta" href="<?= e(url('catalogo')) ?>">Ver as 20 velas</a>
                <div class="setas">
                    <button type="button" class="seta" data-carrossel-ant="amados" aria-label="Anteriores"><?= icone('seta-esq', 20) ?></button>
                    <button type="button" class="seta" data-carrossel-prox="amados" aria-label="Próximas"><?= icone('seta', 20) ?></button>
                </div>
            </div>
        </header>
    </div>
    <div class="carrossel" data-carrossel="amados" tabindex="0" aria-label="Velas mais amadas">
        <?php foreach ($destaques as $p): ?>
            <div class="carrossel-item"><?php include RAIZ . '/views/partials/card_produto.php'; ?></div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ============ BLOCOS PROMOCIONAIS ============ -->
<section class="promo wrap" aria-label="Destaques da estação">
    <a class="promo-bloco promo-foto" href="<?= e(url('catalogo', 'index', ['momento' => 'aconchego'])) ?>">
        <span class="promo-midia" data-parallax="12">
            <img src="<?= e($us('1727911584115-d758020192bc', 1100, 1300)) ?>" alt="" width="1100" height="1300" loading="lazy">
        </span>
        <span class="promo-texto">
            <span class="promo-titulo">Outono em cada chama</span>
            <span class="promo-desc">Canela, baunilha e âmbar para dias de manta e chuva fina.</span>
            <span class="botao botao-claro">Escolher aroma</span>
        </span>
    </a>
    <a class="promo-bloco promo-video" href="<?= e(url('catalogo', 'index', ['familia' => 'orientais'])) ?>">
        <span class="promo-midia" data-parallax="8">
            <video autoplay muted loop playsinline preload="metadata" poster="<?= e($us('1759262151253-375c311347c7', 1100, 1300)) ?>" data-video-lazy>
                <source src="https://videos.pexels.com/video-files/5092038/5092038-hd_1920_1080_24fps.mp4" type="video/mp4">
            </video>
        </span>
        <span class="promo-texto promo-texto-baixo">
            <span class="promo-titulo">Noites que pedem âmbar</span>
            <span class="promo-desc">Oud, resinas e especiarias. A família mais intensa da casa.</span>
            <span class="link-seta">Ver orientais</span>
        </span>
    </a>
</section>

<!-- ============ ABAS POR MOMENTO ============ -->
<section class="secao momentos" aria-labelledby="momentos-titulo" data-abas>
    <div class="wrap">
        <header class="secao-topo secao-topo-centro">
            <h2 id="momentos-titulo" data-revelar>Escolha pelo momento</h2>
            <p class="secao-sub">Cada vela foi pensada para uma hora do dia. Comece pelo que você quer sentir.</p>
        </header>

        <div class="abas" role="tablist" aria-label="Momentos">
            <?php $i = 0; foreach (momentos_lista() as $slug => $nome): ?>
                <button type="button" role="tab" class="aba" id="aba-<?= e($slug) ?>" aria-controls="painel-<?= e($slug) ?>"
                        aria-selected="<?= $i === 0 ? 'true' : 'false' ?>" tabindex="<?= $i === 0 ? '0' : '-1' ?>" data-aba><?= e($nome) ?></button>
            <?php $i++; endforeach; ?>
            <span class="abas-indicador" aria-hidden="true" data-abas-indicador></span>
        </div>

        <?php $i = 0; foreach (momentos_lista() as $slug => $nome): ?>
            <div class="painel" role="tabpanel" id="painel-<?= e($slug) ?>" aria-labelledby="aba-<?= e($slug) ?>" <?= $i > 0 ? 'hidden' : '' ?> data-painel>
                <p class="painel-desc"><?= e($descMomentos[$slug]) ?></p>
                <div class="grade-cards">
                    <?php foreach ($porMomento[$slug] as $p): ?>
                        <?php include RAIZ . '/views/partials/card_produto.php'; ?>
                    <?php endforeach; ?>
                </div>
                <p class="painel-mais"><a class="link-seta" href="<?= e(url('catalogo', 'index', ['momento' => $slug])) ?>">Todas as velas para <?= e(mb_strtolower($nome)) ?></a></p>
            </div>
        <?php $i++; endforeach; ?>
    </div>
</section>

<!-- ============ INSPIRAÇÃO: vídeo fixado com texto revelado pela rolagem ============ -->
<section class="inspira" id="inspiracao" aria-labelledby="inspira-titulo" data-inspira>
    <div class="inspira-palco">
        <div class="inspira-video" data-inspira-video>
            <video autoplay muted loop playsinline preload="none" poster="<?= e($us('1759262151253-375c311347c7', 1920, 1080)) ?>" data-video-lazy>
                <source src="https://videos.pexels.com/video-files/4106248/4106248-hd_1920_1080_30fps.mp4" type="video/mp4">
            </video>
        </div>
        <div class="inspira-texto">
            <h2 id="inspira-titulo" class="sr">Inspiração</h2>
            <p class="inspira-frase" data-palavras>Acender uma vela é avisar a casa que o dia acabou. O pavio estala, a cera derrete devagar e o perfume ocupa o espaço que a pressa deixou.</p>
            <a class="botao botao-claro" href="#ritual">Aprenda o ritual</a>
        </div>
    </div>
</section>

<!-- ============ FAMÍLIAS OLFATIVAS ============ -->
<section class="secao" id="familias" aria-labelledby="familias-titulo">
    <div class="wrap">
        <header class="secao-topo">
            <h2 id="familias-titulo" data-revelar>Famílias olfativas</h2>
            <div class="secao-topo-acoes">
                <div class="setas">
                    <button type="button" class="seta" data-carrossel-ant="familias" aria-label="Anteriores"><?= icone('seta-esq', 20) ?></button>
                    <button type="button" class="seta" data-carrossel-prox="familias" aria-label="Próximas"><?= icone('seta', 20) ?></button>
                </div>
            </div>
        </header>
    </div>
    <div class="carrossel carrossel-familias" data-carrossel="familias" tabindex="0" aria-label="Famílias olfativas">
        <?php foreach ($familias as $f): if ((int)$f['total'] === 0) continue; ?>
            <a class="familia" href="<?= e(url('catalogo', 'index', ['familia' => $f['slug']])) ?>">
                <span class="familia-foto"><img src="<?= e(imagemTamanho((string)$f['imagem_url'], 700, 900)) ?>" alt="" width="700" height="900" loading="lazy"></span>
                <span class="familia-nome"><?= e($f['nome']) ?></span>
                <span class="familia-desc"><?= e($f['descricao']) ?></span>
                <span class="familia-qtd"><?= (int)$f['total'] ?> <?= (int)$f['total'] === 1 ? 'vela' : 'velas' ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- ============ RITUAL DE USO ============ -->
<section class="ritual" id="ritual" aria-labelledby="ritual-titulo">
    <div class="wrap ritual-grade">
        <div class="ritual-midia" data-parallax="6">
            <video autoplay muted loop playsinline preload="none" poster="public/assets/video/experiencia-poster.jpg" data-video-lazy>
                <source src="public/assets/video/experiencia.mp4" type="video/mp4">
            </video>
        </div>
        <div class="ritual-texto">
            <h2 id="ritual-titulo" data-revelar>O ritual da primeira chama</h2>
            <p class="secao-sub">Três cuidados simples fazem a vela queimar por igual até o fim e render todas as horas prometidas.</p>
            <ol class="passos">
                <li class="passo" data-passo>
                    <span class="passo-num">1</span>
                    <div>
                        <h3>Primeira queima sem pressa</h3>
                        <p>Deixe acesa até a cera derreter de borda a borda, de 2 a 3 horas. Isso evita que a vela cave um túnel no meio.</p>
                    </div>
                </li>
                <li class="passo" data-passo>
                    <span class="passo-num">2</span>
                    <div>
                        <h3>Pavio sempre aparado</h3>
                        <p>Antes de cada uso, corte o pavio em 5 mm. A chama fica baixa, estável e não solta fuligem no vidro.</p>
                    </div>
                </li>
                <li class="passo" data-passo>
                    <span class="passo-num">3</span>
                    <div>
                        <h3>No máximo 4 horas por vez</h3>
                        <p>Apague com um abafador, longe de correntes de ar, e espere a cera esfriar antes de acender de novo.</p>
                    </div>
                </li>
            </ol>
        </div>
    </div>
</section>

<!-- ============ GALERIA DA MARCA ============ -->
<section class="galeria" aria-labelledby="galeria-titulo">
    <div class="wrap">
        <header class="secao-topo secao-topo-centro">
            <h2 id="galeria-titulo" data-revelar>Na casa de quem acende Lumière</h2>
        </header>
        <div class="galeria-grade">
            <figure class="g1" data-parallax="-6"><img src="uploads/fotos/mulheres.jpg" alt="Cinco amigas segurando velas Lumière" width="1536" height="1024" loading="lazy"></figure>
            <figure class="g2" data-parallax="10"><img src="uploads/fotos/ruiva.jpg" alt="Mulher ruiva sentindo o aroma de uma vela Lumière" width="1536" height="1024" loading="lazy"></figure>
            <figure class="g3" data-parallax="4"><img src="uploads/fotos/velas.jpg" alt="Velas Lumière acesas num jardim ao entardecer" width="1536" height="1024" loading="lazy"></figure>
            <figure class="g4" data-parallax="-8"><img src="uploads/fotos/morena.jpg" alt="Mulher de cabelos cacheados com uma vela Lumière acesa" width="1374" height="1145" loading="lazy"></figure>
            <figure class="g5" data-parallax="12"><img src="uploads/fotos/loira.jpg" alt="Mulher loira segurando uma vela Lumière" width="1536" height="1024" loading="lazy"></figure>
        </div>
    </div>
</section>

<!-- ============ BENEFÍCIOS ============ -->
<section class="beneficios" aria-label="Vantagens de comprar na Lumière">
    <ul class="wrap beneficios-lista">
        <li><?= icone('caminhao', 28) ?><div><strong>Frete grátis</strong><span>em compras acima de <?= e(moeda(config('frete_gratis_acima'))) ?></span></div></li>
        <li><?= icone('pix', 28) ?><div><strong>5% de desconto</strong><span>pagando com PIX</span></div></li>
        <li><?= icone('cartao', 28) ?><div><strong>Até 6x sem juros</strong><span>no cartão de crédito</span></div></li>
        <li><?= icone('nota', 28) ?><div><strong>Nota fiscal no e-mail</strong><span>logo depois da compra</span></div></li>
    </ul>
</section>
