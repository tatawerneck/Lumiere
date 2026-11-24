<?php
// Página completa do carrinho. Recebe $resumo.
$classeCorpo = 'pagina-carrinho';
?>
<section class="wrap pagina-cabeca">
    <ol class="trilha" aria-label="Você está em">
        <li><a href="index.php">Início</a></li>
        <li aria-current="page">Carrinho</li>
    </ol>
    <h1>Seu carrinho</h1>
</section>

<?php if (!$resumo['itens']): ?>
    <section class="wrap vazio-catalogo" style="padding-bottom: 120px">
        <?= icone('chama', 40) ?>
        <h2>O carrinho está vazio</h2>
        <p>Escolha uma fragrância e ela aparece aqui na hora.</p>
        <a class="botao" href="<?= e(url('catalogo')) ?>">Ver as velas</a>
    </section>
<?php else: ?>
    <div class="wrap carrinho-pagina">
        <div>
            <ul class="gaveta-lista">
                <?php foreach ($resumo['itens'] as $item): $p = $item['produto']; $q = (int)$item['quantidade']; ?>
                    <li class="linha-item">
                        <a class="linha-foto" href="<?= e(url('catalogo', 'produto', ['id' => (int)$p['id']])) ?>">
                            <img src="<?= e(imagemTamanho(imagemProduto($p), 200, 250)) ?>" alt="" width="100" height="125">
                        </a>
                        <div class="linha-info">
                            <p class="linha-familia"><?= e($p['categoria_nome']) ?></p>
                            <a class="linha-nome" href="<?= e(url('catalogo', 'produto', ['id' => (int)$p['id']])) ?>"><?= e($p['nome']) ?></a>
                            <p class="linha-preco"><?= e(moeda($p['preco'])) ?> cada<?= (int)$p['estoque'] <= (int)config('estoque_baixo') ? ', últimas ' . (int)$p['estoque'] . ' unidades' : '' ?></p>
                            <div class="linha-acoes">
                                <form class="qtd" method="post" action="<?= e(url('carrinho', 'atualizar')) ?>" data-carrinho-form>
                                    <?= csrf_campo() ?>
                                    <input type="hidden" name="produto_id" value="<?= (int)$p['id'] ?>">
                                    <button type="submit" name="quantidade" value="<?= $q - 1 ?>" aria-label="Diminuir quantidade de <?= e($p['nome']) ?>"><?= icone('menos', 16) ?></button>
                                    <span><?= $q ?></span>
                                    <button type="submit" name="quantidade" value="<?= $q + 1 ?>" aria-label="Aumentar quantidade de <?= e($p['nome']) ?>" <?= $q >= (int)$p['estoque'] ? 'disabled' : '' ?>><?= icone('mais', 16) ?></button>
                                </form>
                                <form method="post" action="<?= e(url('carrinho', 'remover')) ?>" data-carrinho-form>
                                    <?= csrf_campo() ?>
                                    <input type="hidden" name="produto_id" value="<?= (int)$p['id'] ?>">
                                    <button class="linha-remover" type="submit">Remover</button>
                                </form>
                            </div>
                        </div>
                        <p class="linha-subtotal"><?= e(moeda($item['subtotal'])) ?></p>
                    </li>
                <?php endforeach; ?>
            </ul>
            <p style="margin-top: 24px"><a class="link-seta" href="<?= e(url('catalogo')) ?>">Continuar comprando</a></p>
        </div>

        <aside class="caixa caixa-sticky" aria-labelledby="resumo-titulo">
            <h2 id="resumo-titulo">Resumo</h2>
            <div class="frete-barra" style="--pct: <?= (int)$resumo['frete_pct'] ?>%; padding: 0">
                <p>
                    <?php if ($resumo['falta_frete'] > 0): ?>
                        Faltam <strong><?= e(moeda($resumo['falta_frete'])) ?></strong> para o frete grátis.
                    <?php else: ?>
                        <strong>Frete grátis</strong> garantido.
                    <?php endif; ?>
                </p>
                <span class="frete-trilho" aria-hidden="true"><span></span></span>
            </div>
            <dl class="resumo-linhas">
                <div><dt><?= (int)$resumo['quantidade'] ?> <?= (int)$resumo['quantidade'] === 1 ? 'vela' : 'velas' ?></dt><dd><?= e(moeda($resumo['subtotal'])) ?></dd></div>
                <div><dt>Frete</dt><dd><?= $resumo['frete'] > 0 ? e(moeda($resumo['frete'])) : 'Grátis' ?></dd></div>
                <div class="resumo-total"><dt>Total</dt><dd><?= e(moeda($resumo['total'])) ?></dd></div>
            </dl>
            <p class="resumo-pix"><?= icone('pix', 16) ?> <?= e(moeda($resumo['total_pix'])) ?> no PIX ou <?= e(parcelamento($resumo['total'])) ?></p>
            <a class="botao botao-bloco botao-grande" href="<?= e(url('checkout')) ?>">Finalizar compra</a>
            <ul class="selos-confianca">
                <li><?= icone('escudo', 18) ?> Dados do cartão não ficam guardados na loja</li>
                <li><?= icone('nota', 18) ?> Nota fiscal enviada para o seu e-mail</li>
                <li><?= icone('caminhao', 18) ?> Envio em até 2 dias úteis</li>
            </ul>
        </aside>
    </div>
<?php endif; ?>
