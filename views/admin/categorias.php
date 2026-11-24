<?php
// Famílias olfativas (categorias). Recebe $categorias.
?>
<div class="grade-2">
    <section class="painel-caixa">
        <h2>Famílias olfativas <span class="estrela" aria-hidden="true">✦</span></h2>
        <div class="tabela-rolagem">
            <table class="tabela">
                <thead><tr><th>Foto</th><th>Família</th><th class="num">Velas</th><th>Situação</th><th><span class="sr">Ações</span></th></tr></thead>
                <tbody>
                    <?php foreach ($categorias as $c): ?>
                        <tr>
                            <td><?php if ($c['imagem_url']): ?><img class="mini" src="<?= e(imagemTamanho($c['imagem_url'], 104, 128)) ?>" alt="" width="52" height="64" loading="lazy"><?php endif; ?></td>
                            <td>
                                <details>
                                    <summary><strong><?= e($c['nome']) ?></strong><small><?= e($c['descricao']) ?></small></summary>
                                    <form class="form-grade" method="post" action="<?= e(url('categoria', 'salvar')) ?>" style="margin-top: 12px">
                                        <?= csrf_campo() ?>
                                        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                        <div class="campo inteira"><label for="c-nome-<?= (int)$c['id'] ?>">Nome</label><input id="c-nome-<?= (int)$c['id'] ?>" name="nome" maxlength="60" value="<?= e($c['nome']) ?>" required></div>
                                        <div class="campo inteira"><label for="c-desc-<?= (int)$c['id'] ?>">Descrição</label><input id="c-desc-<?= (int)$c['id'] ?>" name="descricao" maxlength="255" value="<?= e($c['descricao']) ?>"></div>
                                        <div class="campo inteira"><label for="c-img-<?= (int)$c['id'] ?>">Foto (https)</label><input id="c-img-<?= (int)$c['id'] ?>" type="url" name="imagem_url" maxlength="500" value="<?= e($c['imagem_url']) ?>"></div>
                                        <div class="inteira"><button class="bt bt-pequeno" type="submit">Salvar família</button></div>
                                    </form>
                                </details>
                            </td>
                            <td class="num"><?= (int)$c['produtos'] ?></td>
                            <td><span class="selo-adm <?= $c['ativo'] ? 's-ok' : 's-inativo' ?>"><?= $c['ativo'] ? 'Visível' : 'Oculta' ?></span></td>
                            <td>
                                <div class="acoes">
                                    <form method="post" action="<?= e(url('categoria', 'alternar')) ?>">
                                        <?= csrf_campo() ?>
                                        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                                        <input type="hidden" name="ativo" value="<?= $c['ativo'] ? '0' : '1' ?>">
                                        <button class="bt bt-linha bt-pequeno" type="submit"><?= $c['ativo'] ? 'Ocultar' : 'Mostrar' ?></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="dica" style="margin-top: 14px"><?= icone('editar', 18) ?> Clique no nome de uma família para editar. Famílias ocultas somem do menu e da vitrine, junto com as velas delas.</p>
    </section>

    <section class="painel-caixa">
        <h2>Nova família</h2>
        <form class="form-grade" method="post" action="<?= e(url('categoria', 'salvar')) ?>">
            <?= csrf_campo() ?>
            <input type="hidden" name="id" value="0">
            <div class="campo inteira"><label for="nc-nome">Nome</label><input id="nc-nome" name="nome" maxlength="60" required placeholder="Ex.: Aquáticas"></div>
            <div class="campo inteira"><label for="nc-desc">Descrição curta</label><input id="nc-desc" name="descricao" maxlength="255" placeholder="Aparece no menu Coleções"></div>
            <div class="campo inteira"><label for="nc-img">Foto (https)</label><input id="nc-img" type="url" name="imagem_url" maxlength="500" placeholder="https://..."></div>
            <div class="inteira"><button class="bt" type="submit"><?= icone('mais', 18) ?> Criar família</button></div>
        </form>
    </section>
</div>
