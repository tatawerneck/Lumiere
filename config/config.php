<?php
// Configurações gerais da Lumière. Os valores padrão funcionam no XAMPP sem mudar nada.
// Em produção, prefira definir os valores sensíveis por variáveis de ambiente.
return [
    'app_nome'  => 'Lumière Velas Aromáticas',
    'app_url'   => getenv('LUMIERE_URL') ?: '',   // ex.: https://www.lumiere.com.br (usado nos links do e-mail). Vazio = detecta sozinho.

    // ---------- Banco de dados MySQL (XAMPP: usuário root, sem senha) ----------
    'db_host'   => getenv('LUMIERE_DB_HOST') ?: '127.0.0.1',
    'db_porta'  => (int)(getenv('LUMIERE_DB_PORTA') ?: 3306),
    'db_nome'   => getenv('LUMIERE_DB_NOME') ?: 'lumiere',
    'db_usuario'=> getenv('LUMIERE_DB_USUARIO') ?: 'root',
    'db_senha'  => getenv('LUMIERE_DB_SENHA') ?: '',

    // ---------- Loja ----------
    'frete_gratis_acima' => 199.00,   // pedidos a partir deste valor não pagam frete
    'frete_fixo'         => 19.90,    // frete para pedidos abaixo do valor acima
    'pix_desconto'       => 0.05,     // 5% de desconto no PIX
    'parcelas_max'       => 6,        // parcelamento sem juros no crédito
    'parcela_minima'     => 30.00,    // valor mínimo de cada parcela
    'estoque_baixo'      => 5,        // alerta no painel

    // ---------- Pagamento ----------
    // 'simulacao': nenhum valor é cobrado. Cartões são validados (número, validade, CVV) e aprovados na hora;
    // o PIX gera um QR Code real no formato do Banco Central e pode ser confirmado pelo botão de simulação
    // ou pelo painel. Para cobrar de verdade, integre um gateway (Mercado Pago, PagBank, Stripe etc.).
    'pagamento_modo' => 'simulacao',
    'pix_chave'      => getenv('LUMIERE_PIX_CHAVE') ?: 'thyssaadmin@gmail.com', // troque pela chave PIX da loja
    'pix_nome'       => 'LUMIERE VELAS',      // até 25 caracteres, sem acento
    'pix_cidade'     => 'SAO PAULO',          // até 15 caracteres, sem acento

    // ---------- Dados do emitente (aparecem na nota fiscal) ----------
    // ATENÇÃO: os valores abaixo são de exemplo. Troque pelos dados reais da empresa.
    'emitente' => [
        'razao_social' => 'Lumière Velas Aromáticas',
        'cnpj'         => '00.000.000/0001-00',      // preencha com o CNPJ da loja
        'ie'           => 'ISENTO',
        'endereco'     => 'Rua das Velas, 100, Centro',
        'cidade_uf'    => 'São Paulo - SP',
        'cep'          => '01000-000',
        'telefone'     => '(11) 0000-0000',
    ],
    'aliquota_tributos' => 0.3245,   // carga tributária aproximada (Lei 12.741/2012), só informativa

    // ---------- E-mail (Gmail via SMTP) ----------
    // Para o Gmail enviar, use uma "senha de app" (Conta Google > Segurança > Verificação em duas etapas > Senhas de app).
    // A senha normal da conta NÃO funciona por SMTP. Enquanto smtp_senha estiver vazio, os e-mails são salvos
    // em storage/emails/ (dá para abrir no navegador) e o painel avisa que o envio não foi feito.
    'smtp_host'       => 'smtp.gmail.com',
    'smtp_porta'      => 587,
    'smtp_usuario'    => getenv('LUMIERE_SMTP_USUARIO') ?: 'thyssaadmin@gmail.com',
    'smtp_senha'      => getenv('LUMIERE_SMTP_SENHA') ?: '',
    'email_remetente' => 'thyssaadmin@gmail.com',
    'email_nome'      => 'Lumière Velas Aromáticas',

    // ---------- Segurança e listas ----------
    'upload_max_bytes'     => 3 * 1024 * 1024,
    'login_max_tentativas' => 5,
    'login_bloqueio_seg'   => 300,
    'por_pagina'           => 12,
];
