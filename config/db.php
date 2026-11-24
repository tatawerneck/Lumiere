<?php
// Conexão PDO com MySQL/MariaDB (XAMPP). Os dados de acesso ficam em config/config.php.
class Database
{
    private static ?PDO $conn = null;

    public static function iniciar(array $cfg): void
    {
        if (self::$conn !== null) {
            return;
        }
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $cfg['db_host'], $cfg['db_porta'], $cfg['db_nome']);
        try {
            self::$conn = new PDO($dsn, $cfg['db_usuario'], $cfg['db_senha'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => true,   // permite repetir o mesmo :parametro na consulta
                PDO::ATTR_STRINGIFY_FETCHES  => false,
            ]);
            self::$conn->exec("SET time_zone = '-03:00'");
        } catch (PDOException $e) {
            http_response_code(500);
            $msg = 'Não foi possível conectar ao banco de dados MySQL.';
            echo '<!doctype html><meta charset="utf-8"><title>Banco de dados</title>'
               . '<div style="font:16px/1.6 system-ui;max-width:640px;margin:10vh auto;padding:24px">'
               . '<h1 style="font-size:22px">' . $msg . '</h1>'
               . '<p>Confira se o MySQL está ligado no painel do XAMPP e se o arquivo <code>database/lumiere.sql</code> '
               . 'foi importado no phpMyAdmin. Os dados de acesso ficam em <code>config/config.php</code>.</p></div>';
            exit;
        }
    }

    public static function conexao(): PDO
    {
        if (self::$conn === null) {
            throw new RuntimeException('Banco de dados não iniciado.');
        }
        return self::$conn;
    }
}
