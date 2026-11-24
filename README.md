# Lumière Velas Aromáticas: loja virtual

Site da Lumière refeito do zero sobre a estrutura do antigo "Lumière site teste", com a mesma linguagem: **PHP, HTML, CSS, JavaScript e SQL (MySQL)**, sem framework. Agora é uma loja completa: vitrine com 20 velas, carrinho, pagamento por cartão de crédito, débito ou PIX, nota fiscal enviada por e-mail e painel só para a administradora.

## Como rodar no XAMPP

1. Copie a pasta `lumiere` para `C:\xampp\htdocs\` (no Mac: `/Applications/XAMPP/htdocs/`).
2. Abra o painel do XAMPP e ligue **Apache** e **MySQL**.
3. Acesse `http://localhost/phpmyadmin`, clique na aba **Importar**, escolha o arquivo `database/lumiere.sql` e clique em **Importar** (ou **Executar**).
   O arquivo cria o banco `lumiere` com todas as tabelas, as 8 famílias olfativas, as 20 velas e a conta da administradora.
   Importar de novo apaga os pedidos e clientes existentes.
4. Abra `http://localhost/lumiere`.

O site usa o usuário `root` sem senha, que é o padrão do XAMPP. Se o seu MySQL tiver senha, ajuste `db_usuario` e `db_senha` em `config/config.php`.
Requisitos: PHP 8.1 ou mais novo (o XAMPP atual já vem com PHP 8) e MySQL ou MariaDB.

## Acesso da administradora

| Campo  | Valor                   |
|--------|-------------------------|
| E-mail | ThyssaAdmin@gmail.com   |
| Senha  | ThyssaLU3               |

Entre por **Entrar** (ícone de pessoa no topo) ou direto em `http://localhost/lumiere/index.php?controller=auth&action=form`.
A senha fica guardada no banco só como hash (bcrypt). Troque-a em **Painel > Acesso > Trocar minha senha** assim que o site for para a internet.
Clientes comuns que criarem conta nunca enxergam o painel: todas as páginas administrativas verificam o perfil no servidor.

## O que tem no site

**Loja**
- Hero igual à referência: letreiro gigante "LUMIERE", vela âmbar recortada à frente das letras e o bloco "O calor em cada detalhe".
- 20 velas aromáticas, cada uma com notas de topo, coração e fundo, descrição da essência e "quando usar". A primeira frase de cada uma aparece no cartão da vitrine.
- Vitrine com filtros por família olfativa, momento (relaxar, concentrar, romance, receber, energizar, aconchego), preço, estoque e promoção; ordenação e busca com sugestões enquanto digita.
- Cartões no estilo da referência: coração de favoritos, selos "Novo" e de desconto, preço no PIX e botão de sacola.
- Carrinho em gaveta lateral: a vela "voa" até o ícone do carrinho, a gaveta atualiza sem recarregar e mostra quanto falta para o frete grátis.
- Favoritos guardados no navegador (e no banco para quem tem conta).
- Checkout em três etapas com máscaras, CEP que preenche o endereço sozinho (ViaCEP), prévia animada do cartão e parcelamento em até 6x.
- Confirmação com QR Code PIX real (padrão do Banco Central) e código copia e cola; a página confere o pagamento sozinha.
- "Meus pedidos" para o cliente acompanhar status e abrir as notas.

**Efeitos**
- Rolagem suave (Lenis) e animações com GSAP + ScrollTrigger.
- Letras do hero sobem uma a uma; letreiro, vela e texto se movem em velocidades diferentes (parallax).
- Faixa de frases que acelera com a velocidade da rolagem.
- Títulos revelados por máscara, fotos e vídeos com parallax.
- Seção "Inspiração" fixa na tela: o vídeo abre até ocupar a tela inteira e a frase acende palavra por palavra conforme a rolagem.
- Abas "Escolha pelo momento" com indicador deslizante e troca animada dos produtos; menu "Coleções" que desce com as famílias.
- Quem ativa "reduzir movimento" no sistema vê tudo sem animação.

**Painel administrativo** (azul-noite, rosa e dourado, como nas referências DASHBOARD)
- Dashboard com faturamento do mês e variação, pedidos, ticket médio, PIX pendentes, gráfico de 30 dias, formas de pagamento, mais vendidas e estoque baixo.
- Pedidos com filtros, detalhe completo e mudança de status (confirmar pagamento, enviado, entregue, cancelar devolvendo o estoque).
- Notas fiscais: lista, visualização e reenvio por e-mail.
- Produtos (cadastro com upload de foto ou endereço de imagem), famílias olfativas, entradas de estoque, clientes, mensagens do contato, relatório por período e contas de acesso.

