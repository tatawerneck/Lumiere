<?php
// Rodapé público: newsletter, links, formas de pagamento e créditos das imagens.
?>
<section class="news" aria-labelledby="news-titulo">
    <div class="wrap news-dentro">
        <div>
            <h2 id="news-titulo">Receba o primeiro aviso de cada fragrância nova</h2>
            <p>Uma carta por mês, com lançamentos, rituais de uso e cupons para quem assina.</p>
        </div>
        <form class="news-form" method="post" action="<?= e(url('newsletter', 'inscrever')) ?>" data-news>
            <?= csrf_campo() ?>
            <label class="sr" for="news-email">Seu e-mail</label>
            <input id="news-email" type="email" name="email" placeholder="seu@email.com" autocomplete="email" required maxlength="120">
            <button class="botao" type="submit">Assinar</button>
            <p class="news-retorno" data-news-retorno role="status"></p>
        </form>
    </div>
</section>

<footer class="rodape">
    <div class="wrap rodape-grade">
        <div class="rodape-marca">
            <img src="public/assets/img/emblema-lumiere.png" alt="Lumière Velas Aromáticas" width="72" height="72" loading="lazy">
            <p>Velas de cera vegetal de soja, pavio de algodão e fragrâncias livres de ftalatos. Feitas à mão, em pequenos lotes.</p>
        </div>
        <nav class="rodape-col" aria-label="Loja">
            <p class="rodape-titulo">Loja</p>
            <a href="<?= e(url('catalogo')) ?>">Todas as velas</a>
            <a href="<?= e(url('catalogo', 'index', ['ordenar' => 'recentes'])) ?>">Lançamentos</a>
            <a href="<?= e(url('catalogo', 'index', ['promocao' => 1])) ?>">Em promoção</a>
            <a href="<?= e(url('carrinho')) ?>">Carrinho</a>
        </nav>
        <nav class="rodape-col" aria-label="Famílias olfativas">
            <p class="rodape-titulo">Famílias</p>
            <?php foreach (array_slice($familiasMenu, 0, 6) as $f): ?>
                <a href="<?= e(url('catalogo', 'index', ['familia' => $f['slug']])) ?>"><?= e($f['nome']) ?></a>
            <?php endforeach; ?>
        </nav>
        <nav class="rodape-col" aria-label="Atendimento">
            <p class="rodape-titulo">Atendimento</p>
            <a href="<?= e(url('contato', 'form')) ?>">Fale conosco</a>
            <a href="<?= e(url('pedido', 'meus')) ?>">Meus pedidos e notas</a>
            <a href="index.php#ritual">Como acender sua vela</a>
            <a href="<?= e(url('auth', 'form')) ?>">Entrar</a>
        </nav>
    </div>

    <div class="wrap rodape-base">
        <ul class="pagamentos" aria-label="Formas de pagamento">
            <li>Visa</li><li>Mastercard</li><li>Elo</li><li>Amex</li><li><?= icone('pix', 14) ?> PIX</li>
        </ul>
        <p>© <?= date('Y') ?> Lumière Velas Aromáticas. Loja de demonstração: pagamentos em modo simulação e nota fiscal sem valor fiscal.</p>
        <p class="rodape-creditos">Fotos de produtos e ambientes: Unsplash e Pexels (licenças gratuitas).</p>
    </div>
</footer>
