<?php
// Vitrine com filtros. Recebe $filtros, $produtos, $total, $pagina, $totalPaginas, $familias.
$familiaAtual = null;
foreach ($familias as $f) {
    if ($f['slug'] === $filtros['familia']) { $familiaAtual = $f; }
}
$momentoAtual = momentos_lista()[$filtros['momento']] ?? null;

// Parâmetros atuais (sem vazios) para montar links que preservam os filtros.
$atuais = array_filter([
    'busca'      => $filtros['busca'],
    'familia'    => $filtros['familia'],
    'momento'    => $filtros['momento'],
    'preco_max'  => $filtros['preco_max'] !== null ? (int)$filtros['preco_max'] : null,
    'disponivel' => $filtros['disponivel'] ? 1 : null,
    'promocao'   => $filtros['promocao'] ? 1 : null,
    'ordenar'    => $filtros['ordenar'] !== 'destaques' ? $filtros['ordenar'] : null,
], fn($v) => $v !== null && $v !== '' && $v !== false);
$linkFiltro = fn(array $mudar = [], array $tirar = []) => url('catalogo', 'index', array_diff_key(array_merge($atuais, $mudar), array_flip($tirar)));

$titulo = $familiaAtual['nome'] ?? ($momentoAtual ? 'Para ' . mb_strtolower($momentoAtual) : ($filtros['busca'] !== '' ? 'Busca: ' . $filtros['busca'] : 'Coleção completa'));
$subtitulo = $familiaAtual['descricao'] ?? 'Velas de cera vegetal de soja e pavio de algodão, com fragrâncias para cada hora do dia.';
$ordens = ['destaques' => 'Destaques', 'recentes' => 'Lançamentos', 'preco_asc' => 'Menor preço', 'preco_desc' => 'Maior preço', 'nome' => 'Nome (A a Z)'];
$precoMax = $filtros['preco_max'] !== null ? (int)$filtros['preco_max'] : 150;
?>
<section class="wrap pagina-cabeca">
    <ol class="trilha" aria-label="Você está em">
        <li><a href="index.php">Início</a></li>
        <li><a href="<?= e(url('catalogo')) ?>">Coleção</a></li>
        <?php if ($familiaAtual || $momentoAtual): ?><li aria-current="page"><?= e($titulo) ?></li><?php endif; ?>
    </ol>
    <h1 data-revelar><?= e($titulo) ?></h1>
    <p><?= e($subtitulo) ?></p>
</section>