## Pagamentos: modo simulação

O arquivo `config/config.php` vem com `'pagamento_modo' => 'simulacao'`. **Nenhum valor é cobrado.** O site avisa isso no checkout e no rodapé.

| Para testar           | Use                                                     |
|-----------------------|---------------------------------------------------------|
| Cartão aprovado       | 4111 1111 1111 1111, qualquer validade futura, CVV 123  |
| Cartão recusado       | 4000 0000 0000 0002                                     |
| PIX                   | Escolha PIX e, na confirmação, clique em "Simular pagamento do PIX" |

O número completo do cartão e o CVV **nunca** são gravados: o banco guarda só a bandeira e os 4 últimos números.

Para cobrar de verdade é preciso contratar um intermediador de pagamento (Mercado Pago, PagBank, Asaas, Stripe etc.), usar o SDK dele no `CheckoutController` e receber a confirmação por webhook, chamando `Pedido::marcarPago()` e `NotaFiscal::emitirEEnviar()` (o mesmo que o botão de simulação faz hoje). Troque também a chave PIX em `pix_chave`.

## Nota fiscal por e-mail (Gmail)

Assim que um pedido é pago, a loja gera a nota (número sequencial, série 1, chave de acesso de 44 dígitos e tributos aproximados) e envia para o e-mail do comprador.

**Importante:** é uma nota de **demonstração, sem valor fiscal**. Uma NF-e válida precisa ser autorizada pela SEFAZ, com certificado digital da empresa. Para isso, integre um emissor (Focus NFe, eNotas, NFE.io, Bling etc.) no método `NotaFiscal::emitir()`.

Para o envio pelo Gmail funcionar:

1. Na conta Google da loja (ThyssaAdmin@gmail.com), ative a **Verificação em duas etapas**.
2. Em **Conta Google > Segurança > Senhas de app**, crie uma senha de app (16 letras).
3. Abra `config/config.php` e coloque essa senha em `'smtp_senha'`. **Não use a senha normal da conta**: o Google bloqueia login por SMTP com ela.

Enquanto `smtp_senha` estiver vazio, a nota continua sendo gerada, o cliente vê a nota na tela e uma cópia do e-mail fica em `storage/emails/` para você conferir. O painel mostra quais notas ainda não foram enviadas e tem o botão "Reenviar por e-mail".

## Fotos e vídeos

- **Fotos das velas e das famílias:** Unsplash (licença Unsplash, uso comercial gratuito), carregadas direto do servidor do Unsplash. Para usar fotos próprias, envie pelo painel em **Produtos > Editar > Foto**.
- **Vídeos:** Pexels (licença Pexels, uso gratuito), carregados direto do Pexels.
- **Foto e recorte do hero, logo, emblema, vídeo vertical e fotos das modelos:** arquivos da pasta "FOTOS PARA O LUMIERE" da própria marca.

Como as fotos do Unsplash e os vídeos do Pexels vêm da internet, o computador precisa estar conectado para que apareçam.

## O que mudou em relação ao site antigo

- Banco trocado de SQLite para **MySQL**, importável no phpMyAdmin.
- Módulos de "interesse", chat e atendimento substituídos por carrinho, checkout, pedidos e notas fiscais.
- Design novo inteiro (paleta linho, âmbar e azul-noite; Bodoni Moda e Hanken Grotesk, fontes locais).
- Mantidos: roteamento `index.php?controller=&action=`, proteção CSRF em todo formulário, senhas com hash, bloqueio após tentativas de login erradas, validação de upload pelo conteúdo do arquivo e escape de tudo que aparece na tela.

## Estrutura das pastas

```
lumiere/
├── index.php            entrada única e rotas
├── config/              config.php (ajustes), db.php (conexão), helpers.php
├── controllers/         loja (Home, Catalogo, Carrinho, Checkout, Pedido, Nota...) e painel
├── models/              Produto, Carrinho, Pedido, NotaFiscal, Metricas, Usuario...
├── views/               layouts, partes, páginas públicas, painel e a nota fiscal
├── lib/                 Pix.php (BR Code), Mailer.php e PHPMailer
├── public/assets/       css, js (GSAP, ScrollTrigger, Lenis, Chart.js, QR Code), fontes, imagens, vídeos
├── uploads/             fotos da marca e fotos enviadas pelo painel
├── storage/emails/      cópias dos e-mails quando o Gmail não está configurado
└── database/lumiere.sql banco completo para importar
```
