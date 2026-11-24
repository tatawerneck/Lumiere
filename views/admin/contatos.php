<?php
// Mensagens enviadas pelo formulário de contato. Recebe $mensagens.
?>
<section class="painel-caixa">
    <h2>Mensagens do site <span class="estrela" aria-hidden="true">✦</span></h2>
    <?php if (!$mensagens): ?>
        <p class="vazio">Nenhuma mensagem recebida.</p>
    <?php else: ?>
        <?php foreach ($mensagens as $m): ?>
            <article class="mensagem <?= $m['lido'] ? '' : 'nova' ?>">
                <div class="mensagem-topo">
                    <div><strong><?= e($m['nome']) ?></strong> <a href="mailto:<?= e($m['email']) ?>?subject=<?= rawurlencode('Re: sua mensagem para a Lumière') ?>"><?= e($m['email']) ?></a></div>
                    <small><?= e(dataBr($m['criado_em'], true)) ?></small>
                </div>
                <p><?= e($m['mensagem']) ?></p>
                <div class="acoes" style="justify-content: flex-start">
                    <a class="bt bt-noite bt-pequeno" href="mailto:<?= e($m['email']) ?>?subject=<?= rawurlencode('Re: sua mensagem para a Lumière') ?>"><?= icone('carta', 16) ?> Responder</a>
                    <?php if (!$m['lido']): ?>
                        <form method="post" action="<?= e(url('contato', 'lido')) ?>">
                            <?= csrf_campo() ?>
                            <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                            <button class="bt bt-linha bt-pequeno" type="submit"><?= icone('check', 16) ?> Marcar como lida</button>
                        </form>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</section>
