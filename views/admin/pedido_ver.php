<?php
// Detalhe do pedido no painel. Recebe $p, $itens, $nota.
$ordem = ['aguardando_pagamento', 'pago', 'enviado', 'entregue'];
$posicao = array_search($p['status'], $ordem, true);
$proximos = [
    'aguardando_pagamento' => ['pago' => 'Confirmar pagamento', 'cancelado' => 'Cancelar pedido'],
    'pago'                 => ['enviado' => 'Marcar como enviado', 'cancelado' => 'Cancelar pedido'],
    'enviado'              => ['entregue' => 'Marcar como entregue'],
    'entregue'             => [],
    'cancelado'            => [],
][$p['status']] ?? [];
?>
<p><a class="bt bt-linha bt-pequeno" href="<?= e(url('vendas')) ?>"><?= icone('seta-esq', 16) ?> Voltar aos pedidos</a></p>

<div class="grade-2">
    <div style="display: grid; gap: 22px">
        <section class="painel-caixa">
            <div class="caixa-topo">
                <h2>Pedido <?= e($p['codigo']) ?></h2>
                <span class="selo-adm s-<?= e($p['status']) ?>"><?= e(status_pedido($p['status'])) ?></span>
            </div>
            <div class="tabela-rolagem">
                <table class="tabela">
                    <thead><tr><th>Vela</th><th class="num">Qtd.</th><th class="num">Unitário</th><th class="num">Total</th></tr></thead>
                    <tbody>
                        <?php foreach ($itens as $it): ?>
                            <tr>
                                <td><?= e($it['nome']) ?></td>
                                <td class="num"><?= (int)$it['quantidade'] ?></td>
                                <td class="num"><?= e(moeda($it['preco_unitario'])) ?></td>
                                <td class="num"><?= e(moeda($it['total'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr><td colspan="3" class="num">Subtotal</td><td class="num"><?= e(moeda($p['subtotal'])) ?></td></tr>
                        <?php if ((float)$p['desconto'] > 0): ?><tr><td colspan="3" class="num">Desconto PIX</td><td class="num">− <?= e(moeda($p['desconto'])) ?></td></tr><?php endif; ?>
                        <tr><td colspan="3" class="num">Frete</td><td class="num"><?= (float)$p['frete'] > 0 ? e(moeda($p['frete'])) : 'Grátis' ?></td></tr>
                        <tr><td colspan="3" class="num"><strong>Total</strong></td><td class="num"><strong><?= e(moeda($p['total'])) ?></strong></td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="painel-caixa">
            <h2>Cliente e entrega</h2>
            <dl class="dados-lista">
                <dt>Nome</dt><dd><?= e($p['nome']) ?></dd>
                <dt>E-mail</dt><dd><a href="mailto:<?= e($p['email']) ?>"><?= e($p['email']) ?></a></dd>
                <dt>CPF</dt><dd><?= e(cpf_formatado($p['cpf'])) ?></dd>
                <dt>Telefone</dt><dd><?= e($p['telefone']) ?></dd>
                <dt>Endereço</dt><dd><?= e($p['endereco'] . ', ' . $p['numero'] . ($p['complemento'] ? ', ' . $p['complemento'] : '')) ?><br><?= e($p['bairro'] . ', ' . $p['cidade'] . ' (' . $p['uf'] . ')') ?><br>CEP <?= e(substr($p['cep'], 0, 5) . '-' . substr($p['cep'], 5)) ?></dd>
                <dt>Pagamento</dt><dd><?= e(forma_pagamento($p['pagamento'])) ?><?= $p['cartao_final'] ? ', ' . e($p['cartao_bandeira']) . ' final ' . e($p['cartao_final']) : '' ?><?= $p['pagamento'] === 'credito' ? ', ' . (int)$p['parcelas'] . 'x' : '' ?></dd>
                <dt>Criado em</dt><dd><?= e(dataBr($p['criado_em'], true)) ?></dd>
                <?php if ($p['pago_em']): ?><dt>Pago em</dt><dd><?= e(dataBr($p['pago_em'], true)) ?></dd><?php endif; ?>
            </dl>
        </section>
    </div>

    <div style="display: grid; gap: 22px">
        <section class="painel-caixa">
            <h2>Andamento</h2>
            <?php if ($p['status'] === 'cancelado'): ?>
                <p class="selo-adm s-cancelado">Pedido cancelado. O estoque foi devolvido.</p>
            <?php else: ?>
                <ol class="linha-tempo">
                    <?php foreach ($ordem as $i => $st): ?>
                        <li class="<?= $posicao !== false && $i <= $posicao ? 'feito' : '' ?>"><?= e(status_pedido($st)) ?></li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
            <?php if ($proximos): ?>
                <div class="form-acoes">
                    <?php foreach ($proximos as $st => $rot): ?>
                        <form method="post" action="<?= e(url('vendas', 'status')) ?>" <?= $st === 'cancelado' ? 'data-confirmar="Cancelar este pedido e devolver o estoque?"' : '' ?>>
                            <?= csrf_campo() ?>
                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                            <input type="hidden" name="status" value="<?= e($st) ?>">
                            <button class="bt <?= $st === 'cancelado' ? 'bt-perigo' : '' ?>" type="submit"><?= e($rot) ?></button>
                        </form>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="painel-caixa">
            <h2>Nota fiscal</h2>
            <?php if (!$nota): ?>
                <p class="vazio">A nota é emitida automaticamente quando o pagamento é confirmado.</p>
            <?php else: ?>
                <dl class="dados-lista">
                    <dt>Número</dt><dd><?= str_pad((string)$nota['numero'], 9, '0', STR_PAD_LEFT) ?>, série <?= (int)$nota['serie'] ?></dd>
                    <dt>Chave</dt><dd class="chave"><?= e(trim(chunk_split($nota['chave_acesso'], 4, ' '))) ?></dd>
                    <dt>Emitida em</dt><dd><?= e(dataBr($nota['emitida_em'], true)) ?></dd>
                    <dt>E-mail</dt><dd><span class="selo-adm <?= $nota['email_enviado'] ? 's-ok' : 's-alerta' ?>"><?= $nota['email_enviado'] ? 'Enviado' : 'Não enviado' ?></span><small style="display: block; margin-top: 4px"><?= e($nota['email_info'] ?? '') ?></small></dd>
                </dl>
                <div class="form-acoes" style="margin-top: 14px">
                    <a class="bt bt-noite bt-pequeno" href="<?= e(url('nota', 'ver', ['codigo' => $p['codigo']])) ?>" target="_blank" rel="noopener"><?= icone('olho', 16) ?> Ver nota</a>
                    <form method="post" action="<?= e(url('vendas', 'reenviarNota')) ?>">
                        <?= csrf_campo() ?>
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        <button class="bt bt-pequeno" type="submit"><?= icone('carta', 16) ?> Reenviar por e-mail</button>
                    </form>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>
