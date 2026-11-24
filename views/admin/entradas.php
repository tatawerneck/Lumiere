<?php
// Entradas de estoque. Recebe $entradas e $produtos.
?>
<div class="grade-2">
    <section class="painel-caixa">
        <h2>Últimas entradas <span class="estrela" aria-hidden="true">✦</span></h2>
        <?php if (!$entradas): ?>
            <p class="vazio">Nenhuma entrada registrada.</p>
        <?php else: ?>
            <div class="tabela-rolagem">
                <table class="tabela">
                    <thead><tr><th>Data</th><th>Vela</th><th class="num">Quantidade</th><th class="num">Custo unit.</th><th>Observação</th></tr></thead>
                    <tbody>
                        <?php foreach ($entradas as $en): ?>
                            <tr>
                                <td><?= e(dataBr($en['data'])) ?></td>
                                <td><?= e($en['produto_nome']) ?></td>
                                <td class="num">+<?= (int)$en['quantidade'] ?></td>
                                <td class="num"><?= $en['custo_unitario'] !== null ? e(moeda($en['custo_unitario'])) : '—' ?></td>
                                <td><small><?= e($en['observacao'] ?? '') ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="painel-caixa">
        <h2>Registrar entrada</h2>
        <form class="form-grade" method="post" action="<?= e(url('entrada', 'salvar')) ?>">
            <?= csrf_campo() ?>
            <div class="campo inteira">
                <label for="en-prod">Vela</label>
                <select id="en-prod" name="produto_id" required>
                    <option value="">Escolha</option>
                    <?php foreach ($produtos as $p): ?>
                        <option value="<?= (int)$p['id'] ?>"><?= e($p['nome']) ?> (estoque: <?= (int)$p['estoque'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="campo"><label for="en-qtd">Quantidade</label><input id="en-qtd" type="number" name="quantidade" min="1" max="10000" required></div>
            <div class="campo"><label for="en-custo">Custo unitário (R$)</label><input id="en-custo" name="custo_unitario" inputmode="decimal" placeholder="Opcional"></div>
            <div class="campo"><label for="en-data">Data</label><input id="en-data" type="date" name="data" value="<?= date('Y-m-d') ?>"></div>
            <div class="campo"><label for="en-obs">Observação</label><input id="en-obs" name="observacao" maxlength="255" placeholder="Ex.: lote de outubro"></div>
            <div class="inteira"><button class="bt" type="submit"><?= icone('entrada', 18) ?> Somar ao estoque</button></div>
        </form>
    </section>
</div>
