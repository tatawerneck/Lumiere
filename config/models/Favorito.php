<?php
// Lista de desejos dos clientes logados (visitantes usam o navegador, via localStorage).
class Favorito
{
    public function ids(int $usuarioId): array
    {
        $s = Database::conexao()->prepare('SELECT produto_id FROM favorito WHERE usuario_id = :u');
        $s->execute([':u' => $usuarioId]);
        return array_map('intval', $s->fetchAll(PDO::FETCH_COLUMN));
    }

    public function alternar(int $usuarioId, int $produtoId): bool
    {
        $db = Database::conexao();
        $s = $db->prepare('DELETE FROM favorito WHERE usuario_id = :u AND produto_id = :p');
        $s->execute([':u' => $usuarioId, ':p' => $produtoId]);
        if ($s->rowCount() > 0) {
            return false;
        }
        $db->prepare('INSERT IGNORE INTO favorito (usuario_id, produto_id) VALUES (:u, :p)')->execute([':u' => $usuarioId, ':p' => $produtoId]);
        return true;
    }
}