<div class="wrap catalogo">
    <aside class="filtros" id="filtros" data-filtros aria-label="Filtros">
        <form id="form-filtros" method="get" action="index.php" data-filtros-form>
            <input type="hidden" name="controller" value="catalogo">
            <?php if ($filtros['busca'] !== ''): ?><input type="hidden" name="busca" value="<?= e($filtros['busca']) ?>"><?php endif; ?>
            <?php if ($filtros['ordenar'] !== 'destaques'): ?><input type="hidden" name="ordenar" value="<?= e($filtros['ordenar']) ?>"><?php endif; ?>

            <div class="filtros-topo-movel">
                <p class="filtros-titulo">Filtrar velas</p>
            </div>

            <fieldset class="filtros-grupo">
                <legend>Família olfativa</legend>
                <label class="filtro-opcao <?= $filtros['familia'] === '' ? 'ativo' : '' ?>">
                    <input class="sr" type="radio" name="familia" value="" <?= $filtros['familia'] === '' ? 'checked' : '' ?>> Todas
                </label>
                <?php foreach ($familias as $f): ?>
                    <label class="filtro-opcao <?= $filtros['familia'] === $f['slug'] ? 'ativo' : '' ?>">
                        <input class="sr" type="radio" name="familia" value="<?= e($f['slug']) ?>" <?= $filtros['familia'] === $f['slug'] ? 'checked' : '' ?>>
                        <?= e($f['nome']) ?> <small><?= (int)$f['total'] ?></small>
                    </label>
                <?php endforeach; ?>
            </fieldset>

            <fieldset class="filtros-grupo">
                <legend>Momento</legend>
                <label class="filtro-opcao <?= $filtros['momento'] === '' ? 'ativo' : '' ?>">
                    <input class="sr" type="radio" name="momento" value="" <?= $filtros['momento'] === '' ? 'checked' : '' ?>> Qualquer hora
                </label>
                <?php foreach (momentos_lista() as $slug => $nome): ?>
                    <label class="filtro-opcao <?= $filtros['momento'] === $slug ? 'ativo' : '' ?>">
                        <input class="sr" type="radio" name="momento" value="<?= e($slug) ?>" <?= $filtros['momento'] === $slug ? 'checked' : '' ?>> <?= e($nome) ?>
                    </label>
                <?php endforeach; ?>
            </fieldset>

            <div class="filtros-grupo preco-faixa">
                <label class="filtros-titulo" for="preco-max">Preço até</label>
                <input id="preco-max" type="range" name="preco_max" min="70" max="150" step="5" value="<?= $precoMax ?>" data-preco>
                <output for="preco-max" data-preco-saida><?= e(moeda($precoMax)) ?></output>
            </div>

            <fieldset class="filtros-grupo">
                <legend>Mostrar</legend>
                <label class="check"><input type="checkbox" name="disponivel" value="1" <?= $filtros['disponivel'] ? 'checked' : '' ?>> Só com estoque</label>
                <label class="check"><input type="checkbox" name="promocao" value="1" <?= $filtros['promocao'] ? 'checked' : '' ?>> Em promoção</label>
            </fieldset>

            <div class="filtros-acoes">
                <button class="botao botao-pequeno" type="submit" data-filtros-aplicar>Aplicar</button>
                <a class="botao botao-linha botao-pequeno" href="<?= e(url('catalogo')) ?>">Limpar</a>
            </div>
        </form>
    </aside>

    <div>
        <div class="barra-catalogo">
            <p aria-live="polite"><strong><?= (int)$total ?></strong> <?= (int)$total === 1 ? 'vela encontrada' : 'velas encontradas' ?></p>
            <div class="ordenar">
                <button class="botao botao-linha botao-pequeno filtros-abrir" type="button" data-filtros-abrir aria-controls="filtros" aria-expanded="false"><?= icone('filtro', 18) ?> Filtros</button>
                <label for="ordenar">Ordenar por</label>
                <select id="ordenar" data-ordenar>
                    <?php foreach ($ordens as $valor => $rotulo): ?>
                        <option value="<?= e($linkFiltro(['ordenar' => $valor], ['pagina'])) ?>" <?= $filtros['ordenar'] === $valor ? 'selected' : '' ?>><?= e($rotulo) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <?php if (count($atuais) > (isset($atuais['ordenar']) ? 1 : 0)): ?>
            <div class="chips-ativos">
                <?php if ($filtros['busca'] !== ''): ?><a class="chip" href="<?= e($linkFiltro([], ['busca'])) ?>">“<?= e($filtros['busca']) ?>” <?= icone('fechar', 14) ?></a><?php endif; ?>
                <?php if ($familiaAtual): ?><a class="chip" href="<?= e($linkFiltro([], ['familia'])) ?>"><?= e($familiaAtual['nome']) ?> <?= icone('fechar', 14) ?></a><?php endif; ?>
                <?php if ($momentoAtual): ?><a class="chip" href="<?= e($linkFiltro([], ['momento'])) ?>"><?= e($momentoAtual) ?> <?= icone('fechar', 14) ?></a><?php endif; ?>
                <?php if ($filtros['preco_max'] !== null): ?><a class="chip" href="<?= e($linkFiltro([], ['preco_max'])) ?>">Até <?= e(moeda($filtros['preco_max'])) ?> <?= icone('fechar', 14) ?></a><?php endif; ?>
                <?php if ($filtros['disponivel']): ?><a class="chip" href="<?= e($linkFiltro([], ['disponivel'])) ?>">Com estoque <?= icone('fechar', 14) ?></a><?php endif; ?>
                <?php if ($filtros['promocao']): ?><a class="chip" href="<?= e($linkFiltro([], ['promocao'])) ?>">Em promoção <?= icone('fechar', 14) ?></a><?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!$produtos): ?>
            <div class="vazio-catalogo">
                <?= icone('chama', 40) ?>
                <h2>Nenhuma vela com esses filtros</h2>
                <p>Tire um dos filtros acima ou veja a coleção inteira.</p>
                <a class="botao" href="<?= e(url('catalogo')) ?>">Ver todas as velas</a>
            </div>
        <?php else: ?>
            <div class="grade-cards grade-catalogo">
                <?php foreach ($produtos as $p): ?>
                    <?php include RAIZ . '/views/partials/card_produto.php'; ?>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPaginas > 1): ?>
                <nav aria-label="Páginas">
                    <ul class="paginacao">
                        <?php for ($n = 1; $n <= $totalPaginas; $n++): ?>
                            <li>
                                <?php if ($n === $pagina): ?>
                                    <span aria-current="page"><?= $n ?></span>
                                <?php else: ?>
                                    <a href="<?= e($linkFiltro(['pagina' => $n])) ?>" aria-label="Página <?= $n ?>"><?= $n ?></a>
                                <?php endif; ?>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
