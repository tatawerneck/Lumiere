<?php // Página de erro (404, 403...). Recebe $codigo e $mensagem. ?>
<section class="erro-pagina">
    <p class="erro-codigo"><?= (int)($codigo ?? 404) ?></p>
    <h1><?= e($mensagem ?? 'Algo não saiu como esperado.') ?></h1>
    <p>Que tal acender outra ideia? As 20 velas da coleção continuam aqui.</p>
    <p><a class="botao" href="<?= e(url('catalogo')) ?>">Ver a coleção</a></p>
</section>
