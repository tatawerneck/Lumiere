<?php
// Lista de velas. Recebe $produtos, $busca, $status.
$filtrosStatus = ['todos' => 'Todas', 'ativos' => 'À venda', 'inativos' => 'Ocultas', 'estoque_baixo' => 'Estoque baixo'];
?>
<section class="painel-caixa">
    <div class="caixa-topo">
        <div class="chips-status" role="navigation" aria-label="Filtrar">
            <?php foreach ($filtrosStatus as $st => $rot): ?>
                <a class="chip-adm <?= $status === $st ? 'ativo' : '' ?>" href="<?= e(url('produto', 'index', array_filter(['status' => $st === 'todos' ? '' : $st, 'busca' => $busca]))) ?>"><?= e($rot) ?></a>
            <?php endforeach; ?>
        </div>
        <a class="bt" href="<?= e(url('produto', 'novo')) ?>"><?= icone('mais', 18) ?> Nova vela</a>
    </div>
    <form class="filtros-adm" method="get" action="index.php">
        <input type="hidden" name="controller" value="produto">
        <?php if ($status !== 'todos'): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
        <div class="campo campo-busca"><label for="p-busca">Buscar vela</label><input id="p-busca" type="search" name="busca" value="<?= e($busca) ?>" placeholder="Nome ou família"></div>
        <button class="bt bt-noite" type="submit"><?= icone('busca', 18) ?> Buscar</button>
    </form>
</section>

<section class="painel-caixa">
    <h2>Lista de produtos <span class="estrela" aria-hidden="true">✦</span></h2>
    <?php if (!$produtos): ?>
        <p class="vazio">Nenhuma vela encontrada.</p>
    <?php else: ?>
        <div class="tabela-rolagem">
            <table class="tabela">
                <thead><tr><th>Foto</th><th>Nome</th><th>Família</th><th>Notas</th><th class="num">Preço</th><th class="num">Estoque</th><th>Situação</th><th><span class="sr">Ações</span></th></tr></thead>
                <tbody>
                    <?php foreach ($produtos as $p): $baixo = (int)$p['estoque'] <= (int)config('estoque_baixo'); ?>
                        <tr>
                            <td><img class="mini" src="<?= e(imagemTamanho(imagemProduto($p), 104, 128)) ?>" alt="" width="52" height="64" loading="lazy"></td>
                            <td><strong><?= e($p['nome']) ?></strong><small>#<?= (int)$p['id'] ?><?= $p['destaque'] ? ', destaque' : '' ?><?= $p['novo'] ? ', novidade' : '' ?></small></td>
                            <td><?= e($p['categoria_nome']) ?></td>
                            <td><small><?= e(trim(($p['notas_topo'] ?? '') . ', ' . ($p['notas_coracao'] ?? ''), ', ')) ?></small></td>
                            <td class="num"><?= e(moeda($p['preco'])) ?><?php if ($p['preco_antigo']): ?><small><s><?= e(moeda($p['preco_antigo'])) ?></s></small><?php endif; ?></td>
                            <td class="num"><span class="selo-adm <?= (int)$p['estoque'] === 0 ? 's-erro' : ($baixo ? 's-alerta' : 's-ok') ?>"><?= (int)$p['estoque'] ?></span></td>
                            <td><span class="selo-adm <?= $p['ativo'] ? 's-ok' : 's-inativo' ?>"><?= $p['ativo'] ? 'À venda' : 'Oculta' ?></span></td>
                            <td>
                                <div class="acoes">
                                    <a class="bt bt-linha bt-pequeno" href="<?= e(url('produto', 'editar', ['id' => (int)$p['id']])) ?>"><?= icone('editar', 16) ?> Editar</a>
                                    <form method="post" action="<?= e(url('produto', 'alternar')) ?>">
                                        <?= csrf_campo() ?>
                                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                        <input type="hidden" name="ativo" value="<?= $p['ativo'] ? '0' : '1' ?>">
                                        <button class="bt bt-linha bt-pequeno" type="submit"><?= $p['ativo'] ? 'Ocultar' : 'Publicar' ?></button>
                                    </form>
                                    <form method="post" action="<?= e(url('produto', 'excluir')) ?>" data-confirmar="Excluir a vela <?= e($p['nome']) ?>? Esta ação não pode ser desfeita.">
                                        <?= csrf_campo() ?>
                                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                                        <button class="bt bt-perigo bt-pequeno bt-icone" type="submit" aria-label="Excluir <?= e($p['nome']) ?>"><?= icone('lixo', 16) ?></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    <p class="dica" style="margin-top: 16px"><?= icone('alerta', 18) ?> Velas que já foram vendidas não podem ser excluídas, só ocultadas. Assim o histórico de pedidos e notas continua correto.</p>
</section>
