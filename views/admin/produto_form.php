<?php
// Cadastro e edição de vela. Recebe $p (ou null) e $categorias.
$temOld = !empty($_SESSION['old']);
$v = function (string $k, $padrao = '') use ($p, $temOld) {
    return $temOld ? old($k, '') : ($p[$k] ?? $padrao);
};
$marcado = function (string $k, bool $padrao) use ($p, $temOld): bool {
    return $temOld ? (bool)old($k, false) : ($p ? (bool)$p[$k] : $padrao);
};
$preco = fn(string $k) => ($x = $v($k)) !== '' && $x !== null && is_numeric($x) ? number_format((float)$x, 2, ',', '') : (string)$x;
$momentosSel = $temOld ? (array)old('momentos', []) : array_filter(explode(',', (string)($p['momentos'] ?? '')));
$foto = $p ? imagemProduto($p) : 'public/assets/img/placeholder-vela.svg';
limpa_old();
?>
<p><a class="bt bt-linha bt-pequeno" href="<?= e(url('produto')) ?>"><?= icone('seta-esq', 16) ?> Voltar à lista</a></p>

<form class="grade-2" method="post" action="<?= e(url('produto', 'salvar')) ?>" enctype="multipart/form-data">
    <?= csrf_campo() ?>
    <input type="hidden" name="id" value="<?= (int)($p['id'] ?? 0) ?>">

    <section class="painel-caixa">
        <h2><?= $p ? 'Editar vela' : 'Cadastrar vela' ?> <span class="estrela" aria-hidden="true">✦</span></h2>
        <div class="form-grade">
            <div class="campo inteira">
                <label for="pf-nome">Nome</label>
                <input id="pf-nome" name="nome" maxlength="100" required value="<?= e($v('nome')) ?>" placeholder="Ex.: Lavanda Provençal">
            </div>
            <div class="campo">
                <label for="pf-cat">Família olfativa</label>
                <select id="pf-cat" name="categoria_id" required>
                    <option value="">Escolha</option>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= (int)$v('categoria_id', 0) === (int)$c['id'] ? 'selected' : '' ?>><?= e($c['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="campo">
                <?php if ($p): ?>
                    <label for="pf-estoque">Estoque atual</label>
                    <input id="pf-estoque" type="number" value="<?= (int)$p['estoque'] ?>" disabled>
                    <span class="campo-ajuda">Para repor, use <a href="<?= e(url('entrada')) ?>">Estoque</a>. Vendas baixam sozinhas.</span>
                <?php else: ?>
                    <label for="pf-estoque">Estoque inicial</label>
                    <input id="pf-estoque" type="number" name="estoque" min="0" value="<?= e($v('estoque', 0)) ?>">
                <?php endif; ?>
            </div>
            <div class="campo">
                <label for="pf-preco">Preço (R$)</label>
                <input id="pf-preco" name="preco" inputmode="decimal" required value="<?= e($preco('preco')) ?>" placeholder="89,90">
            </div>
            <div class="campo">
                <label for="pf-antigo">Preço antigo (opcional)</label>
                <input id="pf-antigo" name="preco_antigo" inputmode="decimal" value="<?= e($preco('preco_antigo')) ?>" placeholder="Mostra o selo de desconto">
            </div>
            <div class="campo">
                <label for="pf-peso">Peso (g)</label>
                <input id="pf-peso" type="number" name="peso_g" min="1" max="5000" value="<?= e($v('peso_g', 200)) ?>">
            </div>
            <div class="campo">
                <label for="pf-queima">Horas de queima</label>
                <input id="pf-queima" type="number" name="queima_h" min="1" max="500" value="<?= e($v('queima_h', 45)) ?>">
            </div>
            <div class="campo">
                <label for="pf-topo">Notas de topo</label>
                <input id="pf-topo" name="notas_topo" maxlength="150" value="<?= e($v('notas_topo')) ?>" placeholder="lavanda, bergamota">
            </div>
            <div class="campo">
                <label for="pf-coracao">Notas de coração</label>
                <input id="pf-coracao" name="notas_coracao" maxlength="150" value="<?= e($v('notas_coracao')) ?>">
            </div>
            <div class="campo inteira">
                <label for="pf-fundo">Notas de fundo</label>
                <input id="pf-fundo" name="notas_fundo" maxlength="150" value="<?= e($v('notas_fundo')) ?>">
            </div>
            <div class="campo inteira">
                <label for="pf-desc">Descrição da essência</label>
                <textarea id="pf-desc" name="descricao" maxlength="1500" rows="4" data-contar><?= e($v('descricao')) ?></textarea>
            </div>
            <div class="campo inteira">
                <label for="pf-quando">Quando usar</label>
                <textarea id="pf-quando" name="quando_usar" maxlength="1500" rows="3" data-contar><?= e($v('quando_usar')) ?></textarea>
                <span class="campo-ajuda">A primeira frase aparece no cartão da vitrine, abaixo do nome.</span>
            </div>
            <fieldset class="campo inteira" style="border: 0; padding: 0; margin: 0">
                <legend class="rotulo" style="margin-bottom: 8px">Combina com os momentos</legend>
                <div class="checks">
                    <?php foreach (momentos_lista() as $slug => $nome): ?>
                        <label class="check"><input type="checkbox" name="momentos[]" value="<?= e($slug) ?>" <?= in_array($slug, $momentosSel, true) ? 'checked' : '' ?>> <?= e($nome) ?></label>
                    <?php endforeach; ?>
                </div>
            </fieldset>
        </div>
    </section>

    <div style="display: grid; gap: 22px">
        <section class="painel-caixa">
            <h2>Foto</h2>
            <img class="previa-foto" src="<?= e(imagemTamanho($foto, 600, 750)) ?>" alt="Prévia da foto da vela" width="300" height="375" data-previa>
            <label class="upload" data-upload style="margin-top: 14px">
                <?= icone('caixa', 30) ?>
                <span><strong>Escolher arquivo</strong> ou arraste aqui<small data-nome-arquivo>JPG, PNG ou WebP até 3 MB</small></span>
                <input type="file" name="imagem" accept="image/jpeg,image/png,image/webp">
            </label>
            <div class="campo" style="margin-top: 14px">
                <label for="pf-url">Ou endereço da foto (https)</label>
                <input id="pf-url" type="url" name="imagem_url" maxlength="500" value="<?= e($v('imagem_url')) ?>" placeholder="https://..." data-url-foto>
            </div>
            <div class="campo" style="margin-top: 12px">
                <label for="pf-url2">Segunda foto (aparece ao passar o mouse)</label>
                <input id="pf-url2" type="url" name="imagem2_url" maxlength="500" value="<?= e($v('imagem2_url')) ?>" placeholder="https://...">
            </div>
            <?php if (!empty($p['imagem_ext'])): ?>
                <label class="check" style="margin-top: 12px"><input type="checkbox" name="remover_foto" value="1"> Remover a foto enviada</label>
            <?php endif; ?>
        </section>

        <section class="painel-caixa">
            <h2>Vitrine</h2>
            <div class="checks" style="display: grid; gap: 10px">
                <label class="check"><input type="checkbox" name="ativo" value="1" <?= $marcado('ativo', true) ? 'checked' : '' ?>> À venda na loja</label>
                <label class="check"><input type="checkbox" name="destaque" value="1" <?= $marcado('destaque', false) ? 'checked' : '' ?>> Mostrar em “Mais amados”</label>
                <label class="check"><input type="checkbox" name="novo" value="1" <?= $marcado('novo', false) ? 'checked' : '' ?>> Selo “Novo”</label>
            </div>
            <div class="form-acoes" style="margin-top: 18px">
                <button class="bt" type="submit"><?= icone('check', 18) ?> Salvar</button>
                <a class="bt bt-linha" href="<?= e(url('produto')) ?>">Cancelar</a>
                <?php if ($p): ?><a class="bt bt-linha" href="<?= e(url('catalogo', 'produto', ['id' => (int)$p['id']])) ?>" target="_blank" rel="noopener"><?= icone('olho', 18) ?> Ver na loja</a><?php endif; ?>
            </div>
        </section>
    </div>
</form>
