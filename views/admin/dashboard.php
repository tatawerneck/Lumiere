<?php
// Dashboard. Recebe $resumo, $serie, $maisVendidos, $pagamentos, $ultimos, $estoqueBaixo.
$u = usuario_logado();
$var = $resumo['mes_anterior'] > 0 ? ($resumo['faturamento'] - $resumo['mes_anterior']) / $resumo['mes_anterior'] * 100 : null;
$dadosSerie = ['rotulos' => array_column($serie, 'dia'), 'valores' => array_column($serie, 'total')];
$dadosPg = ['rotulos' => array_map(fn($r) => forma_pagamento($r['pagamento']), $pagamentos), 'valores' => array_map(fn($r) => (float)$r['total'], $pagamentos)];
$meses = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
?>
<section class="adm-banner" aria-label="Boas-vindas">
    <img src="uploads/fotos/ruiva.jpg" alt="" width="1536" height="1024">
    <span class="brilho" style="left: 22px; top: 40px" aria-hidden="true">✦</span>
    <p>Mais cor,<br>mais brilho,<br><span>mais você.</span></p>
</section>

<div class="adm-boas">
    <div>
        <h2>Bem-vinda, <span><?= e(explode(' ', trim($u['nome']))[0]) ?>!</span></h2>
        <p>Resumo de <?= e($meses[(int)date('n') - 1]) ?> de <?= date('Y') ?>. Os números consideram pedidos pagos, enviados e entregues.</p>
    </div>
</div>

<nav class="atalhos" aria-label="Atalhos">
    <a class="atalho" href="<?= e(url('produto', 'novo')) ?>"><span class="ic-redondo"><?= icone('mais', 20) ?></span> Nova vela</a>
    <a class="atalho" href="<?= e(url('vendas', 'index', ['status' => 'aguardando_pagamento'])) ?>"><span class="ic-redondo"><?= icone('relogio', 20) ?></span> PIX pendentes</a>
    <a class="atalho" href="<?= e(url('entrada')) ?>"><span class="ic-redondo"><?= icone('entrada', 20) ?></span> Repor estoque</a>
    <a class="atalho" href="<?= e(url('relatorio')) ?>"><span class="ic-redondo"><?= icone('grafico', 20) ?></span> Relatórios</a>
</nav>

<section class="kpis" aria-label="Indicadores do mês">
    <div class="kpi">
        <div class="kpi-topo"><span class="ic-redondo rosa"><?= icone('grafico', 20) ?></span> Faturamento</div>
        <div class="kpi-valor"><?= e(moeda($resumo['faturamento'])) ?></div>
        <small>
            <?php if ($var === null): ?>Primeiro mês com vendas
            <?php else: ?><span class="variacao <?= $var >= 0 ? 'sobe' : 'desce' ?>"><?= $var >= 0 ? '+' : '' ?><?= number_format($var, 1, ',', '.') ?>%</span> sobre o mês passado<?php endif; ?>
        </small>
    </div>
    <div class="kpi">
        <div class="kpi-topo"><span class="ic-redondo"><?= icone('carrinho', 20) ?></span> Pedidos pagos</div>
        <div class="kpi-valor"><?= (int)$resumo['pedidos'] ?></div>
        <small>Ticket médio de <?= e(moeda($resumo['ticket'])) ?></small>
    </div>
    <div class="kpi">
        <div class="kpi-topo"><span class="ic-redondo ouro"><?= icone('relogio', 20) ?></span> Aguardando PIX</div>
        <div class="kpi-valor"><?= (int)$resumo['aguardando'] ?></div>
        <small><a href="<?= e(url('vendas', 'index', ['status' => 'aguardando_pagamento'])) ?>">Ver pedidos pendentes</a></small>
    </div>
    <div class="kpi">
        <div class="kpi-topo"><span class="ic-redondo"><?= icone('pessoas', 20) ?></span> Clientes</div>
        <div class="kpi-valor"><?= (int)$resumo['clientes'] ?></div>
        <small><?= (int)$resumo['produtos'] ?> velas à venda</small>
    </div>
