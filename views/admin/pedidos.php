<?php
// Lista de pedidos com filtros. Recebe $filtros, $pedidos, $contagem.
$statusLista = ['' => 'Todos', 'aguardando_pagamento' => 'Aguardando', 'pago' => 'Pagos', 'enviado' => 'Enviados', 'entregue' => 'Entregues', 'cancelado' => 'Cancelados'];
$totalGeral = array_sum($contagem);
?>
<section class="painel-caixa">
    <div class="caixa-topo">
        <div class="chips-status" role="navigation" aria-label="Filtrar por status">
            <?php foreach ($statusLista as $st => $rot): ?>
                <a class="chip-adm <?= $filtros['status'] === $st ? 'ativo' : '' ?>" href="<?= e(url('vendas', 'index', array_filter(['status' => $st, 'busca' => $filtros['busca']]))) ?>">
                    <?= e($rot) ?> <b><?= $st === '' ? $totalGeral : (int)($contagem[$st] ?? 0) ?></b>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <form class="filtros-adm" method="get" action="index.php">
        <input type="hidden" name="controller" value="vendas">
        <?php if ($filtros['status']): ?><input type="hidden" name="status" value="<?= e($filtros['status']) ?>"><?php endif; ?>
        <div class="campo campo-busca">
            <label for="f-busca">Buscar</label>
            <input id="f-busca" type="search" name="busca" value="<?= e($filtros['busca']) ?>" placeholder="Código, nome ou e-mail">
        </div>
        <div class="campo"><label for="f-de">De</label><input id="f-de" type="date" name="de" value="<?= e($filtros['de']) ?>"></div>
        <div class="campo"><label for="f-ate">Até</label><input id="f-ate" type="date" name="ate" value="<?= e($filtros['ate']) ?>"></div>
        <button class="bt bt-noite" type="submit"><?= icone('busca', 18) ?> Filtrar</button>
        <?php if ($filtros['busca'] || $filtros['de'] || $filtros['ate']): ?><a class="bt bt-linha" href="<?= e(url('vendas')) ?>">Limpar</a><?php endif; ?>
    </form>
</section>

<section class="painel-caixa">
    <h2>Pedidos <span class="estrela" aria-hidden="true">✦</span></h2>
    <?php if (!$pedidos): ?>
        <p class="vazio">Nenhum pedido com esses filtros.</p>
    <?php else: ?>
        <div class="tabela-rolagem">
            <table class="tabela">
                <thead>
                    <tr><th>Pedido</th><th>Cliente</th><th>Itens</th><th>Pagamento</th><th>Status</th><th>Nota fiscal</th><th class="num">Total</th><th><span class="sr">Ações</span></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $p): ?>
                        <tr>
                            <td><strong><?= e($p['codigo']) ?></strong><small><?= e(dataBr($p['criado_em'], true)) ?></small></td>
                            <td><?= e($p['nome']) ?><small><?= e($p['email']) ?></small></td>
                            <td><?= (int)$p['itens'] ?></td>
                            <td><?= e(forma_pagamento($p['pagamento'])) ?><?php if ($p['cartao_final']): ?><small><?= e($p['cartao_bandeira']) ?> final <?= e($p['cartao_final']) ?></small><?php endif; ?></td>
                            <td><span class="selo-adm s-<?= e($p['status']) ?>"><?= e(status_pedido($p['status'])) ?></span></td>
                            <td>
                                <?php if ($p['nota_numero']): ?>
                                    nº <?= (int)$p['nota_numero'] ?>
                                    <small><?= $p['email_enviado'] ? 'enviada por e-mail' : 'e-mail pendente' ?></small>
                                <?php else: ?><small>—</small><?php endif; ?>
                            </td>
                            <td class="num"><strong><?= e(moeda($p['total'])) ?></strong></td>
                            <td><div class="acoes"><a class="bt bt-linha bt-pequeno" href="<?= e(url('vendas', 'ver', ['id' => (int)$p['id']])) ?>">Abrir</a></div></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
