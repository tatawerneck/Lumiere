<?php
// Conteúdo da gaveta do carrinho. Recebe $resumo (Carrinho::resumo()). Também é devolvido via fetch.
$acaoAtualizar = url('carrinho', 'atualizar');
$acaoRemover = url('carrinho', 'remover');
?>
<?php if (!$resumo['itens']): ?>
    <div class="gaveta-vazia">
        <?= icone('chama', 34) ?>
        <p>Seu carrinho está vazio por enquanto.</p>
        <a class="botao botao-linha" href="<?= e(url('catalogo')) ?>">Escolher uma vela</a>
    </div>
<?php else: ?>
    <div class="frete-barra" style="--pct: <?= (int)$resumo['frete_pct'] ?>%">
        <p>
            <?php if ($resumo['falta_frete'] > 0): ?>
                Faltam <strong><?= e(moeda($resumo['falta_frete'])) ?></strong> para o frete grátis.
            <?php else: ?>
                <strong>Frete grátis</strong> garantido neste pedido.
            <?php endif; ?>
        </p>
        <span class="frete-trilho" aria-hidden="true"><span></span></span>
    </div>

    <ul class="gaveta-lista">
        <?php foreach ($resumo['itens'] as $item): $p = $item['produto']; $q = (int)$item['quantidade']; ?>
            <li class="linha-item">
                <a class="linha-foto" href="<?= e(url('catalogo', 'produto', ['id' => (int)$p['id']])) ?>">
                    <img src="<?= e(imagemTamanho(imagemProduto($p), 160, 200)) ?>" alt="" width="80" height="100" loading="lazy">
                </a>
                <div class="linha-info">
                    <p class="linha-familia"><?= e($p['categoria_nome']) ?></p>
                    <a class="linha-nome" href="<?= e(url('catalogo', 'produto', ['id' => (int)$p['id']])) ?>"><?= e($p['nome']) ?></a>
                    <p class="linha-preco"><?= e(moeda($p['preco'])) ?></p>
                    <div class="linha-acoes">
                        <form class="qtd" method="post" action="<?= e($acaoAtualizar) ?>" data-carrinho-form>
                            <?= csrf_campo() ?>
                            <input type="hidden" name="produto_id" value="<?= (int)$p['id'] ?>">
                            <button type="submit" name="quantidade" value="<?= $q - 1 ?>" aria-label="Diminuir quantidade de <?= e($p['nome']) ?>"><?= icone('menos', 16) ?></button>
                            <span aria-label="Quantidade"><?= $q ?></span>
                            <button type="submit" name="quantidade" value="<?= $q + 1 ?>" aria-label="Aumentar quantidade de <?= e($p['nome']) ?>"
                                <?= $q >= (int)$p['estoque'] ? 'disabled' : '' ?>><?= icone('mais', 16) ?></button>
                        </form>
                        <form method="post" action="<?= e($acaoRemover) ?>" data-carrinho-form>
                            <?= csrf_campo() ?>
                            <input type="hidden" name="produto_id" value="<?= (int)$p['id'] ?>">
                            <button class="linha-remover" type="submit" aria-label="Remover <?= e($p['nome']) ?>">Remover</button>
                        </form>
                    </div>
                </div>
                <p class="linha-subtotal"><?= e(moeda($item['subtotal'])) ?></p>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="gaveta-rodape">
        <dl class="resumo-linhas">
            <div><dt>Subtotal</dt><dd><?= e(moeda($resumo['subtotal'])) ?></dd></div>
            <div><dt>Frete</dt><dd><?= $resumo['frete'] > 0 ? e(moeda($resumo['frete'])) : 'Grátis' ?></dd></div>
            <div class="resumo-total"><dt>Total</dt><dd><?= e(moeda($resumo['total'])) ?></dd></div>
        </dl>
        <p class="resumo-pix"><?= icone('pix', 16) ?> <?= e(moeda($resumo['total_pix'])) ?> no PIX ou <?= e(parcelamento($resumo['total'])) ?></p>
        <a class="botao botao-bloco botao-grande" href="<?= e(url('checkout')) ?>">Finalizar compra</a>
        <a class="gaveta-link" href="<?= e(url('carrinho')) ?>">Ver carrinho completo</a>
    </div>
<?php endif; ?>
