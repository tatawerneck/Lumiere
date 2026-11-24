<?php
// Relatório de vendas por período. Recebe $de, $ate e $periodo (resumo + produtos).
$r = $periodo['resumo'];
$pedidos = (int)$r['pedidos'];
$fat = (float)$r['faturamento'];
?>
<section class="painel-caixa nao-imprimir">
    <form class="filtros-adm" method="get" action="index.php">
        <input type="hidden" name="controller" value="relatorio">
        <div class="campo"><label for="r-de">De</label><input id="r-de" type="date" name="de" value="<?= e($de) ?>"></div>
        <div class="campo"><label for="r-ate">Até</label><input id="r-ate" type="date" name="ate" value="<?= e($ate) ?>"></div>
        <button class="bt bt-noite" type="submit">Gerar relatório</button>
        <button class="bt bt-linha" type="button" onclick="window.print()"><?= icone('imprimir', 18) ?> Imprimir</button>
    </form>
</section>

<p>Período de <strong><?= e(dataBr($de)) ?></strong> a <strong><?= e(dataBr($ate)) ?></strong>, considerando pedidos pagos, enviados e entregues.</p>

<section class="kpis">
    <div class="kpi"><div class="kpi-topo"><span class="ic-redondo rosa"><?= icone('grafico', 20) ?></span> Faturamento</div><div class="kpi-valor"><?= e(moeda($fat)) ?></div></div>
    <div class="kpi"><div class="kpi-topo"><span class="ic-redondo"><?= icone('carrinho', 20) ?></span> Pedidos</div><div class="kpi-valor"><?= $pedidos ?></div><small>Ticket médio de <?= e(moeda($pedidos ? $fat / $pedidos : 0)) ?></small></div>
    <div class="kpi"><div class="kpi-topo"><span class="ic-redondo ouro"><?= icone('pix', 20) ?></span> Descontos PIX</div><div class="kpi-valor"><?= e(moeda($r['descontos'])) ?></div></div>
    <div class="kpi"><div class="kpi-topo"><span class="ic-redondo"><?= icone('caminhao', 20) ?></span> Fretes cobrados</div><div class="kpi-valor"><?= e(moeda($r['fretes'])) ?></div></div>
</section>

<section class="painel-caixa">
    <h2>Velas vendidas no período</h2>
    <?php if (!$periodo['produtos']): ?>
        <p class="vazio">Nenhuma venda paga neste período.</p>
    <?php else: ?>
        <div class="tabela-rolagem">
            <table class="tabela">
                <thead><tr><th>Vela</th><th class="num">Unidades</th><th class="num">Receita</th><th class="num">Participação</th></tr></thead>
                <tbody>
                    <?php $somaItens = array_sum(array_map(fn($x) => (float)$x['total'], $periodo['produtos'])); ?>
                    <?php foreach ($periodo['produtos'] as $pr): ?>
                        <tr>
                            <td><?= e($pr['nome']) ?></td>
                            <td class="num"><?= (int)$pr['qtd'] ?></td>
                            <td class="num"><?= e(moeda($pr['total'])) ?></td>
                            <td class="num"><?= $somaItens > 0 ? number_format((float)$pr['total'] / $somaItens * 100, 1, ',', '.') : '0' ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
