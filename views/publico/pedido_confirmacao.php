<?php
// Depois da compra. Recebe $pedido, $itens, $nota (ou null) e $pix (payload copia e cola ou null).
$codigo = $pedido['codigo'];
$primeiroNome = explode(' ', trim($pedido['nome']))[0];
$status = $pedido['status'];
$enviouEmail = $_SESSION['envio_nota'][$codigo] ?? ($nota ? (bool)$nota['email_enviado'] : null);
$numeroNota = $nota ? str_pad((string)$nota['numero'], 9, '0', STR_PAD_LEFT) : '';
$numeroNota = $nota ? substr($numeroNota, 0, 3) . '.' . substr($numeroNota, 3, 3) . '.' . substr($numeroNota, 6, 3) : '';
?>
<section class="wrap pagina-cabeca">
    <ol class="trilha" aria-label="Você está em">
        <li><a href="index.php">Início</a></li>
        <li aria-current="page">Pedido <?= e($codigo) ?></li>
    </ol>
</section>

<div class="wrap confirma">
    <div>
        <?php if ($status === 'aguardando_pagamento' && $pix): ?>
            <span class="confirma-selo status-aguardando_pagamento"><?= icone('relogio', 16) ?> Aguardando pagamento</span>
            <h1 class="confirma-titulo">Falta só pagar o PIX</h1>
            <p class="secao-sub">Abra o app do seu banco, escolha pagar com PIX e leia o QR Code ou cole o código. O valor é <strong><?= e(moeda($pedido['total'])) ?></strong>, já com 5% de desconto.</p>

            <div class="caixa" style="margin-top: 28px" data-pix data-codigo="<?= e($codigo) ?>" data-status-url="<?= e(url('pedido', 'status', ['codigo' => $codigo])) ?>">
                <div class="pix-caixa">
                    <div class="pix-qr" data-pix-qr data-payload="<?= e($pix) ?>" role="img" aria-label="QR Code do PIX"></div>
                    <div>
                        <label class="rotulo" for="pix-copia">PIX copia e cola</label>
                        <div class="pix-copia">
                            <input id="pix-copia" value="<?= e($pix) ?>" readonly data-pix-texto>
                            <button class="botao botao-pequeno" type="button" data-copiar><?= icone('copiar', 16) ?> <span>Copiar</span></button>
                        </div>
                        <p class="pix-aguardando"><span class="pulso" aria-hidden="true"></span> Esperando a confirmação do banco. Esta página atualiza sozinha.</p>
                        <?php if (config('pagamento_modo') === 'simulacao'): ?>
                            <form method="post" action="<?= e(url('pedido', 'simularPix')) ?>" style="margin-top: 18px">
                                <?= csrf_campo() ?>
                                <input type="hidden" name="codigo" value="<?= e($codigo) ?>">
                                <button class="botao botao-linha" type="submit">Simular pagamento do PIX</button>
                                <p class="campo-ajuda" style="margin-top: 6px">Botão de demonstração: confirma o pagamento sem usar um banco de verdade.</p>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        <?php elseif ($status === 'cancelado'): ?>
            <span class="confirma-selo status-cancelado">Cancelado</span>
            <h1 class="confirma-titulo">Este pedido foi cancelado</h1>
            <p class="secao-sub">Se foi engano, <a href="<?= e(url('contato', 'form')) ?>">fale com a gente</a> informando o código <?= e($codigo) ?>.</p>

        <?php else: ?>
            <div class="check-anim" aria-hidden="true"><?= icone('check', 36) ?></div>
            <span class="confirma-selo status-<?= e($status) ?>"><?= e(status_pedido($status)) ?></span>
            <h1 class="confirma-titulo">Pedido confirmado, <?= e($primeiroNome) ?></h1>
            <p class="secao-sub">Seu pedido <strong><?= e($codigo) ?></strong> foi confirmado. Separamos as velas e enviamos em até 2 dias úteis.</p>

            <?php if ($nota): ?>
                <div class="caixa nota-caixa" style="margin-top: 28px">
                    <?= icone('nota', 30) ?>
                    <div>
                        <h2 style="font-size: 1.5rem; margin-bottom: 6px">Nota fiscal nº <?= e($numeroNota) ?></h2>
                        <?php if ($enviouEmail): ?>
                            <p>Enviamos a nota para <strong><?= e($pedido['email']) ?></strong>. Se não aparecer na caixa de entrada, olhe também em Promoções ou Spam.</p>
                        <?php else: ?>
                            <p>A nota foi gerada e fica disponível aqui e em “Meus pedidos”. O envio automático para <strong><?= e($pedido['email']) ?></strong> acontece assim que o e-mail da loja estiver configurado.</p>
                        <?php endif; ?>
                        <div class="nota-acoes">
                            <a class="botao botao-pequeno" href="<?= e(url('nota', 'ver', ['codigo' => $codigo])) ?>" target="_blank" rel="noopener"><?= icone('olho', 16) ?> Ver nota fiscal</a>
                            <a class="botao botao-linha botao-pequeno" href="<?= e(url('nota', 'ver', ['codigo' => $codigo, 'imprimir' => 1])) ?>" target="_blank" rel="noopener"><?= icone('imprimir', 16) ?> Imprimir ou salvar PDF</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="caixa" style="margin-top: 18px">
            <h2 style="font-size: 1.4rem; margin-bottom: 10px">Entrega</h2>
            <div class="dados-entrega">
                <p><?= e($pedido['nome']) ?></p>
                <p><?= e($pedido['endereco']) ?>, <?= e($pedido['numero']) ?><?= $pedido['complemento'] ? ', ' . e($pedido['complemento']) : '' ?></p>
                <p><?= e($pedido['bairro']) ?>, <?= e($pedido['cidade']) ?> (<?= e($pedido['uf']) ?>)</p>
                <p>CEP <?= e(substr($pedido['cep'], 0, 5) . '-' . substr($pedido['cep'], 5)) ?></p>
            </div>
        </div>

        <p style="margin-top: 26px; display: flex; gap: 22px; flex-wrap: wrap">
            <a class="link-seta" href="<?= e(url('catalogo')) ?>">Continuar comprando</a>
            <?php if (usuario_logado()): ?><a class="link-seta" href="<?= e(url('pedido', 'meus')) ?>">Ver meus pedidos</a><?php endif; ?>
        </p>
    </div>

    <aside class="caixa caixa-sticky resumo-pedido" aria-labelledby="conf-resumo">
        <h2 id="conf-resumo">Pedido <?= e($codigo) ?></h2>
        <ul class="gaveta-lista">
            <?php foreach ($itens as $it): ?>
                <li class="linha-item">
                    <span class="linha-foto"><img src="<?= e(imagemTamanho(imagemProduto(['id' => $it['produto_id'], 'imagem_ext' => $it['imagem_ext'], 'imagem_url' => $it['imagem_url']]), 120, 150)) ?>" alt="" width="60" height="75"></span>
                    <div>
                        <p class="linha-nome"><?= e($it['nome']) ?></p>
                        <p class="qtd-mini"><?= (int)$it['quantidade'] ?> × <?= e(moeda($it['preco_unitario'])) ?></p>
                    </div>
                    <p class="linha-subtotal"><?= e(moeda($it['total'])) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
        <dl class="resumo-linhas">
            <div><dt>Subtotal</dt><dd><?= e(moeda($pedido['subtotal'])) ?></dd></div>
            <?php if ((float)$pedido['desconto'] > 0): ?><div><dt>Desconto PIX</dt><dd>− <?= e(moeda($pedido['desconto'])) ?></dd></div><?php endif; ?>
            <div><dt>Frete</dt><dd><?= (float)$pedido['frete'] > 0 ? e(moeda($pedido['frete'])) : 'Grátis' ?></dd></div>
            <div class="resumo-total"><dt>Total</dt><dd><?= e(moeda($pedido['total'])) ?></dd></div>
        </dl>
        <?php
        $pagto = forma_pagamento($pedido['pagamento']);
        if ($pedido['cartao_final']) { $pagto .= ', ' . $pedido['cartao_bandeira'] . ' final ' . $pedido['cartao_final']; }
        if ($pedido['pagamento'] === 'credito') { $pagto .= ', ' . (int)$pedido['parcelas'] . 'x de ' . moeda($pedido['total'] / max(1, (int)$pedido['parcelas'])); }
        ?>
        <p class="campo-ajuda"><?= e($pagto) ?>
        </p>
    </aside>
</div>
