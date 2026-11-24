<?php
// Ponto de entrada único: roteamento por ?controller=&action= e verificação de CSRF em todo POST.
declare(strict_types=1);

define('RAIZ', __DIR__);

$config = require RAIZ . '/config/config.php';
require RAIZ . '/config/helpers.php';
require RAIZ . '/config/db.php';

// Classes de modelo (models/Nome.php) e bibliotecas (lib/Nome.php) carregadas sob demanda.
spl_autoload_register(function (string $classe): void {
    if (!preg_match('/^[A-Za-z]+$/', $classe)) {
        return;
    }
    foreach (['models', 'lib'] as $pasta) {
        $arquivo = RAIZ . '/' . $pasta . '/' . $classe . '.php';
        if (is_file($arquivo)) {
            require_once $arquivo;
            return;
        }
    }
});

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => !empty($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
date_default_timezone_set('America/Sao_Paulo');

Database::iniciar($config);

$controller = preg_replace('/[^a-z]/', '', (string)($_GET['controller'] ?? 'home'));
$action     = preg_replace('/[^a-zA-Z_]/', '', (string)($_GET['action'] ?? 'index'));

$mapa = [
    // site público
    'home'      => 'HomeController',
    'catalogo'  => 'CatalogoController',
    'carrinho'  => 'CarrinhoController',
    'checkout'  => 'CheckoutController',
    'pedido'    => 'PedidoController',
    'nota'      => 'NotaController',
    'favorito'  => 'FavoritoController',
    'auth'      => 'AuthController',
    'contato'   => 'ContatoController',
    'newsletter'=> 'NewsletterController',
    // painel administrativo
    'dashboard' => 'DashboardController',
    'vendas'    => 'VendasController',
    'produto'   => 'ProdutoController',
    'categoria' => 'CategoriaController',
    'entrada'   => 'EntradaController',
    'cliente'   => 'ClienteController',
    'relatorio' => 'RelatorioController',
    'usuario'   => 'UsuarioController',
];

if (!isset($mapa[$controller])) {
    http_response_code(404);
    renderiza('erro', ['codigo' => 404, 'mensagem' => 'Página não encontrada.']);
    exit;
}

$classe = $mapa[$controller];
require RAIZ . '/controllers/' . $classe . '.php';
$c = new $classe();

if ($action === '' || str_starts_with($action, '_') || !method_exists($c, $action)
    || !(new ReflectionMethod($c, $action))->isPublic()) {
    http_response_code(404);
    renderiza('erro', ['codigo' => 404, 'mensagem' => 'Página não encontrada.']);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    csrf_exige();
}

$c->$action();
