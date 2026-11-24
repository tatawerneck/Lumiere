<?php
// Carrinho de compras guardado na sessão: [produto_id => quantidade].
// Preços e estoque sempre vêm do banco no momento do cálculo (nunca do navegador).
class Carrinho
{
    private const MAX_POR_ITEM = 20;

    public static function bruto(): array
    {
        return array_filter((array)($_SESSION['carrinho'] ?? []), fn($q) => (int)$q > 0);
    }

    public static function quantidadeTotal(): int
    {
        return array_sum(array_map('intval', self::bruto()));
    }

    public static function adicionar(int $produtoId, int $qtd): void
    {
        $atual = (int)($_SESSION['carrinho'][$produtoId] ?? 0);
        $_SESSION['carrinho'][$produtoId] = min(self::MAX_POR_ITEM, max(0, $atual + $qtd));
    }

    public static function definir(int $produtoId, int $qtd): void
    {
        if ($qtd <= 0) {
            unset($_SESSION['carrinho'][$produtoId]);
            return;
        }
        $_SESSION['carrinho'][$produtoId] = min(self::MAX_POR_ITEM, $qtd);
    }

    public static function remover(int $produtoId): void
    {
        unset($_SESSION['carrinho'][$produtoId]);
    }

    public static function limpar(): void
    {
        unset($_SESSION['carrinho']);
    }

    // Itens com dados do produto. Ajusta quantidades acima do estoque e remove itens indisponíveis.
    public static function itens(): array
    {
        $bruto = self::bruto();
        $produtos = (new Produto())->buscarVarios(array_keys($bruto));
        $itens = [];
        foreach ($bruto as $id => $qtd) {
            $p = $produtos[(int)$id] ?? null;
            if (!$p || (int)$p['estoque'] <= 0) {
                unset($_SESSION['carrinho'][$id]);
                continue;
            }
            $qtd = min((int)$qtd, (int)$p['estoque']);
            $_SESSION['carrinho'][$id] = $qtd;
            $itens[] = [
                'produto'    => $p,
                'quantidade' => $qtd,
                'subtotal'   => round((float)$p['preco'] * $qtd, 2),
            ];
        }
        return $itens;
    }

    public static function resumo(?array $itens = null, string $pagamento = 'credito'): array
    {
        $itens ??= self::itens();
        $subtotal = round(array_sum(array_column($itens, 'subtotal')), 2);
        $limite = (float)config('frete_gratis_acima');
        $frete = ($subtotal >= $limite || $subtotal <= 0) ? 0.0 : (float)config('frete_fixo');
        $desconto = $pagamento === 'pix' ? round($subtotal * (float)config('pix_desconto'), 2) : 0.0;
        $total = round($subtotal - $desconto + $frete, 2);
        return [
            'itens'         => $itens,
            'quantidade'    => array_sum(array_column($itens, 'quantidade')),
            'subtotal'      => $subtotal,
            'frete'         => $frete,
            'desconto'      => $desconto,
            'total'         => $total,
            'total_pix'     => round($subtotal - round($subtotal * (float)config('pix_desconto'), 2) + $frete, 2),
            'falta_frete'   => max(0, round($limite - $subtotal, 2)),
            'frete_pct'     => $limite > 0 ? min(100, (int)round($subtotal / $limite * 100)) : 100,
        ];
    }
}
