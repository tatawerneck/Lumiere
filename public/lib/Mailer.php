<?php
// Envio de e-mail pelo Gmail (SMTP com senha de app) usando PHPMailer.
// Sem senha configurada, o e-mail é salvo em storage/emails/ para conferência e o retorno avisa que não foi enviado.
require_once RAIZ . '/lib/PHPMailer/Exception.php';
require_once RAIZ . '/lib/PHPMailer/PHPMailer.php';
require_once RAIZ . '/lib/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{
    /** @return array{ok: bool, info: string} */
    public static function enviar(string $para, string $nome, string $assunto, string $html, string $texto = ''): array
    {
        $senha = (string)config('smtp_senha');
        if ($senha === '') {
            $arquivo = self::salvarCopia($para, $assunto, $html);
            return ['ok' => false, 'info' => 'SMTP do Gmail não configurado. Cópia salva em storage/emails/' . $arquivo];
        }

        $m = new PHPMailer(true);
        try {
            $m->isSMTP();
            $m->Host       = (string)config('smtp_host');
            $m->Port       = (int)config('smtp_porta');
            $m->SMTPAuth   = true;
            $m->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $m->Username   = (string)config('smtp_usuario');
            $m->Password   = $senha;
            $m->CharSet    = 'UTF-8';
            $m->Timeout    = 15;
            $m->setFrom((string)config('email_remetente'), (string)config('email_nome'));
            $m->addAddress($para, $nome);
            $m->isHTML(true);
            $m->Subject = $assunto;
            $m->Body    = $html;
            $m->AltBody = $texto !== '' ? $texto : trim(strip_tags(preg_replace('/<br\s*\/?>/i', "\n", $html) ?? ''));
            $m->send();
            return ['ok' => true, 'info' => 'Enviado para ' . $para];
        } catch (Throwable $e) {
            $arquivo = self::salvarCopia($para, $assunto, $html);
            $erro = $m->ErrorInfo ?: $e->getMessage();
            return ['ok' => false, 'info' => mb_substr('Falha no envio: ' . $erro . ' (cópia em storage/emails/' . $arquivo . ')', 0, 250)];
        }
    }

    private static function salvarCopia(string $para, string $assunto, string $html): string
    {
        $dir = RAIZ . '/storage/emails';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $nome = date('Ymd-His') . '-' . substr(preg_replace('/[^a-z0-9]+/', '-', strtolower($para)) ?? 'email', 0, 40) . '.html';
        @file_put_contents($dir . '/' . $nome, $html);
        return $nome;
    }
}
