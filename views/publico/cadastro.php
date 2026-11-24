<?php
// Criar conta de cliente.
$classeCorpo = 'pagina-auth';
?>
<section class="auth" aria-labelledby="titulo-cadastro">
    <div class="auth-foto">
        <img src="uploads/fotos/branquinha.jpg" alt="" width="1374" height="1145">
        <p>Guarde seus pedidos e notas fiscais num só lugar.</p>
    </div>
    <div class="auth-lado">
        <div class="auth-caixa">
            <h1 id="titulo-cadastro">Criar conta</h1>
            <p>Leva menos de um minuto. Depois é só acompanhar seus pedidos por aqui.</p>

            <form class="auth-form" method="post" action="<?= e(url('auth', 'cadastrar')) ?>" data-auth-form data-cadastro novalidate>
                <?= csrf_campo() ?>
                <div class="campo">
                    <label for="cad-nome">Nome completo</label>
                    <input id="cad-nome" name="nome" autocomplete="name" maxlength="80" value="<?= e(old('nome')) ?>" required>
                </div>
                <div class="campo">
                    <label for="cad-email">E-mail</label>
                    <input id="cad-email" type="email" name="email" autocomplete="email" maxlength="120" value="<?= e(old('email')) ?>" required data-email>
                </div>
                <div class="campo campo-senha">
                    <label for="cad-senha">Senha</label>
                    <input id="cad-senha" type="password" name="senha" autocomplete="new-password" minlength="8" maxlength="200" required data-senha aria-describedby="cad-senha-ajuda">
                    <button type="button" class="mostrar-senha" data-mostrar-senha aria-pressed="false" aria-label="Mostrar senha"><?= icone('olho', 20) ?></button>
                </div>
                <p class="campo-ajuda" id="cad-senha-ajuda" style="margin-top: -8px">Use pelo menos 8 caracteres.</p>
                <div class="campo campo-senha">
                    <label for="cad-confirmar">Confirme a senha</label>
                    <input id="cad-confirmar" type="password" name="confirmar" autocomplete="new-password" maxlength="200" required data-confirmar>
                    <button type="button" class="mostrar-senha" data-mostrar-senha aria-pressed="false" aria-label="Mostrar senha"><?= icone('olho', 20) ?></button>
                </div>
                <p class="campo-erro" data-ajuda-confirmar hidden>As senhas não conferem.</p>
                <button class="botao botao-grande botao-bloco" type="submit" data-enviar>
                    <span data-texto>Criar conta</span><span class="girando" aria-hidden="true" hidden></span>
                </button>
            </form>
            <p class="auth-rodape">Já tem conta? <a href="<?= e(url('auth', 'form')) ?>">Entrar</a></p>
        </div>
    </div>
</section>
