<?php
// Inscrições na newsletter (rodapé e home).
class Newsletter
{
    public function inscrever(string $email): bool
    {
        $s = Database::conexao()->prepare('INSERT IGNORE INTO newsletter (email) VALUES (:e)');
        $s->execute([':e' => mb_strtolower($email)]);
        return $s->rowCount() > 0;
    }

    public function total(): int
    {
        return (int)Database::conexao()->query('SELECT COUNT(*) FROM newsletter')->fetchColumn();
    }
}