</section>

<div class="grade-2">
    <section class="painel-caixa">
        <h2>Vendas dos últimos 30 dias <span class="estrela" aria-hidden="true">✦</span></h2>
        <div class="grafico-caixa">
            <canvas id="grafico-vendas" data-grafico='<?= e(json_encode($dadosSerie)) ?>' aria-label="Gráfico de faturamento por dia" role="img"></canvas>
        </div>
    </section>
    <section class="painel-caixa">
        <h2>Formas de pagamento</h2>
        <?php if (!$pagamentos): ?>
            <p class="vazio">Ainda não há vendas pagas.</p>
        <?php else: ?>
            <div class="grafico-pizza">
                <canvas id="grafico-pagamentos" data-grafico='<?= e(json_encode($dadosPg)) ?>' aria-label="Gráfico de vendas por forma de pagamento" role="img"></canvas>
            </div>
        <?php endif; ?>
    </section>
</div>

<div class="grade-2">
    <section class="painel-caixa">
        <div class="caixa-topo">
            <h2>Últimos pedidos</h2>
            <a class="bt bt-linha bt-pequeno" href="<?= e(url('vendas')) ?>">Ver todos</a>
        </div>
        <?php if (!$ultimos): ?>
            <p class="vazio">Os pedidos da loja aparecem aqui.</p>
        <?php else: ?>
            <div class="tabela-rolagem">
                <table class="tabela">
                    <thead><tr><th>Pedido</th><th>Cliente</th><th>Pagamento</th><th>Status</th><th class="num">Total</th></tr></thead>
                    <tbody>
                        <?php foreach ($ultimos as $p): ?>
                            <tr>
                                <td><a href="<?= e(url('vendas', 'ver', ['id' => (int)$p['id']])) ?>"><strong><?= e($p['codigo']) ?></strong></a><small><?= e(dataBr($p['criado_em'], true)) ?></small></td>
                                <td><?= e($p['nome']) ?></td>
                                <td><?= e(forma_pagamento($p['pagamento'])) ?></td>
                                <td><span class="selo-adm s-<?= e($p['status']) ?>"><?= e(status_pedido($p['status'])) ?></span></td>
                                <td class="num"><?= e(moeda($p['total'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <div style="display: grid; gap: 22px">
        <section class="painel-caixa">
            <h2>Mais vendidas</h2>
            <?php if (!$maisVendidos): ?>
                <p class="vazio">Sem vendas ainda.</p>
            <?php else: ?>
                <ol class="lista-simples">
                    <?php foreach ($maisVendidos as $mv): ?>
                        <li><span><?= e($mv['nome']) ?> <small><?= (int)$mv['qtd'] ?> unidades</small></span><strong><?= e(moeda($mv['total'])) ?></strong></li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </section>
        <section class="painel-caixa">
            <h2>Estoque baixo</h2>
            <?php if (!$estoqueBaixo): ?>
                <p class="vazio">Nenhuma vela com <?= (int)config('estoque_baixo') ?> unidades ou menos.</p>
            <?php else: ?>
                <ul class="lista-simples">
                    <?php foreach ($estoqueBaixo as $eb): ?>
                        <li><span><?= e($eb['nome']) ?></span><span class="selo-adm <?= (int)$eb['estoque'] === 0 ? 's-erro' : 's-alerta' ?>"><?= (int)$eb['estoque'] ?> un.</span></li>
                    <?php endforeach; ?>
                </ul>
                <p style="margin-top: 12px"><a class="bt bt-pequeno" href="<?= e(url('entrada')) ?>">Registrar entrada</a></p>
            <?php endif; ?>
        </section>
    </div>
</div>

<p class="dica"><?= icone('alerta', 20) ?> <span>Pagamentos em modo <strong>simulação</strong>. Para enviar as notas pelo Gmail, configure a senha de app (veja o README). A nota gerada é de demonstração, sem valor fiscal.</span></p>
