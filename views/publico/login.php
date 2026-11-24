<?php
// Entrar na conta (clientes e administradora). O perfil é decidido pelo banco, nunca pelo formulário.
$emailAnterior = old('email');
$classeCorpo = 'pagina-auth';
?>
<section class="auth" aria-labelledby="titulo-login">
    <div class="auth-foto">
        <img src="uploads/fotos/mulher-roxa.jpg" alt="" width="1536" height="1024">
        <p>Mais que um aroma: um momento só seu.</p>
    </div>
    <div class="auth-lado">
        <div class="auth-caixa">
            <h1 id="titulo-login">Entrar</h1>
            <p>Acompanhe pedidos, baixe notas fiscais e compre mais rápido.</p>

            <form class="auth-form" method="post" action="<?= e(url('auth', 'login')) ?>" data-auth-form novalidate>
                <?= csrf_campo() ?>
                <div class="campo">
                    <label for="login-email">E-mail</label>
                    <input id="login-email" type="email" name="email" autocomplete="email" maxlength="120" value="<?= e($emailAnterior) ?>" required data-email>
                </div>
                <div class="campo campo-senha">
                    <label for="login-senha">Senha</label>
                    <input id="login-senha" type="password" name="senha" autocomplete="current-password" maxlength="200" required data-senha>
                    <button type="button" class="mostrar-senha" data-mostrar-senha aria-pressed="false" aria-label="Mostrar senha"><?= icone('olho', 20) ?></button>
                </div>
                <div class="auth-linha">
                    <label class="check"><input type="checkbox" name="lembrar" data-lembrar> Lembrar meu e-mail</label>
                    <a href="<?= e(url('contato', 'form')) ?>">Esqueci a senha</a>
                </div>
                <button class="botao botao-grande botao-bloco" type="submit" data-enviar>
                    <span data-texto>Entrar</span><span class="girando" aria-hidden="true" hidden></span>
                </button>
            </form>
            <p class="auth-rodape">Primeira vez aqui? <a href="<?= e(url('auth', 'cadastro')) ?>">Crie sua conta</a></p>
        </div>
    </div>
</section>
