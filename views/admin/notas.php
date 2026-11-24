<?php
// Notas fiscais emitidas. Recebe $notas e $smtpOk.
?>
<?php if (!$smtpOk): ?>
    <div class="dica"><?= icone('carta', 20) ?>
        <span>O envio pelo Gmail ainda não está ligado: as notas são geradas e uma cópia do e-mail fica em <code>storage/emails/</code>.
        Para enviar de verdade, crie uma <strong>senha de app</strong> na conta Google da loja e coloque em <code>smtp_senha</code> no arquivo <code>config/config.php</code> (passo a passo no README).</span>
    </div>
<?php endif; ?>

<section class="painel-caixa">
    <h2>Notas fiscais emitidas <span class="estrela" aria-hidden="true">✦</span></h2>
    <?php if (!$notas): ?>
        <p class="vazio">A primeira nota aparece aqui assim que um pedido for pago.</p>
    <?php else: ?>
        <div class="tabela-rolagem">
            <table class="tabela">
                <thead><tr><th>Número</th><th>Pedido</th><th>Cliente</th><th>Emitida em</th><th>E-mail</th><th class="num">Valor</th><th><span class="sr">Ações</span></th></tr></thead>
                <tbody>
                    <?php foreach ($notas as $n): ?>
                        <tr>
                            <td><strong><?= str_pad((string)$n['numero'], 9, '0', STR_PAD_LEFT) ?></strong><small>Série <?= (int)$n['serie'] ?></small></td>
                            <td><a href="<?= e(url('vendas', 'ver', ['id' => (int)$n['pedido_id']])) ?>"><?= e($n['codigo']) ?></a></td>
                            <td><?= e($n['nome']) ?><small><?= e($n['email']) ?></small></td>
                            <td><?= e(dataBr($n['emitida_em'], true)) ?></td>
                            <td><span class="selo-adm <?= $n['email_enviado'] ? 's-ok' : 's-alerta' ?>"><?= $n['email_enviado'] ? 'Enviado' : 'Pendente' ?></span></td>
                            <td class="num"><?= e(moeda($n['valor_total'])) ?></td>
                            <td><div class="acoes"><a class="bt bt-linha bt-pequeno" href="<?= e(url('nota', 'ver', ['codigo' => $n['codigo']])) ?>" target="_blank" rel="noopener">Ver</a></div></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
