<?php
// Funções auxiliares compartilhadas: escape, URLs, formatação, CSRF, mensagens, sessão e permissões.

function e(mixed $valor): string
{
    return htmlspecialchars((string)$valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $controller = 'home', string $action = 'index', array $extra = []): string
{
    return 'index.php?' . http_build_query(['controller' => $controller, 'action' => $action] + $extra);
}

function redirecionar(string $destino): void
{
    header('Location: ' . $destino);
    exit;
}

function moeda(mixed $valor): string
{
    if ($valor === null || $valor === '') {
        return 'Sob consulta';
    }
    return 'R$ ' . number_format((float)$valor, 2, ',', '.');
}

function preco_pix(float $preco): float
{
    return round($preco * (1 - (float)config('pix_desconto')), 2);
}

// "ou 3x de R$ 29,97 sem juros" (respeita o valor mínimo da parcela)
function parcelamento(float $total): string
{
    $max = (int)config('parcelas_max');
    $min = (float)config('parcela_minima');
    $n = max(1, min($max, (int)floor($total / $min)));
    return $n > 1 ? $n . 'x de ' . moeda($total / $n) . ' sem juros' : 'à vista';
}

function desconto_pct(array $p): ?int
{
    if (!empty($p['preco_antigo']) && (float)$p['preco_antigo'] > (float)$p['preco']) {
        return (int)round((1 - (float)$p['preco'] / (float)$p['preco_antigo']) * 100);
    }
    return null;
}

function momentos_lista(): array
{
    return [
        'relaxar'    => 'Relaxar',
        'concentrar' => 'Concentrar',
        'romantico'  => 'Romance',
        'receber'    => 'Receber',
        'energizar'  => 'Energizar',
        'aconchego'  => 'Aconchego',
    ];
}

function status_pedido(string $s): string
{
    return [
        'aguardando_pagamento' => 'Aguardando pagamento',
        'pago'                 => 'Pago',
        'enviado'              => 'Enviado',
        'entregue'             => 'Entregue',
        'cancelado'            => 'Cancelado',
    ][$s] ?? $s;
}

function forma_pagamento(string $f): string
{
    return ['credito' => 'Cartão de crédito', 'debito' => 'Cartão de débito', 'pix' => 'PIX'][$f] ?? $f;
}

function so_digitos(mixed $v): string
{
    return preg_replace('/\D+/', '', (string)$v) ?? '';
}

function cpf_valido(string $cpf): bool
{
    $cpf = so_digitos($cpf);
    if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }
    for ($t = 9; $t < 11; $t++) {
        $soma = 0;
        for ($i = 0; $i < $t; $i++) {
            $soma += (int)$cpf[$i] * (($t + 1) - $i);
        }
        $dv = ((10 * $soma) % 11) % 10;
        if ((int)$cpf[$t] !== $dv) {
            return false;
        }
    }
    return true;
}

function cpf_formatado(string $cpf): string
{
    $c = so_digitos($cpf);
    return strlen($c) === 11 ? substr($c, 0, 3) . '.' . substr($c, 3, 3) . '.' . substr($c, 6, 3) . '-' . substr($c, 9) : $cpf;
}

// Endereço base do site (para links em e-mails). Usa app_url se definido.
function url_absoluta(string $relativa): string
{
    $base = (string)config('app_url');
    if ($base === '') {
        $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
        $base = ($https ? 'https' : 'http') . '://' . $host . $dir;
    }
    return rtrim($base, '/') . '/' . ltrim($relativa, '/');
}

function asset(string $caminho): string
{
    $arquivo = RAIZ . '/' . $caminho;
    return $caminho . (is_file($arquivo) ? '?v=' . filemtime($arquivo) : '');
}

function dataBr(?string $data, bool $comHora = false): string
{
    if (!$data) {
        return '—';
    }
    $t = strtotime($data);
    return $t === false ? '—' : date($comHora ? 'd/m/Y H:i' : 'd/m/Y', $t);
}

function imagemProduto(array $produto): string
{
    if (!empty($produto['imagem_ext'])) {
        return 'uploads/produtos/' . (int)$produto['id'] . '.' . $produto['imagem_ext'];
    }
    if (!empty($produto['imagem_url'])) {
        return $produto['imagem_url'];
    }
    return 'public/assets/img/placeholder-vela.svg';
}

// Segunda foto (troca ao passar o mouse no cartão). Sem segunda foto, repete a principal.
function imagem2Produto(array $produto): string
{
    return !empty($produto['imagem2_url']) ? $produto['imagem2_url'] : imagemProduto($produto);
}

// Versão menor da foto externa do Unsplash (cartões e miniaturas).
function imagemTamanho(string $url, int $w, int $h): string
{
    if (str_contains($url, 'images.unsplash.com')) {
        return preg_replace(['/([?&])w=\d+/', '/([?&])h=\d+/'], ['${1}w=' . $w, '${1}h=' . $h], $url);
    }
    return $url;
}

function texto_limpo(mixed $valor, int $max): string
{
    $t = trim((string)$valor);
    $t = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $t) ?? '';
    return mb_substr($t, 0, $max);
}

