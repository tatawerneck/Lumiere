<?php
// Números do painel: faturamento, pedidos, ticket médio, gráficos e mais vendidos.
class Metricas
{
    private PDO $db;
    private const PAGOS = "('pago','enviado','entregue')";

    public function __construct()
    {
        $this->db = Database::conexao();
    }

    public function resumoMes(): array
    {
        $r = $this->db->query("SELECT COUNT(*) AS pedidos, COALESCE(SUM(total),0) AS faturamento
                               FROM pedido WHERE status IN " . self::PAGOS . "
                               AND criado_em >= DATE_FORMAT(CURDATE(), '%Y-%m-01')")->fetch();
        $ant = $this->db->query("SELECT COALESCE(SUM(total),0) FROM pedido WHERE status IN " . self::PAGOS . "
                               AND criado_em >= DATE_FORMAT(CURDATE() - INTERVAL 1 MONTH, '%Y-%m-01')
                               AND criado_em < DATE_FORMAT(CURDATE(), '%Y-%m-01')")->fetchColumn();
        $pedidos = (int)$r['pedidos'];
        $fat = (float)$r['faturamento'];
        return [
            'pedidos'      => $pedidos,
            'faturamento'  => $fat,
            'ticket'       => $pedidos ? $fat / $pedidos : 0,
            'mes_anterior' => (float)$ant,
            'aguardando'   => (int)$this->db->query("SELECT COUNT(*) FROM pedido WHERE status = 'aguardando_pagamento'")->fetchColumn(),
            'clientes'     => (int)$this->db->query("SELECT COUNT(*) FROM usuario WHERE perfil = 'cliente'")->fetchColumn(),
            'produtos'     => (int)$this->db->query('SELECT COUNT(*) FROM produto WHERE ativo = 1')->fetchColumn(),
        ];
    }

    // Faturamento por dia nos últimos N dias (inclui dias sem venda).
    public function vendasPorDia(int $dias = 30): array
    {
        $s = $this->db->prepare("SELECT DATE(criado_em) AS dia, SUM(total) AS total, COUNT(*) AS pedidos
                                 FROM pedido WHERE status IN " . self::PAGOS . " AND criado_em >= CURDATE() - INTERVAL :d DAY
                                 GROUP BY DATE(criado_em)");
        $s->bindValue(':d', $dias - 1, PDO::PARAM_INT);
        $s->execute();
        $mapa = [];
        foreach ($s->fetchAll() as $r) {
            $mapa[$r['dia']] = $r;
        }
        $res = [];
        for ($i = $dias - 1; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i day"));
            $res[] = ['dia' => date('d/m', strtotime($d)), 'total' => (float)($mapa[$d]['total'] ?? 0), 'pedidos' => (int)($mapa[$d]['pedidos'] ?? 0)];
        }
        return $res;
    }

    public function maisVendidos(int $limite = 5): array
    {
        $s = $this->db->prepare("SELECT i.nome, SUM(i.quantidade) AS qtd, SUM(i.total) AS total
                                 FROM pedido_item i JOIN pedido p ON p.id = i.pedido_id
                                 WHERE p.status IN " . self::PAGOS . "
                                 GROUP BY i.produto_id, i.nome ORDER BY qtd DESC LIMIT :l");
        $s->bindValue(':l', $limite, PDO::PARAM_INT);
        $s->execute();
        return $s->fetchAll();
    }

    public function porPagamento(): array
    {
        return $this->db->query("SELECT pagamento, COUNT(*) AS n, SUM(total) AS total FROM pedido
                                 WHERE status IN " . self::PAGOS . " GROUP BY pagamento")->fetchAll();
    }

    public function ultimosPedidos(int $limite = 6): array
    {
        $s = $this->db->prepare('SELECT id, codigo, nome, total, status, pagamento, criado_em FROM pedido ORDER BY id DESC LIMIT :l');
        $s->bindValue(':l', $limite, PDO::PARAM_INT);
        $s->execute();
        return $s->fetchAll();
    }

    // Relatório por período.
    public function periodo(string $de, string $ate): array
    {
        $s = $this->db->prepare("SELECT COUNT(*) AS pedidos, COALESCE(SUM(total),0) AS faturamento,
                                        COALESCE(SUM(desconto),0) AS descontos, COALESCE(SUM(frete),0) AS fretes
                                 FROM pedido WHERE status IN " . self::PAGOS . " AND DATE(criado_em) BETWEEN :de AND :ate");
        $s->execute([':de' => $de, ':ate' => $ate]);
        $resumo = $s->fetch();
        $i = $this->db->prepare("SELECT i.nome, SUM(i.quantidade) AS qtd, SUM(i.total) AS total
                                 FROM pedido_item i JOIN pedido p ON p.id = i.pedido_id
                                 WHERE p.status IN " . self::PAGOS . " AND DATE(p.criado_em) BETWEEN :de AND :ate
                                 GROUP BY i.produto_id, i.nome ORDER BY total DESC");
        $i->execute([':de' => $de, ':ate' => $ate]);
        return ['resumo' => $resumo, 'produtos' => $i->fetchAll()];
    }
}
