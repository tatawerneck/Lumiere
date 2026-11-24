<?php
// Área do cliente: pedidos e notas fiscais. Recebe $pedidos.
$u = usuario_logado();
?>
<section class="wrap pagina-cabeca">
    <ol class="trilha" aria-label="Você está em">
        <li><a href="index.php">Início</a></li>
        <li aria-current="page">Meus pedidos</li>
    </ol>
    <h1>Olá, <?= e(explode(' ', trim($u['nome']))[0]) ?></h1>
    <p>Aqui ficam seus pedidos, o status de cada um e as notas fiscais.</p>
    <div style="margin-top: 16px">
        <form method="post" action="<?= e(url('auth', 'sair')) ?>">
            <?= csrf_campo() ?>
            <button class="botao botao-linha botao-pequeno" type="submit"><?= icone('sair', 16) ?> Sair da conta</button>
        </form>
    </div>
</section>

<div class="wrap lista-pedidos">
    <?php if (!$pedidos): ?>
        <div class="vazio-catalogo">
            <?= icone('sacola', 40) ?>
            <h2>Você ainda não fez pedidos</h2>
            <p>Quando comprar, o pedido e a nota fiscal aparecem aqui.</p>
            <a class="botao" href="<?= e(url('catalogo')) ?>">Escolher uma vela</a>
        </div>
    <?php else: ?>
        <?php foreach ($pedidos as $p): ?>
            <article class="pedido-linha">
                <div>
                    <strong><?= e($p['codigo']) ?></strong>
                    <small><?= e(dataBr($p['criado_em'], true)) ?></small>
                </div>
                <div>
                    <span class="confirma-selo status-<?= e($p['status']) ?>"><?= e(status_pedido($p['status'])) ?></span>
                </div>
                <div>
                    <?= e(moeda($p['total'])) ?>
                    <small><?= e(forma_pagamento($p['pagamento'])) ?></small>
                </div>
                <div style="display: flex; gap: 8px; flex-wrap: wrap">
                    <a class="botao botao-linha botao-pequeno" href="<?= e(url('pedido', 'confirmacao', ['codigo' => $p['codigo']])) ?>">Detalhes</a>
                    <?php if (!empty($p['nota_numero'])): ?>
                        <a class="botao botao-pequeno" href="<?= e(url('nota', 'ver', ['codigo' => $p['codigo']])) ?>" target="_blank" rel="noopener"><?= icone('nota', 16) ?> Nota fiscal</a>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
