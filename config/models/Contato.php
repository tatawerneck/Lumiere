<?php
// Mensagens enviadas pelo formulário de contato do site.
class Contato
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::conexao();
    }

    public function registrar(string $nome, string $email, string $mensagem): void
    {
        $s = $this->db->prepare('INSERT INTO contato (nome, email, mensagem) VALUES (:n, :e, :m)');
        $s->execute([':n' => $nome, ':e' => $email, ':m' => $mensagem]);
    }

    public function listar(): array
    {
        return $this->db->query('SELECT * FROM contato ORDER BY lido ASC, id DESC LIMIT 200')->fetchAll();
    }

    public function marcarLido(int $id): void
    {
        $s = $this->db->prepare('UPDATE contato SET lido = 1 WHERE id = :id');
        $s->execute([':id' => $id]);
    }

    public function novos(): int
    {
        return (int)$this->db->query('SELECT COUNT(*) FROM contato WHERE lido = 0')->fetchColumn();
    }
}
