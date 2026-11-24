<?php
// Página inicial: hero, destaques, abas por momento, famílias olfativas e conteúdo editorial.
class HomeController
{
    public function index(): void
    {
        $produto = new Produto();
        $porMomento = [];
        foreach (array_keys(momentos_lista()) as $m) {
            $porMomento[$m] = $produto->porMomento($m, 4);
        }
        renderiza('publico/home', [
            'titulo'     => 'Lumière Velas Aromáticas · Velas de cera vegetal feitas à mão',
            'metaDesc'   => 'Velas aromáticas de cera vegetal com pavio de algodão. 20 fragrâncias para relaxar, receber e aconchegar. Pague no PIX com desconto ou em até 6x.',
            'paginaHome' => true,
            'destaques'  => $produto->destaques(8),
            'porMomento' => $porMomento,
            'familias'   => (new Categoria())->listarAtivas(),
            'scripts'    => [],
            'jsonLd'     => [
                '@context'    => 'https://schema.org',
                '@type'       => 'Store',
                'name'        => 'Lumière Velas Aromáticas',
                'description' => 'Velas aromáticas de cera vegetal de soja, feitas à mão em pequenos lotes.',
                'logo'        => url_absoluta('public/assets/img/logo-lumiere.png'),
                'url'         => url_absoluta('index.php'),
                'paymentAccepted' => 'Cartão de crédito, cartão de débito, PIX',
                'currenciesAccepted' => 'BRL',
            ],
        ]);
    }
}
