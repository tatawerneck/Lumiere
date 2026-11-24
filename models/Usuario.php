<?php
// Contas de acesso (clientes e administradores). Senhas sempre armazenadas como hash.
class Usuario
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::conexao();
    }

    public function buscarPorEmail(string $email): ?array
    {
        $s = $this->db->prepare('SELECT * FROM usuario WHERE email = :email');
        $s->execute([':email' => mb_strtolower($email)]);
        return $s->fetch() ?: null;
    }

    public function buscarPorId(int $id): ?array
    {
        $s = $this->db->prepare('SELECT id, nome, email, perfil, cpf, telefone, ativo, criado_em FROM usuario WHERE id = :id');
        $s->execute([':id' => $id]);
        return $s->fetch() ?: null;
    }

    // Cadastro público: sempre cria perfil "cliente", ativo. Lança PDOException (23000) se o e-mail já existir.
    public function criarCliente(string $nome, string $email, string $senhaHash): int
    {
        $s = $this->db->prepare("INSERT INTO usuario (nome, email, senha, perfil, ativo) VALUES (:nome, :email, :senha, 'cliente', 1)");
        $s->execute([':nome' => $nome, ':email' => mb_strtolower($email), ':senha' => $senhaHash]);
        return (int)$this->db->lastInsertId();
    }

    public function criar(string $nome, string $email, string $senhaHash, string $perfil): int
    {
        $s = $this->db->prepare('INSERT INTO usuario (nome, email, senha, perfil, ativo) VALUES (:n, :e, :s, :p, 1)');
        $s->execute([':n' => $nome, ':e' => mb_strtolower($email), ':s' => $senhaHash, ':p' => $perfil === 'admin' ? 'admin' : 'cliente']);
        return (int)$this->db->lastInsertId();
    }

    public function atualizarContato(int $id, string $cpf, string $telefone): void
    {
        $s = $this->db->prepare('UPDATE usuario SET cpf = :c, telefone = :t WHERE id = :id');
        $s->execute([':c' => $cpf, ':t' => $telefone, ':id' => $id]);
    }

    public function alterarSenha(int $id, string $hash): void
    {
        $s = $this->db->prepare('UPDATE usuario SET senha = :s WHERE id = :id');
        $s->execute([':s' => $hash, ':id' => $id]);
    }

    public function listar(string $busca = ''): array
    {
        $sql = 'SELECT id, nome, email, perfil, ativo, criado_em FROM usuario';
        $params = [];
        if ($busca !== '') {
            $sql .= ' WHERE nome LIKE :b OR email LIKE :b';
            $params[':b'] = '%' . $busca . '%';
        }
        $s = $this->db->prepare($sql . ' ORDER BY perfil DESC, nome');
        $s->execute($params);
        return $s->fetchAll();
    }

    public function listarClientes(string $busca = ''): array
    {
        $sql = "SELECT u.id, u.nome, u.email, u.telefone, u.ativo, u.criado_em,
                       COUNT(p.id) AS pedidos,
                       COALESCE(SUM(CASE WHEN p.status IN ('pago','enviado','entregue') THEN p.total END), 0) AS gasto
                FROM usuario u
                LEFT JOIN pedido p ON p.usuario_id = u.id
                WHERE u.perfil = 'cliente'";
        $params = [];
        if ($busca !== '') {
            $sql .= ' AND (u.nome LIKE :b OR u.email LIKE :b)';
            $params[':b'] = '%' . $busca . '%';
        }
        $s = $this->db->prepare($sql . ' GROUP BY u.id ORDER BY u.nome');
        $s->execute($params);
        return $s->fetchAll();
    }

    public function alternarAtivo(int $id, bool $ativo): void
    {
        $s = $this->db->prepare('UPDATE usuario SET ativo = :a WHERE id = :id');
        $s->execute([':a' => $ativo ? 1 : 0, ':id' => $id]);
    }

    public function definirPerfil(int $id, string $perfil): void
    {
        $s = $this->db->prepare('UPDATE usuario SET perfil = :p WHERE id = :id');
        $s->execute([':p' => $perfil === 'admin' ? 'admin' : 'cliente', ':id' => $id]);
    }

    public function totalAdmins(): int
    {
        return (int)$this->db->query("SELECT COUNT(*) FROM usuario WHERE perfil = 'admin' AND ativo = 1")->fetchColumn();
    }
}
