<?php
// Clientes cadastrados. Recebe $clientes, $busca e $inscritos (newsletter).
?>
<section class="painel-caixa">
    <div class="caixa-topo">
        <form class="filtros-adm" method="get" action="index.php" style="flex: 1">
            <input type="hidden" name="controller" value="cliente">
            <div class="campo campo-busca"><label for="cl-busca">Buscar cliente</label><input id="cl-busca" type="search" name="busca" value="<?= e($busca) ?>" placeholder="Nome ou e-mail"></div>
            <button class="bt bt-noite" type="submit"><?= icone('busca', 18) ?> Buscar</button>
        </form>
        <span class="selo-adm s-rosa"><?= icone('carta', 14) ?> <?= (int)$inscritos ?> na newsletter</span>
    </div>
</section>

<section class="painel-caixa">
    <h2>Clientes <span class="estrela" aria-hidden="true">✦</span></h2>
    <?php if (!$clientes): ?>
        <p class="vazio">Nenhum cliente cadastrado ainda. Compras sem cadastro aparecem em Pedidos.</p>
    <?php else: ?>
        <div class="tabela-rolagem">
            <table class="tabela">
                <thead><tr><th>Cliente</th><th>Telefone</th><th class="num">Pedidos</th><th class="num">Total comprado</th><th>Desde</th><th>Situação</th></tr></thead>
                <tbody>
                    <?php foreach ($clientes as $c): ?>
                        <tr>
                            <td><strong><?= e($c['nome']) ?></strong><small><?= e($c['email']) ?></small></td>
                            <td><?= e($c['telefone'] ?: '—') ?></td>
                            <td class="num"><?= (int)$c['pedidos'] ?></td>
                            <td class="num"><?= e(moeda($c['gasto'])) ?></td>
                            <td><?= e(dataBr($c['criado_em'])) ?></td>
                            <td><span class="selo-adm <?= $c['ativo'] ? 's-ok' : 's-inativo' ?>"><?= $c['ativo'] ? 'Ativo' : 'Inativo' ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