// ---------- CSRF ----------
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_campo(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_exige(): void
{
    $enviado = $_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF'] ?? '');
    if (!is_string($enviado) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $enviado)) {
        if (eh_ajax()) {
            json_resposta(['ok' => false, 'erro' => 'Sua sessão expirou. Recarregue a página.'], 403);
        }
        http_response_code(403);
        renderiza('erro', ['codigo' => 403, 'mensagem' => 'Sua sessão expirou. Recarregue a página e tente novamente.']);
        exit;
    }
}

function eh_ajax(): bool
{
    return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';
}

// ---------- Mensagens de feedback (flash) ----------
function flash(string $tipo, string $mensagem): void
{
    $_SESSION['flash'][] = ['tipo' => $tipo, 'msg' => $mensagem];
}

function flashes(): array
{
    $itens = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $itens;
}

// ---------- Sessão e permissões ----------
function usuario_logado(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function eh_admin(): bool
{
    return (usuario_logado()['perfil'] ?? '') === 'admin';
}

function guarda_retorno(): void
{
    $q = $_SERVER['QUERY_STRING'] ?? '';
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && $q !== '') {
        $_SESSION['retorno'] = $q;
    }
}

// Só aceita destinos internos (controller=... sem auth), para evitar redirecionamento aberto.
function destino_pos_login(): string
{
    $q = $_SESSION['retorno'] ?? null;
    unset($_SESSION['retorno']);
    if (is_string($q) && preg_match('/^controller=[a-z]+(&[A-Za-z0-9_=%.\-]*)?$/', $q) && !str_contains($q, 'controller=auth')) {
        return 'index.php?' . $q;
    }
    return eh_admin() ? url('dashboard') : url('pedido', 'meus');
}

function exige_login(): void
{
    if (!usuario_logado()) {
        guarda_retorno();
        flash('erro', 'Entre na sua conta para continuar.');
        redirecionar(url('auth', 'form'));
    }
}

function exige_admin(): void
{
    if (!usuario_logado()) {
        guarda_retorno();
        redirecionar(url('auth', 'form'));
    }
    if (!eh_admin()) {
        http_response_code(403);
        renderiza('erro', ['codigo' => 403, 'mensagem' => 'Você não tem permissão para acessar esta área.']);
        exit;
    }
}

// ---------- Renderização ----------
function renderiza(string $view, array $dados = [], string $layout = 'publico'): void
{
    extract($dados, EXTR_SKIP);
    ob_start();
    require RAIZ . '/views/' . $view . '.php';
    $conteudo = ob_get_clean();
    require RAIZ . '/views/layouts/' . $layout . '.php';
}

