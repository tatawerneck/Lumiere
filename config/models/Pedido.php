<?php
// Pedidos da loja: criação em transação (com baixa de estoque), consulta e mudança de status.
class Pedido
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::conexao();
    }

    // Cria o pedido e dá baixa no estoque. Lança RuntimeException se algum item acabou.
    public function criar(array $d, array $itens): array
    {
        $this->db->beginTransaction();
        try {
            $codigo = $this->novoCodigo();
            $s = $this->db->prepare('INSERT INTO pedido (codigo, usuario_id, nome, email, cpf, telefone, cep, endereco, numero, complemento,
                    bairro, cidade, uf, subtotal, desconto, frete, total, pagamento, parcelas, cartao_bandeira, cartao_final, status, pix_txid, pago_em)
                VALUES (:codigo, :usuario_id, :nome, :email, :cpf, :telefone, :cep, :endereco, :numero, :complemento,
                    :bairro, :cidade, :uf, :subtotal, :desconto, :frete, :total, :pagamento, :parcelas, :bandeira, :final, :status, :txid, :pago_em)');
            $s->execute([
                ':codigo' => $codigo, ':usuario_id' => $d['usuario_id'], ':nome' => $d['nome'], ':email' => $d['email'],
                ':cpf' => $d['cpf'], ':telefone' => $d['telefone'], ':cep' => $d['cep'], ':endereco' => $d['endereco'],
                ':numero' => $d['numero'], ':complemento' => $d['complemento'] ?: null, ':bairro' => $d['bairro'],
                ':cidade' => $d['cidade'], ':uf' => $d['uf'], ':subtotal' => $d['subtotal'], ':desconto' => $d['desconto'],
                ':frete' => $d['frete'], ':total' => $d['total'], ':pagamento' => $d['pagamento'], ':parcelas' => $d['parcelas'],
                ':bandeira' => $d['cartao_bandeira'], ':final' => $d['cartao_final'], ':status' => $d['status'],
                ':txid' => $d['pix_txid'], ':pago_em' => $d['status'] === 'pago' ? date('Y-m-d H:i:s') : null,
            ]);
            $pedidoId = (int)$this->db->lastInsertId();

            $baixa = $this->db->prepare('UPDATE produto SET estoque = estoque - :q WHERE id = :id AND estoque >= :q2');
            $item = $this->db->prepare('INSERT INTO pedido_item (pedido_id, produto_id, nome, preco_unitario, quantidade, total)
                                        VALUES (:p, :prod, :nome, :preco, :q, :t)');
            foreach ($itens as $i) {
                $p = $i['produto'];
                $baixa->execute([':q' => $i['quantidade'], ':q2' => $i['quantidade'], ':id' => $p['id']]);
                if ($baixa->rowCount() !== 1) {
                    throw new RuntimeException('A vela "' . $p['nome'] . '" acabou de esgotar. Ajuste o carrinho e tente de novo.');
                }
                $item->execute([':p' => $pedidoId, ':prod' => $p['id'], ':nome' => $p['nome'], ':preco' => $p['preco'],
                                ':q' => $i['quantidade'], ':t' => $i['subtotal']]);
            }
            $this->db->commit();
            return ['id' => $pedidoId, 'codigo' => $codigo];
        } catch (Throwable $t) {
            $this->db->rollBack();
            throw $t;
        }
    }

    private function novoCodigo(): string
    {
        $alfabeto = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        do {
            $c = 'LU';
            for ($i = 0; $i < 8; $i++) {
                $c .= $alfabeto[random_int(0, strlen($alfabeto) - 1)];
            }
            $s = $this->db->prepare('SELECT COUNT(*) FROM pedido WHERE codigo = :c');
            $s->execute([':c' => $c]);
        } while ((int)$s->fetchColumn() > 0);
        return $c;
    }

    public function buscarPorCodigo(string $codigo): ?array
    {
        $s = $this->db->prepare('SELECT * FROM pedido WHERE codigo = :c');
        $s->execute([':c' => $codigo]);
        return $s->fetch() ?: null;
    }

    public function buscarPorId(int $id): ?array
    {
        $s = $this->db->prepare('SELECT * FROM pedido WHERE id = :id');
        $s->execute([':id' => $id]);
        return $s->fetch() ?: null;
    }

    public function itens(int $pedidoId): array
    {
        $s = $this->db->prepare('SELECT i.*, p.imagem_ext, p.imagem_url, p.id AS produto_id
                                 FROM pedido_item i LEFT JOIN produto p ON p.id = i.produto_id
                                 WHERE i.pedido_id = :p ORDER BY i.id');
        $s->execute([':p' => $pedidoId]);
        return $s->fetchAll();
    }

    // Só pedidos feitos com a conta logada. Não libera por e-mail: o cadastro não confirma o e-mail,
    // então qualquer pessoa poderia criar conta com o e-mail de outra e ver endereço e CPF dela.
    public function doUsuario(int $usuarioId): array
    {
        $s = $this->db->prepare('SELECT p.*, n.numero AS nota_numero
                                 FROM pedido p LEFT JOIN nota_fiscal n ON n.pedido_id = p.id
                                 WHERE p.usuario_id = :u ORDER BY p.id DESC');
        $s->execute([':u' => $usuarioId]);
        return $s->fetchAll();
    }

    public function listar(array $f): array
    {
        $where = [];
        $params = [];
        if (!empty($f['status'])) {
            $where[] = 'p.status = :status';
            $params[':status'] = $f['status'];
        }
        if (!empty($f['busca'])) {
            $where[] = '(p.codigo LIKE :b OR p.nome LIKE :b OR p.email LIKE :b)';
            $params[':b'] = '%' . $f['busca'] . '%';
        }
        if (!empty($f['de'])) {
            $where[] = 'DATE(p.criado_em) >= :de';
            $params[':de'] = $f['de'];
        }
        if (!empty($f['ate'])) {
            $where[] = 'DATE(p.criado_em) <= :ate';
            $params[':ate'] = $f['ate'];
        }
        $sql = 'SELECT p.*, n.numero AS nota_numero, n.email_enviado,
                       (SELECT SUM(quantidade) FROM pedido_item i WHERE i.pedido_id = p.id) AS itens
                FROM pedido p LEFT JOIN nota_fiscal n ON n.pedido_id = p.id'
             . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY p.id DESC LIMIT 300';
        $s = $this->db->prepare($sql);
        $s->execute($params);
        return $s->fetchAll();
    }

    public function marcarPago(int $id): bool
    {
        $s = $this->db->prepare("UPDATE pedido SET status = 'pago', pago_em = NOW() WHERE id = :id AND status = 'aguardando_pagamento'");
        $s->execute([':id' => $id]);
        return $s->rowCount() === 1;
    }

    // Muda o status. Cancelar devolve o estoque (uma vez só).
    public function alterarStatus(int $id, string $status): void
    {
        $permitidos = ['aguardando_pagamento', 'pago', 'enviado', 'entregue', 'cancelado'];
        if (!in_array($status, $permitidos, true)) {
            throw new InvalidArgumentException('Status inválido.');
        }
        $this->db->beginTransaction();
        try {
            $s = $this->db->prepare('SELECT status FROM pedido WHERE id = :id FOR UPDATE');
            $s->execute([':id' => $id]);
            $atual = $s->fetchColumn();
            if ($atual === false) {
                throw new RuntimeException('Pedido não encontrado.');
            }
            if ($atual === 'cancelado' && $status !== 'cancelado') {
                throw new RuntimeException('Pedido cancelado não pode ser reaberto.');
            }
            if ($status === 'cancelado' && $atual !== 'cancelado') {
                $this->db->prepare('UPDATE produto p JOIN pedido_item i ON i.produto_id = p.id
                                    SET p.estoque = p.estoque + i.quantidade WHERE i.pedido_id = :id')->execute([':id' => $id]);
            }
            $u = $this->db->prepare('UPDATE pedido SET status = :s, pago_em = IF(:s2 = \'pago\' AND pago_em IS NULL, NOW(), pago_em) WHERE id = :id');
            $u->execute([':s' => $status, ':s2' => $status, ':id' => $id]);
            $this->db->commit();
        } catch (Throwable $t) {
            $this->db->rollBack();
            throw $t;
        }
    }

    public function contarPorStatus(): array
    {
        $res = [];
        foreach ($this->db->query('SELECT status, COUNT(*) AS n FROM pedido GROUP BY status') as $r) {
            $res[$r['status']] = (int)$r['n'];
        }
        return $res;
    }
}
