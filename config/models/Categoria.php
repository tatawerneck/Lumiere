<?php
// Famílias olfativas. Só as ativas aparecem na vitrine e nos filtros.
class Categoria
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::conexao();
    }

    public function listarComContagem(): array
    {
        return $this->db->query('SELECT c.*, COUNT(p.id) AS produtos
                                 FROM categoria c LEFT JOIN produto p ON p.categoria_id = c.id
                                 GROUP BY c.id ORDER BY c.nome')->fetchAll();
    }

    public function listarAtivas(): array
    {
        return $this->db->query('SELECT c.*, COUNT(p.id) AS total
                                 FROM categoria c
                                 LEFT JOIN produto p ON p.categoria_id = c.id AND p.ativo = 1
                                 WHERE c.ativo = 1
                                 GROUP BY c.id ORDER BY c.id')->fetchAll();
    }

    public function buscarPorId(int $id): ?array
    {
        $s = $this->db->prepare('SELECT * FROM categoria WHERE id = :id');
        $s->execute([':id' => $id]);
        return $s->fetch() ?: null;
    }

    public function salvar(?int $id, string $nome, string $descricao, string $imagemUrl): int
    {
        $slug = Produto::gerarSlug($nome);
        if ($id === null) {
            $s = $this->db->prepare('INSERT INTO categoria (nome, slug, descricao, imagem_url, ativo) VALUES (:n, :s, :d, :i, 1)');
            $s->execute([':n' => $nome, ':s' => $slug, ':d' => $descricao ?: null, ':i' => $imagemUrl ?: null]);
            return (int)$this->db->lastInsertId();
        }
        $s = $this->db->prepare('UPDATE categoria SET nome = :n, slug = :s, descricao = :d, imagem_url = :i WHERE id = :id');
        $s->execute([':n' => $nome, ':s' => $slug, ':d' => $descricao ?: null, ':i' => $imagemUrl ?: null, ':id' => $id]);
        return $id;
    }

    public function alternarAtivo(int $id, bool $ativo): void
    {
        $s = $this->db->prepare('UPDATE categoria SET ativo = :a WHERE id = :id');
        $s->execute([':a' => $ativo ? 1 : 0, ':id' => $id]);
    }
}