function json_resposta(array $dados, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

// ---------- Configuração e entrada de dados ----------
function config(string $chave): mixed
{
    static $cfg = null;
    $cfg ??= require RAIZ . '/config/config.php';
    return $cfg[$chave] ?? null;
}

// Converte texto em número decimal não negativo, ou null se vazio/inválido.
function valor_decimal(mixed $v, ?float $max = null): ?float
{
    $t = str_replace(',', '.', trim((string)$v));
    if ($t === '' || !is_numeric($t)) {
        return null;
    }
    $n = (float)$t;
    if ($n < 0 || ($max !== null && $n > $max)) {
        return null;
    }
    return round($n, 2);
}

// Guarda os campos digitados (sem senha e sem CSRF) para repovoar o formulário após um erro.
function stash_old(array $dados, array $ignorar = ['senha', 'confirmar', 'csrf']): void
{
    $_SESSION['old'] = array_diff_key($dados, array_flip($ignorar));
}

function old(string $chave, mixed $padrao = ''): mixed
{
    return $_SESSION['old'][$chave] ?? $padrao;
}

function limpa_old(): void
{
    unset($_SESSION['old']);
}

// Primeira frase de um texto (usada na descrição curta dos cartões).
function primeira_frase(mixed $texto): string
{
    $t = trim((string)$texto);
    if (preg_match('/^(.+?[.!?])(\s|$)/u', $t, $m)) {
        return $m[1];
    }
    return $t;
}

// Ícones de traço fino usados no site e no painel (SVG inline, sem dependência externa).
function icone(string $nome, int $tam = 22): string
{
    static $p = [
        'busca'    => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="M20 20l-4.8-4.8"/>',
        'usuario'  => '<circle cx="12" cy="8" r="4"/><path d="M4 20.5c1.4-3.6 4.4-5.5 8-5.5s6.6 1.9 8 5.5"/>',
        'coracao'  => '<path d="M12 20s-7.5-4.6-7.5-10.2A4.3 4.3 0 0112 7.3a4.3 4.3 0 017.5 2.5C19.5 15.4 12 20 12 20z"/>',
        'sacola'   => '<path d="M5.5 8.5h13l-1 12h-11z"/><path d="M9 8.5V7a3 3 0 016 0v1.5"/>',
        'fechar'   => '<path d="M6 6l12 12M18 6L6 18"/>',
        'menu'     => '<path d="M3.5 7h17M3.5 12h17M3.5 17h17"/>',
        'seta'     => '<path d="M4 12h15M14 6.5l5.5 5.5-5.5 5.5"/>',
        'seta-esq' => '<path d="M20 12H5M10 6.5L4.5 12l5.5 5.5"/>',
        'mais'     => '<path d="M12 5v14M5 12h14"/>',
        'menos'    => '<path d="M5 12h14"/>',
        'lixo'     => '<path d="M4.5 7h15M9.5 7V4.5h5V7M6.5 7l1 13h9l1-13"/>',
        'chama'    => '<path d="M12 21c-3.6 0-6-2.4-6-5.6 0-3.4 3-5.4 3.6-9.4 2.4 1.6 3.4 3.6 3.4 5.6 1-.6 1.6-1.6 1.8-2.8 1.6 1.4 3.2 3.6 3.2 6.6 0 3.2-2.4 5.6-6 5.6z"/>',
        'relogio'  => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>',
        'caminhao' => '<path d="M2.5 6.5h11v9h-11zM13.5 9.5h4l3 3v3h-7"/><circle cx="7" cy="17.5" r="1.8"/><circle cx="17" cy="17.5" r="1.8"/>',
        'pix'      => '<path d="M12 3.5l8.5 8.5-8.5 8.5L3.5 12z"/><path d="M8.5 9l3.5 3.5L15.5 9M8.5 15l3.5-3.5 3.5 3.5"/>',
        'cartao'   => '<rect x="2.5" y="5.5" width="19" height="13" rx="2"/><path d="M2.5 9.5h19M6 15h4"/>',
        'nota'     => '<path d="M6 2.5h9l3.5 3.5v15.5H6z"/><path d="M15 2.5V6h3.5M9 11h6.5M9 14.5h6.5M9 18h4"/>',
        'escudo'   => '<path d="M12 3l7.5 3v5.5c0 4.6-3.2 8.2-7.5 9.5-4.3-1.3-7.5-4.9-7.5-9.5V6z"/><path d="M8.8 12l2.2 2.2 4.2-4.4"/>',
        'folha'    => '<path d="M5 19c0-8 5-13.5 14.5-14.5C19 14 13.5 19 5 19z"/><path d="M5 19l8-8"/>',
        'check'    => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
        'copiar'   => '<rect x="8.5" y="8.5" width="11" height="11" rx="1.5"/><path d="M15.5 8.5v-3a1 1 0 00-1-1h-9a1 1 0 00-1 1v9a1 1 0 001 1h3"/>',
        'filtro'   => '<path d="M4 6.5h16M7 12h10M10 17.5h4"/>',
        'sair'     => '<path d="M14.5 4.5h4v15h-4M10 8l-4 4 4 4M6 12h10"/>',
        'casa'     => '<path d="M3.5 11L12 4l8.5 7M6 9.5v10h12v-10"/>',
        'caixa'    => '<path d="M3.5 7.5L12 3.5l8.5 4v9L12 20.5l-8.5-4z"/><path d="M3.5 7.5L12 11.5l8.5-4M12 11.5v9"/>',
        'etiqueta' => '<path d="M3.5 12.5V4h8.5l8.5 8.5-8 8z"/><circle cx="8" cy="8.5" r="1.4"/>',
        'entrada'  => '<rect x="3.5" y="3.5" width="17" height="17" rx="2"/><path d="M8 12h8M12.5 8.5L16 12l-3.5 3.5"/>',
        'carrinho' => '<path d="M2.5 4h2.6l2.3 11h11l2-8H6.2"/><circle cx="9" cy="19" r="1.4"/><circle cx="17" cy="19" r="1.4"/>',
        'grafico'  => '<path d="M4 20V10M10 20V4M16 20v-7M21 20H3"/>',
        'pessoas'  => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20c.8-3.4 3.4-5.5 6.5-5.5s5.7 2.1 6.5 5.5"/><path d="M15.5 4.8a3.5 3.5 0 010 6.4M18 14.8c1.8.8 3 2.6 3.5 5.2"/>',
        'carta'    => '<rect x="3" y="5.5" width="18" height="13" rx="1.5"/><path d="M3.5 6.5L12 13l8.5-6.5"/>',
        'chave'    => '<circle cx="8" cy="15" r="4.5"/><path d="M11.2 11.8L20 3M16.5 6.5L19 9M14.5 8.5l2 2"/>',
        'alerta'   => '<path d="M12 4l9 16H3z"/><path d="M12 10v4.5M12 17.2v.3"/>',
        'olho'     => '<path d="M2 12s3.6-6.5 10-6.5S22 12 22 12s-3.6 6.5-10 6.5S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
        'editar'   => '<path d="M4 20h4L19 9l-4-4L4 16z"/><path d="M13.5 6.5l4 4"/>',
        'imprimir' => '<path d="M7 9V3.5h10V9M7 17.5H4.5v-8h15v8H17"/><rect x="7" y="14" width="10" height="6.5"/>',
    ];
    $d = $p[$nome] ?? '';
    return '<svg class="ic ic-' . $nome . '" width="' . $tam . '" height="' . $tam . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $d . '</svg>';
}
