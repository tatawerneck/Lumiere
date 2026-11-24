<?php
// Gera o "PIX copia e cola" (BR Code estático) no padrão EMV do Banco Central, com CRC16.
class Pix
{
    private static function campo(string $id, string $valor): string
    {
        return $id . str_pad((string)strlen($valor), 2, '0', STR_PAD_LEFT) . $valor;
    }

    private static function limpar(string $t, int $max): string
    {
        $t = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $t) ?: $t;
        $t = strtoupper(preg_replace('/[^A-Za-z0-9 ]/', '', $t) ?? '');
        return substr(trim($t), 0, $max);
    }

    public static function payload(string $chave, string $nome, string $cidade, float $valor, string $txid): string
    {
        $conta = self::campo('00', 'br.gov.bcb.pix') . self::campo('01', $chave);
        $txid = substr(preg_replace('/[^A-Za-z0-9]/', '', $txid) ?: '***', 0, 25);
        $p = self::campo('00', '01')
           . self::campo('26', $conta)
           . self::campo('52', '0000')
           . self::campo('53', '986')
           . self::campo('54', number_format($valor, 2, '.', ''))
           . self::campo('58', 'BR')
           . self::campo('59', self::limpar($nome, 25))
           . self::campo('60', self::limpar($cidade, 15))
           . self::campo('62', self::campo('05', $txid))
           . '6304';
        return $p . self::crc16($p);
    }

    private static function crc16(string $dados): string
    {
        $crc = 0xFFFF;
        $len = strlen($dados);
        for ($i = 0; $i < $len; $i++) {
            $crc ^= ord($dados[$i]) << 8;
            for ($b = 0; $b < 8; $b++) {
                $crc = ($crc & 0x8000) ? (($crc << 1) ^ 0x1021) : ($crc << 1);
                $crc &= 0xFFFF;
            }
        }
        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }
}
