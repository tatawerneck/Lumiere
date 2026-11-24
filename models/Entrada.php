<?php
// Entradas de estoque: somam quantidades ao estoque do produto, em transação.
class Entrada
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::conexao();
    }

    public function registrar(int $produtoId, int $quantidade, ?float $custo, string $data, ?string $observacao): void
    {
        $this->db->beginTransaction();
        try {
            $s = $this->db->prepare('INSERT INTO entrada (produto_id, quantidade, custo_unitario, data, observacao)
                                     VALUES (:p, :q, :c, :d, :o)');
            $s->execute([':p' => $produtoId, ':q' => $quantidade, ':c' => $custo, ':d' => $data, ':o' => $observacao]);
            $u = $this->db->prepare('UPDATE produto SET estoque = estoque + :q WHERE id = :p');
            $u->execute([':q' => $quantidade, ':p' => $produtoId]);
            $this->db->commit();
        } catch (Throwable $t) {
            $this->db->rollBack();
            throw $t;
        }
    }

    public function listarRecentes(int $limite = 50): array
    {
        $s = $this->db->prepare('SELECT e.*, p.nome AS produto_nome
                                 FROM entrada e JOIN produto p ON p.id = e.produto_id
                                 ORDER BY e.id DESC LIMIT :lim');
        $s->bindValue(':lim', $limite, PDO::PARAM_INT);
        $s->execute();
        return $s->fetchAll();
    }
}
