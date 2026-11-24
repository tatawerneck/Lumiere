<?php
// Nota fiscal do pedido. Modelo de demonstração: gera número, série e chave de acesso no formato da NF-e,
// monta o documento e envia por e-mail. Não é autorizada pela SEFAZ (não tem valor fiscal).
class NotaFiscal
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::conexao();
    }

    public function doPedido(int $pedidoId): ?array
    {
        $s = $this->db->prepare('SELECT * FROM nota_fiscal WHERE pedido_id = :p');
        $s->execute([':p' => $pedidoId]);
        return $s->fetch() ?: null;
    }

    // Emite a nota (uma por pedido). Se já existir, devolve a existente.
    public function emitir(array $pedido): array
    {
        $existente = $this->doPedido((int)$pedido['id']);
        if ($existente) {
            return $existente;
        }
        $this->db->beginTransaction();
        try {
            $numero = (int)$this->db->query('SELECT COALESCE(MAX(numero), 0) + 1 FROM nota_fiscal WHERE serie = 1 FOR UPDATE')->fetchColumn();
            $chave = $this->chaveAcesso($numero, 1);
            $tributos = round((float)$pedido['total'] * (float)config('aliquota_tributos'), 2);
            $s = $this->db->prepare('INSERT INTO nota_fiscal (pedido_id, numero, serie, chave_acesso, valor_total, valor_tributos)
                                     VALUES (:p, :n, 1, :c, :v, :t)');
            $s->execute([':p' => $pedido['id'], ':n' => $numero, ':c' => $chave, ':v' => $pedido['total'], ':t' => $tributos]);
            $this->db->commit();
        } catch (Throwable $t) {
            $this->db->rollBack();
            throw $t;
        }
        return $this->doPedido((int)$pedido['id']);
    }

    // Chave de 44 dígitos: UF + AAMM + CNPJ + modelo 55 + série + número + tipo de emissão + código + dígito (módulo 11).
    private function chaveAcesso(int $numero, int $serie): string
    {
        $cnpj = str_pad(so_digitos(config('emitente')['cnpj'] ?? ''), 14, '0', STR_PAD_LEFT);
        $base = '35' . date('ym') . substr($cnpj, 0, 14) . '55'
              . str_pad((string)$serie, 3, '0', STR_PAD_LEFT)
              . str_pad((string)$numero, 9, '0', STR_PAD_LEFT)
              . '1' . str_pad((string)random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        $peso = 2;
        $soma = 0;
        for ($i = strlen($base) - 1; $i >= 0; $i--) {
            $soma += (int)$base[$i] * $peso;
            $peso = $peso === 9 ? 2 : $peso + 1;
        }
        $resto = $soma % 11;
        $dv = ($resto < 2) ? 0 : 11 - $resto;
        return $base . $dv;
    }

    public function registrarEnvio(int $notaId, bool $ok, string $info): void
    {
        $s = $this->db->prepare('UPDATE nota_fiscal SET email_enviado = :ok, email_info = :i, enviado_em = IF(:ok2 = 1, NOW(), enviado_em) WHERE id = :id');
        $s->execute([':ok' => $ok ? 1 : 0, ':ok2' => $ok ? 1 : 0, ':i' => mb_substr($info, 0, 255), ':id' => $notaId]);
    }

    public function listar(): array
    {
        return $this->db->query('SELECT n.*, p.codigo, p.nome, p.email FROM nota_fiscal n JOIN pedido p ON p.id = n.pedido_id
                                 ORDER BY n.id DESC LIMIT 300')->fetchAll();
    }

    // HTML da nota (mesma página usada na tela, na impressão e no e-mail).
    public static function html(array $pedido, array $itens, array $nota, bool $paraEmail = false): string
    {
        ob_start();
        require RAIZ . '/views/nota/documento.php';
        return (string)ob_get_clean();
    }

    // Emite (se preciso) e envia por e-mail. Usado depois do pagamento e pelo botão "Reenviar" do painel.
    public static function emitirEEnviar(int $pedidoId): array
    {
        $pedidoModel = new Pedido();
        $pedido = $pedidoModel->buscarPorId($pedidoId);
        if (!$pedido) {
            return ['ok' => false, 'info' => 'Pedido não encontrado.'];
        }
        $nf = new self();
        $nota = $nf->emitir($pedido);
        $itens = $pedidoModel->itens($pedidoId);
        $html = self::html($pedido, $itens, $nota, true);
        $assunto = 'Nota fiscal do seu pedido ' . $pedido['codigo'] . ' · Lumière';
        $res = Mailer::enviar($pedido['email'], $pedido['nome'], $assunto, $html);
        $nf->registrarEnvio((int)$nota['id'], $res['ok'], $res['info']);
        return $res + ['nota' => $nota];
    }
}
