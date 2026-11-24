<?php
// Nota fiscal de demonstração (layout inspirado no DANFE). Usada na tela, na impressão e no corpo do e-mail.
// Recebe $pedido, $itens, $nota e $paraEmail. Estilos em linha para funcionar também no Gmail.
$em = config('emitente');
$num = str_pad((string)$nota['numero'], 9, '0', STR_PAD_LEFT);
$numFmt = substr($num, 0, 3) . '.' . substr($num, 3, 3) . '.' . substr($num, 6, 3);
$chaveFmt = trim(chunk_split((string)$nota['chave_acesso'], 4, ' '));
$ufEmitente = strtoupper(trim(substr((string)$em['cidade_uf'], -2)));
$cfop = strtoupper((string)$pedido['uf']) === $ufEmitente ? '5102' : '6102';
$linkPedido = url_absoluta(url('pedido', 'confirmacao', ['codigo' => $pedido['codigo']]));
$linkNota = url_absoluta(url('nota', 'ver', ['codigo' => $pedido['codigo']]));
$primeiro = explode(' ', trim($pedido['nome']))[0];

$c = [ // estilos reaproveitados
    'cel'   => 'border:1px solid #CDBDA6;padding:6px 8px;vertical-align:top;',
    'rot'   => 'display:block;font-size:9px;letter-spacing:.06em;text-transform:uppercase;color:#7A7268;margin-bottom:2px;',
    'val'   => 'font-size:12px;color:#1C1A17;',
    'th'    => 'border:1px solid #CDBDA6;padding:6px;font-size:9px;letter-spacing:.05em;text-transform:uppercase;color:#7A7268;background:#F1E8DC;text-align:left;',
    'td'    => 'border:1px solid #CDBDA6;padding:6px;font-size:11px;color:#1C1A17;',
];
?>
<?php if (!$paraEmail): ?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Nota fiscal <?= e($numFmt) ?> · Pedido <?= e($pedido['codigo']) ?></title>
    <style>
        body { margin: 0; background: #EADFCF; }
        .barra { max-width: 820px; margin: 0 auto; padding: 18px 16px 0; display: flex; gap: 10px; justify-content: flex-end; font: 14px/1.4 system-ui, sans-serif; }
        .barra a, .barra button { display: inline-flex; align-items: center; min-height: 40px; padding: 0 16px; border: 1px solid #1C1A17; background: #1C1A17; color: #FAF6F0; text-decoration: none; cursor: pointer; font: inherit; border-radius: 2px; }
        .barra a { background: transparent; color: #1C1A17; }
        @media print { body { background: #fff; } .barra { display: none; } .folha { box-shadow: none !important; margin: 0 !important; } }
    </style>
</head>
<body>
<div class="barra">
    <a href="<?= e(url('pedido', 'confirmacao', ['codigo' => $pedido['codigo']])) ?>">Voltar ao pedido</a>
    <button type="button" onclick="window.print()">Imprimir ou salvar PDF</button>
</div>
<?php endif; ?>

<div class="folha" style="max-width:820px;margin:18px auto;background:#FFFDF9;padding:28px;font-family:Helvetica,Arial,sans-serif;color:#1C1A17;box-shadow:0 20px 50px -30px rgba(40,26,12,.5);">

    <?php if ($paraEmail): ?>
        <p style="font-size:15px;line-height:1.6;margin:0 0 6px;">Olá, <?= e($primeiro) ?>!</p>
        <p style="font-size:15px;line-height:1.6;margin:0 0 18px;">Agradecemos a sua compra na Lumière. Seu pagamento foi confirmado e a nota fiscal do pedido <strong><?= e($pedido['codigo']) ?></strong> está logo abaixo. Você também pode <a href="<?= e($linkNota) ?>" style="color:#8A5A2B;">abrir a nota no site</a> para imprimir ou salvar em PDF.</p>
    <?php endif; ?>

    <div style="background:#F2E2CB;border-left:3px solid #8A5A2B;padding:8px 12px;font-size:11px;letter-spacing:.04em;margin-bottom:14px;">
        DOCUMENTO DE DEMONSTRAÇÃO, SEM VALOR FISCAL. Gerado pela loja em modo de simulação, sem transmissão à SEFAZ.
    </div>

    <table role="presentation" style="width:100%;border-collapse:collapse;">
        <tr>
            <td style="<?= $c['cel'] ?>width:58%;">
                <div style="font-family:Georgia,'Times New Roman',serif;font-size:22px;letter-spacing:.04em;margin-bottom:4px;">LUMIÈRE</div>
                <div style="<?= $c['val'] ?>font-weight:bold;"><?= e($em['razao_social']) ?></div>
                <div style="<?= $c['val'] ?>"><?= e($em['endereco']) ?></div>
                <div style="<?= $c['val'] ?>"><?= e($em['cidade_uf']) ?>, CEP <?= e($em['cep']) ?></div>
                <div style="<?= $c['val'] ?>">Fone <?= e($em['telefone']) ?></div>
            </td>
            <td style="<?= $c['cel'] ?>text-align:center;">
                <div style="font-size:15px;font-weight:bold;letter-spacing:.06em;">DANFE</div>
                <div style="font-size:10px;color:#4B4640;margin:2px 0 8px;">Documento auxiliar da nota fiscal eletrônica</div>
                <div style="font-size:12px;">0 - Entrada &nbsp; <strong>1 - Saída</strong></div>
                <div style="font-size:14px;font-weight:bold;margin-top:8px;">Nº <?= e($numFmt) ?></div>
                <div style="font-size:12px;">Série <?= (int)$nota['serie'] ?></div>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="<?= $c['cel'] ?>">
                <span style="<?= $c['rot'] ?>">Chave de acesso</span>
                <span style="font-family:'Courier New',monospace;font-size:13px;letter-spacing:.04em;"><?= e($chaveFmt) ?></span>
            </td>
        </tr>
        <tr>
            <td style="<?= $c['cel'] ?>"><span style="<?= $c['rot'] ?>">Natureza da operação</span><span style="<?= $c['val'] ?>">Venda de mercadoria</span></td>
            <td style="<?= $c['cel'] ?>"><span style="<?= $c['rot'] ?>">Data de emissão</span><span style="<?= $c['val'] ?>"><?= e(dataBr($nota['emitida_em'], true)) ?></span></td>
        </tr>
        <tr>
            <td style="<?= $c['cel'] ?>"><span style="<?= $c['rot'] ?>">CNPJ</span><span style="<?= $c['val'] ?>"><?= e($em['cnpj']) ?></span></td>
            <td style="<?= $c['cel'] ?>"><span style="<?= $c['rot'] ?>">Inscrição estadual</span><span style="<?= $c['val'] ?>"><?= e($em['ie']) ?></span></td>
        </tr>
    </table>

    <p style="font-size:11px;font-weight:bold;letter-spacing:.06em;margin:16px 0 4px;">DESTINATÁRIO</p>
    <table role="presentation" style="width:100%;border-collapse:collapse;">
        <tr>
            <td style="<?= $c['cel'] ?>width:58%;"><span style="<?= $c['rot'] ?>">Nome</span><span style="<?= $c['val'] ?>"><?= e($pedido['nome']) ?></span></td>
            <td style="<?= $c['cel'] ?>"><span style="<?= $c['rot'] ?>">CPF</span><span style="<?= $c['val'] ?>"><?= e(cpf_formatado($pedido['cpf'])) ?></span></td>
        </tr>
        <tr>
            <td style="<?= $c['cel'] ?>"><span style="<?= $c['rot'] ?>">Endereço</span><span style="<?= $c['val'] ?>"><?= e($pedido['endereco'] . ', ' . $pedido['numero'] . ($pedido['complemento'] ? ', ' . $pedido['complemento'] : '') . ', ' . $pedido['bairro']) ?></span></td>
            <td style="<?= $c['cel'] ?>"><span style="<?= $c['rot'] ?>">Município / UF / CEP</span><span style="<?= $c['val'] ?>"><?= e($pedido['cidade'] . ' / ' . $pedido['uf'] . ' / ' . substr($pedido['cep'], 0, 5) . '-' . substr($pedido['cep'], 5)) ?></span></td>
        </tr>
        <tr>
            <td style="<?= $c['cel'] ?>"><span style="<?= $c['rot'] ?>">E-mail</span><span style="<?= $c['val'] ?>"><?= e($pedido['email']) ?></span></td>
            <td style="<?= $c['cel'] ?>"><span style="<?= $c['rot'] ?>">Pedido</span><span style="<?= $c['val'] ?>"><?= e($pedido['codigo']) ?></span></td>
        </tr>
    </table>

    <p style="font-size:11px;font-weight:bold;letter-spacing:.06em;margin:16px 0 4px;">PRODUTOS</p>
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr>
                <th style="<?= $c['th'] ?>">Cód.</th>
                <th style="<?= $c['th'] ?>">Descrição</th>
                <th style="<?= $c['th'] ?>">NCM</th>
                <th style="<?= $c['th'] ?>">CFOP</th>
                <th style="<?= $c['th'] ?>">Un.</th>
                <th style="<?= $c['th'] ?>text-align:right;">Qtd.</th>
                <th style="<?= $c['th'] ?>text-align:right;">Valor unit.</th>
                <th style="<?= $c['th'] ?>text-align:right;">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($itens as $it): ?>
                <tr>
                    <td style="<?= $c['td'] ?>"><?= str_pad((string)$it['produto_id'], 4, '0', STR_PAD_LEFT) ?></td>
                    <td style="<?= $c['td'] ?>">Vela aromática <?= e($it['nome']) ?></td>
                    <td style="<?= $c['td'] ?>">3406.00.00</td>
                    <td style="<?= $c['td'] ?>"><?= $cfop ?></td>
                    <td style="<?= $c['td'] ?>">UN</td>
                    <td style="<?= $c['td'] ?>text-align:right;"><?= (int)$it['quantidade'] ?></td>
                    <td style="<?= $c['td'] ?>text-align:right;"><?= e(moeda($it['preco_unitario'])) ?></td>
                    <td style="<?= $c['td'] ?>text-align:right;"><?= e(moeda($it['total'])) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <table role="presentation" style="width:100%;border-collapse:collapse;margin-top:14px;">
        <tr>
            <td style="<?= $c['cel'] ?>"><span style="<?= $c['rot'] ?>">Valor dos produtos</span><span style="<?= $c['val'] ?>"><?= e(moeda($pedido['subtotal'])) ?></span></td>
            <td style="<?= $c['cel'] ?>"><span style="<?= $c['rot'] ?>">Desconto</span><span style="<?= $c['val'] ?>"><?= e(moeda($pedido['desconto'])) ?></span></td>
            <td style="<?= $c['cel'] ?>"><span style="<?= $c['rot'] ?>">Frete</span><span style="<?= $c['val'] ?>"><?= e(moeda($pedido['frete'])) ?></span></td>
            <td style="<?= $c['cel'] ?>background:#F1E8DC;"><span style="<?= $c['rot'] ?>">Valor total da nota</span><span style="font-size:15px;font-weight:bold;"><?= e(moeda($nota['valor_total'])) ?></span></td>
        </tr>
        <tr>
            <td colspan="2" style="<?= $c['cel'] ?>"><span style="<?= $c['rot'] ?>">Forma de pagamento</span><span style="<?= $c['val'] ?>"><?= e(forma_pagamento($pedido['pagamento'])) ?><?= $pedido['cartao_final'] ? ', ' . e($pedido['cartao_bandeira']) . ' final ' . e($pedido['cartao_final']) : '' ?><?= $pedido['pagamento'] === 'credito' ? ', ' . (int)$pedido['parcelas'] . 'x' : '' ?></span></td>
            <td colspan="2" style="<?= $c['cel'] ?>"><span style="<?= $c['rot'] ?>">Tributos aproximados (Lei 12.741/2012)</span><span style="<?= $c['val'] ?>"><?= e(moeda($nota['valor_tributos'])) ?></span></td>
        </tr>
    </table>

    <p style="font-size:10px;color:#7A7268;line-height:1.5;margin:16px 0 0;">
        Informações complementares: documento emitido em ambiente de demonstração da loja Lumière. Não substitui a NF-e autorizada pela Secretaria da Fazenda.
        Consulte seu pedido em <a href="<?= e($linkPedido) ?>" style="color:#8A5A2B;"><?= e($linkPedido) ?></a>.
    </p>
</div>

<?php if (!$paraEmail): ?>
<?php if (!empty($_GET['imprimir'])): ?><script>window.addEventListener('load', () => window.print());</script><?php endif; ?>
</body>
</html>
<?php endif; ?>
