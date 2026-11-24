<?php
// Usuários e acesso ao painel. Recebe $usuarios.
$eu = (int)usuario_logado()['id'];
?>
<div class="grade-2">
    <section class="painel-caixa">
        <h2>Contas <span class="estrela" aria-hidden="true">✦</span></h2>
        <div class="tabela-rolagem">
            <table class="tabela">
                <thead><tr><th>Nome</th><th>Perfil</th><th>Situação</th><th>Desde</th><th><span class="sr">Ações</span></th></tr></thead>
                <tbody>
                    <?php foreach ($usuarios as $us): ?>
                        <tr>
                            <td><strong><?= e($us['nome']) ?></strong><small><?= e($us['email']) ?></small></td>
                            <td><span class="selo-adm <?= $us['perfil'] === 'admin' ? 's-rosa' : 's-enviado' ?>"><?= $us['perfil'] === 'admin' ? 'Administradora' : 'Cliente' ?></span></td>
                            <td><span class="selo-adm <?= $us['ativo'] ? 's-ok' : 's-inativo' ?>"><?= $us['ativo'] ? 'Ativa' : 'Inativa' ?></span></td>
                            <td><?= e(dataBr($us['criado_em'])) ?></td>
                            <td>
                                <div class="acoes">
                                    <?php if ((int)$us['id'] !== $eu): ?>
                                        <form method="post" action="<?= e(url('usuario', 'alternar')) ?>" <?= $us['ativo'] ? 'data-confirmar="Desativar a conta de ' . e($us['nome']) . '?"' : '' ?>>
                                            <?= csrf_campo() ?>
                                            <input type="hidden" name="id" value="<?= (int)$us['id'] ?>">
                                            <input type="hidden" name="ativo" value="<?= $us['ativo'] ? '0' : '1' ?>">
                                            <button class="bt bt-linha bt-pequeno" type="submit"><?= $us['ativo'] ? 'Desativar' : 'Ativar' ?></button>
                                        </form>
                                    <?php else: ?><small>Você</small><?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <div style="display: grid; gap: 22px">
        <section class="painel-caixa">
            <h2>Nova administradora</h2>
            <form class="form-grade" method="post" action="<?= e(url('usuario', 'criar')) ?>">
                <?= csrf_campo() ?>
                <div class="campo inteira"><label for="u-nome">Nome</label><input id="u-nome" name="nome" maxlength="80" required></div>
                <div class="campo inteira"><label for="u-email">E-mail</label><input id="u-email" type="email" name="email" maxlength="120" required autocomplete="off"></div>
                <div class="campo inteira"><label for="u-senha">Senha provisória</label><input id="u-senha" type="password" name="senha" minlength="8" maxlength="200" required autocomplete="new-password"></div>
                <div class="inteira"><button class="bt" type="submit"><?= icone('mais', 18) ?> Criar acesso</button></div>
            </form>
        </section>
        <section class="painel-caixa">
            <h2>Trocar minha senha</h2>
            <form class="form-grade" method="post" action="<?= e(url('usuario', 'senha')) ?>">
                <?= csrf_campo() ?>
                <div class="campo inteira"><label for="u-atual">Senha atual</label><input id="u-atual" type="password" name="atual" required autocomplete="current-password"></div>
                <div class="campo inteira"><label for="u-nova">Nova senha</label><input id="u-nova" type="password" name="nova" minlength="8" maxlength="200" required autocomplete="new-password"></div>
                <div class="inteira"><button class="bt bt-noite" type="submit"><?= icone('chave', 18) ?> Salvar nova senha</button></div>
            </form>
        </section>
    </div>
</div>
