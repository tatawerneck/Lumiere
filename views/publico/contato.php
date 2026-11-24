<?php
// Contato. A loja ainda não informou telefone nem endereço, por isso o atendimento é pelo formulário.
?>
<section class="wrap pagina-cabeca">
    <ol class="trilha" aria-label="Você está em">
        <li><a href="index.php">Início</a></li>
        <li aria-current="page">Contato</li>
    </ol>
    <h1 data-revelar>Fale com a Lumière</h1>
    <p>Dúvida sobre um pedido, uma fragrância ou presentes para empresas? Escreva aqui e respondemos pelo e-mail que você informar.</p>
</section>

<div class="wrap contato">
    <div class="contato-info">
        <dl>
            <div><dt>Sobre um pedido</dt><dd>Informe o código do pedido (começa com LU) para agilizar.</dd></div>
            <div><dt>Presentes e eventos</dt><dd>Montamos kits para casamentos, empresas e datas especiais.</dd></div>
            <div><dt>Prazo de resposta</dt><dd>Até 1 dia útil, de segunda a sexta.</dd></div>
        </dl>
    </div>
    <form class="caixa auth-form" method="post" action="<?= e(url('contato', 'enviar')) ?>">
        <?= csrf_campo() ?>
        <div class="campo">
            <label for="c-nome">Nome</label>
            <input id="c-nome" name="nome" value="<?= e(old('nome')) ?>" maxlength="80" required minlength="2" autocomplete="name">
        </div>
        <div class="campo">
            <label for="c-email">E-mail</label>
            <input id="c-email" type="email" name="email" value="<?= e(old('email')) ?>" maxlength="120" required autocomplete="email">
        </div>
        <div class="campo">
            <label for="c-mensagem">Mensagem</label>
            <textarea id="c-mensagem" name="mensagem" rows="6" maxlength="1000" required minlength="5"><?= e(old('mensagem')) ?></textarea>
        </div>
        <button class="botao botao-grande" type="submit">Enviar mensagem</button>
    </form>
</div>
