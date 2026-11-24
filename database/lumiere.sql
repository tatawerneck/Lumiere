-- =====================================================================
-- Lumière Velas Aromáticas: banco de dados MySQL / MariaDB
-- Importe este arquivo no phpMyAdmin do XAMPP (aba "Importar").
-- Ele cria o banco `lumiere`, todas as tabelas, as 8 famílias olfativas,
-- as 20 velas e o acesso de administrador.
-- ATENÇÃO: importar de novo APAGA os dados existentes (pedidos, clientes etc.).
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET time_zone = '-03:00';

CREATE DATABASE IF NOT EXISTS `lumiere` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `lumiere`;

DROP TABLE IF EXISTS `nota_fiscal`;
DROP TABLE IF EXISTS `pedido_item`;
DROP TABLE IF EXISTS `pedido`;
DROP TABLE IF EXISTS `entrada`;
DROP TABLE IF EXISTS `favorito`;
DROP TABLE IF EXISTS `produto`;
DROP TABLE IF EXISTS `categoria`;
DROP TABLE IF EXISTS `contato`;
DROP TABLE IF EXISTS `newsletter`;
DROP TABLE IF EXISTS `usuario`;

-- ---------------------------------------------------------------------
-- Contas: clientes e administradores. Senha sempre em hash (password_hash).
-- ---------------------------------------------------------------------
CREATE TABLE `usuario` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(80) NOT NULL,
  `email` VARCHAR(120) NOT NULL,
  `senha` VARCHAR(255) NOT NULL,
  `perfil` ENUM('cliente','admin') NOT NULL DEFAULT 'cliente',
  `cpf` CHAR(11) NULL,
  `telefone` VARCHAR(20) NULL,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_usuario_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Famílias olfativas (categorias do catálogo)
-- ---------------------------------------------------------------------
CREATE TABLE `categoria` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(60) NOT NULL,
  `slug` VARCHAR(70) NOT NULL,
  `descricao` VARCHAR(255) NULL,
  `imagem_url` VARCHAR(500) NULL,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_categoria_nome` (`nome`),
  UNIQUE KEY `uniq_categoria_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Velas. "momentos" guarda os momentos de uso (abas da home e filtros).
-- imagem_ext: foto enviada pelo painel (uploads/produtos/ID.ext).
-- imagem_url / imagem2_url: fotos externas usadas quando não há upload.
-- ---------------------------------------------------------------------
CREATE TABLE `produto` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `categoria_id` INT UNSIGNED NOT NULL,
  `nome` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL,
  `notas_topo` VARCHAR(150) NULL,
  `notas_coracao` VARCHAR(150) NULL,
  `notas_fundo` VARCHAR(150) NULL,
  `descricao` TEXT NULL,
  `quando_usar` TEXT NULL,
  `momentos` SET('relaxar','concentrar','romantico','receber','energizar','aconchego') NOT NULL DEFAULT '',
  `peso_g` SMALLINT UNSIGNED NOT NULL DEFAULT 200,
  `queima_h` SMALLINT UNSIGNED NOT NULL DEFAULT 45,
  `preco` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `preco_antigo` DECIMAL(10,2) NULL,
  `estoque` INT UNSIGNED NOT NULL DEFAULT 0,
  `destaque` TINYINT(1) NOT NULL DEFAULT 0,
  `novo` TINYINT(1) NOT NULL DEFAULT 0,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `imagem_ext` VARCHAR(5) NULL,
  `imagem_url` VARCHAR(500) NULL,
  `imagem2_url` VARCHAR(500) NULL,
  `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_produto_slug` (`slug`),
  KEY `idx_produto_categoria` (`categoria_id`),
  CONSTRAINT `fk_produto_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Entradas de estoque lançadas pelo painel
-- ---------------------------------------------------------------------
CREATE TABLE `entrada` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `produto_id` INT UNSIGNED NOT NULL,
  `quantidade` INT UNSIGNED NOT NULL,
  `custo_unitario` DECIMAL(10,2) NULL,
  `data` DATE NOT NULL,
  `observacao` VARCHAR(255) NULL,
  `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_entrada_produto` (`produto_id`),
  CONSTRAINT `fk_entrada_produto` FOREIGN KEY (`produto_id`) REFERENCES `produto` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Lista de desejos de clientes logados
-- ---------------------------------------------------------------------
CREATE TABLE `favorito` (
  `usuario_id` INT UNSIGNED NOT NULL,
  `produto_id` INT UNSIGNED NOT NULL,
  `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`usuario_id`,`produto_id`),
  CONSTRAINT `fk_favorito_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_favorito_produto` FOREIGN KEY (`produto_id`) REFERENCES `produto` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Pedidos. O cartão NUNCA é salvo: só a bandeira e os 4 últimos dígitos.
-- ---------------------------------------------------------------------
CREATE TABLE `pedido` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `codigo` CHAR(10) NOT NULL,
  `usuario_id` INT UNSIGNED NULL,
  `nome` VARCHAR(80) NOT NULL,
  `email` VARCHAR(120) NOT NULL,
  `cpf` CHAR(11) NOT NULL,
  `telefone` VARCHAR(20) NOT NULL,
  `cep` CHAR(8) NOT NULL,
  `endereco` VARCHAR(150) NOT NULL,
  `numero` VARCHAR(10) NOT NULL,
  `complemento` VARCHAR(60) NULL,
  `bairro` VARCHAR(80) NOT NULL,
  `cidade` VARCHAR(80) NOT NULL,
  `uf` CHAR(2) NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  `desconto` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `frete` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total` DECIMAL(10,2) NOT NULL,
  `pagamento` ENUM('credito','debito','pix') NOT NULL,
  `parcelas` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `cartao_bandeira` VARCHAR(20) NULL,
  `cartao_final` CHAR(4) NULL,
  `status` ENUM('aguardando_pagamento','pago','enviado','entregue','cancelado') NOT NULL DEFAULT 'aguardando_pagamento',
  `pix_txid` VARCHAR(25) NULL,
  `observacao` VARCHAR(255) NULL,
  `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `pago_em` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_pedido_codigo` (`codigo`),
  KEY `idx_pedido_status_data` (`status`,`criado_em`),
  KEY `idx_pedido_usuario` (`usuario_id`),
  CONSTRAINT `fk_pedido_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pedido_item` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pedido_id` INT UNSIGNED NOT NULL,
  `produto_id` INT UNSIGNED NOT NULL,
  `nome` VARCHAR(100) NOT NULL,
  `preco_unitario` DECIMAL(10,2) NOT NULL,
  `quantidade` INT UNSIGNED NOT NULL,
  `total` DECIMAL(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_item_pedido` (`pedido_id`),
  KEY `idx_item_produto` (`produto_id`),
  CONSTRAINT `fk_item_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedido` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_item_produto` FOREIGN KEY (`produto_id`) REFERENCES `produto` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Nota fiscal gerada após o pagamento (modelo de demonstração, sem valor fiscal).
-- ---------------------------------------------------------------------
CREATE TABLE `nota_fiscal` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pedido_id` INT UNSIGNED NOT NULL,
  `numero` INT UNSIGNED NOT NULL,
  `serie` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  `chave_acesso` CHAR(44) NOT NULL,
  `valor_total` DECIMAL(10,2) NOT NULL,
  `valor_tributos` DECIMAL(10,2) NOT NULL,
  `emitida_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `email_enviado` TINYINT(1) NOT NULL DEFAULT 0,
  `email_info` VARCHAR(255) NULL,
  `enviado_em` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_nota_pedido` (`pedido_id`),
  UNIQUE KEY `uniq_nota_numero` (`serie`,`numero`),
  CONSTRAINT `fk_nota_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedido` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `contato` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(80) NOT NULL,
  `email` VARCHAR(120) NOT NULL,
  `mensagem` TEXT NOT NULL,
  `lido` TINYINT(1) NOT NULL DEFAULT 0,
  `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `newsletter` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(120) NOT NULL,
  `criado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_newsletter_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- Administrador
-- E-mail: ThyssaAdmin@gmail.com (guardado em minúsculas; o login não diferencia maiúsculas)
-- Senha: a combinada com a loja, guardada só como hash. Troque depois em Painel > Usuários.
-- ---------------------------------------------------------------------
INSERT INTO `usuario` (`id`,`nome`,`email`,`senha`,`perfil`,`ativo`) VALUES
  (1, 'Thyssa', 'thyssaadmin@gmail.com', '$2y$10$upZ0j3hzkfrhF4edlPJHQeHRCRwDOPUCioZMnKlm8bQLJ.WPBwE.a', 'admin', 1);

-- Famílias olfativas
INSERT INTO `categoria` (`id`,`nome`,`slug`,`descricao`,`imagem_url`,`ativo`) VALUES
  (1, 'Florais', 'florais', 'Rosas, jasmim e flores brancas para dias delicados.', 'https://images.unsplash.com/photo-1777107857717-af7963c676a2?auto=format&fit=crop&w=900&h=1200&q=80', 1),
  (2, 'Amadeiradas', 'amadeiradas', 'Sândalo, cedro e pinho: aromas secos e profundos.', 'https://images.unsplash.com/photo-1781736363359-a9d529d13771?auto=format&fit=crop&w=900&h=1200&q=80', 1),
  (3, 'Cítricas', 'citricas', 'Laranja, limão e capim-limão para despertar a casa.', 'https://images.unsplash.com/photo-1787020546612-eafe4080ce43?auto=format&fit=crop&w=900&h=1200&q=80', 1),
  (4, 'Gourmand', 'gourmand', 'Baunilha, café e especiarias com cheiro de cozinha boa.', 'https://images.unsplash.com/photo-1659580711050-6de37ed4e3ad?auto=format&fit=crop&w=900&h=1200&q=80', 1),
  (5, 'Herbais', 'herbais', 'Lavanda, eucalipto e alecrim, frescos e calmantes.', 'https://images.unsplash.com/photo-1777107857731-16493cc70fdb?auto=format&fit=crop&w=900&h=1200&q=80', 1),
  (6, 'Frutais', 'frutais', 'Pera, figo, coco e frutos vermelhos, suculentos e leves.', 'https://images.unsplash.com/photo-1638885910090-660c91a78084?auto=format&fit=crop&w=900&h=1200&q=80', 1),
  (7, 'Orientais', 'orientais', 'Âmbar, oud e resinas para noites marcantes.', 'https://images.unsplash.com/photo-1668112037360-900ff60ef6f7?auto=format&fit=crop&w=900&h=1200&q=80', 1),
  (8, 'Frescas', 'frescas', 'Algodão e chá branco: o perfume de casa limpa.', 'https://images.unsplash.com/photo-1606367136227-cce801ddce0b?auto=format&fit=crop&w=900&h=1200&q=80', 1);

-- As 20 velas aromáticas
INSERT INTO `produto` (`id`,`categoria_id`,`nome`,`slug`,`notas_topo`,`notas_coracao`,`notas_fundo`,`descricao`,`quando_usar`,`momentos`,`peso_g`,`queima_h`,`preco`,`preco_antigo`,`estoque`,`destaque`,`novo`,`ativo`,`imagem_ext`,`imagem_url`,`imagem2_url`) VALUES
  (1, 5, 'Lavanda Provençal', 'lavanda-provencal', 'lavanda, bergamota', 'alecrim, gerânio', 'almíscar branco, cumaru', 'Lavanda dos campos da Provença com um toque de alecrim. Um aroma limpo e calmante, que deixa o ambiente leve e silencioso.', 'À noite, antes de dormir, ou durante um banho demorado. Acenda meia hora antes de deitar e apague antes de dormir.', 'relaxar', 200, 45, 89.90, NULL, 42, 1, 0, 1, NULL, 'https://images.unsplash.com/photo-1777107857518-151355201542?auto=format&fit=crop&w=900&h=1125&q=80', 'https://images.unsplash.com/photo-1693567398116-da2a0e76c119?auto=format&fit=crop&w=900&h=1125&q=80'),
  (2, 4, 'Baunilha Bourbon', 'baunilha-bourbon', 'baunilha, açúcar mascavo', 'fava tonka, leite', 'âmbar, sândalo', 'Baunilha de Madagascar cremosa, com fundo quente de fava tonka. Doce na medida, sem enjoar.', 'Em noites frias, numa leitura no sofá ou para receber alguém com cheiro de casa acolhedora.', 'aconchego,receber', 200, 45, 89.90, NULL, 55, 1, 0, 1, NULL, 'https://images.unsplash.com/photo-1634306954206-0b07090bf575?auto=format&fit=crop&w=900&h=1125&q=80', 'https://images.unsplash.com/photo-1733954427762-31beca62dd60?auto=format&fit=crop&w=900&h=1125&q=80'),
  (3, 2, 'Sândalo & Cedro', 'sandalo-cedro', 'cardamomo', 'sândalo, cedro', 'vetiver, couro suave', 'Madeiras secas e cremosas com uma pitada de cardamomo. O pavio de madeira estala baixinho, como uma lareira.', 'No escritório, na meditação ou no fim do expediente, para marcar a hora de desacelerar.', 'concentrar,relaxar', 300, 60, 129.90, NULL, 30, 1, 1, 1, NULL, 'https://images.unsplash.com/photo-1603905179139-db12ab535ca9?auto=format&fit=crop&w=900&h=1125&q=80', 'https://images.unsplash.com/photo-1572726729207-a78d6feb18d7?auto=format&fit=crop&w=900&h=1125&q=80'),
  (4, 3, 'Capim-Limão & Gengibre', 'capim-limao-gengibre', 'capim-limão, limão', 'gengibre fresco', 'chá verde', 'Cítrico vibrante com o picante do gengibre. Desperta, limpa o ar e deixa a casa com cara de manhã.', 'De manhã, no home office e na cozinha. Ótima para tirar o cheiro de comida depois do almoço.', 'energizar,concentrar', 200, 45, 79.90, NULL, 48, 0, 0, 1, NULL, 'https://images.unsplash.com/photo-1595055258834-8290e4181590?auto=format&fit=crop&w=900&h=1125&q=80', 'https://images.unsplash.com/photo-1757528649062-787b727bd3fc?auto=format&fit=crop&w=900&h=1125&q=80'),
  (5, 4, 'Canela & Maçã Assada', 'canela-maca-assada', 'maçã vermelha', 'canela, cravo', 'baunilha, noz-moscada', 'Maçã assada com canela saindo do forno. O cheiro de bolo na casa da avó.', 'No outono e no inverno, em tardes de chuva e quando chega visita para o café.', 'aconchego,receber', 200, 45, 94.90, '109.90', 36, 0, 0, 1, NULL, 'https://images.unsplash.com/photo-1604160526882-551c7c24f2cb?auto=format&fit=crop&w=900&h=1125&q=80', 'https://images.unsplash.com/photo-1635887910118-11c5db92e034?auto=format&fit=crop&w=900&h=1125&q=80'),
  (6, 1, 'Rosas & Peônias', 'rosas-peonias', 'lichia, pimenta-rosa', 'rosa damascena, peônia', 'almíscar', 'Um buquê de rosas frescas com peônias e um toque frutado de lichia. Romântica e delicada.', 'Para um jantar a dois, no quarto, numa data especial ou para presentear quem você ama.', 'romantico', 200, 45, 99.90, NULL, 38, 1, 0, 1, NULL, 'https://images.unsplash.com/photo-1745125996161-0607c4d436f7?auto=format&fit=crop&w=900&h=1125&q=80', 'https://images.unsplash.com/photo-1777107857794-0ae7cfb2475f?auto=format&fit=crop&w=900&h=1125&q=80'),
  (7, 5, 'Eucalipto & Hortelã', 'eucalipto-hortela', 'eucalipto, hortelã', 'alecrim', 'cedro branco', 'Fresco e mentolado, lembra spa e ar de montanha. Abre a respiração e renova o ambiente.', 'No banheiro, depois do treino ou em dias abafados, para clarear o ar da casa.', 'energizar,relaxar', 200, 45, 84.90, NULL, 44, 0, 0, 1, NULL, 'https://images.unsplash.com/photo-1612293905607-b003de9e54fb?auto=format&fit=crop&w=900&h=1125&q=80', 'https://images.unsplash.com/photo-1757688525739-8d1e13daf44f?auto=format&fit=crop&w=900&h=1125&q=80'),
  (8, 7, 'Âmbar & Oud', 'ambar-oud', 'açafrão', 'oud, rosa negra', 'âmbar, resinas, patchouli', 'Nosso aroma mais intenso: oud defumado, âmbar dourado e resinas. Sofisticado e marcante.', 'Em noites especiais, jantares com amigos e salas amplas. Pouco tempo acesa já perfuma o ambiente.', 'romantico,receber', 300, 65, 149.90, NULL, 22, 1, 1, 1, NULL, 'https://images.unsplash.com/photo-1668112037360-900ff60ef6f7?auto=format&fit=crop&w=900&h=1125&q=80', 'https://images.unsplash.com/photo-1781736363359-a9d529d13771?auto=format&fit=crop&w=900&h=1125&q=80'),
  (9, 1, 'Jasmim & Flor de Laranjeira', 'jasmim-flor-de-laranjeira', 'neroli', 'jasmim sambac, flor de laranjeira', 'almíscar branco', 'Flores brancas luminosas, como um jardim no fim da tarde. Elegante sem ser pesada.', 'Em tardes na sala, na primavera e para receber visitas com um perfume delicado.', 'receber,romantico', 200, 45, 99.90, NULL, 33, 0, 0, 1, NULL, 'https://images.unsplash.com/photo-1774802804564-367bb84d74ed?auto=format&fit=crop&w=900&h=1125&q=80', 'https://images.unsplash.com/photo-1745125996058-9f0d7d2908db?auto=format&fit=crop&w=900&h=1125&q=80'),
  (10, 6, 'Pera & Frésia', 'pera-fresia', 'pera madura', 'frésia', 'patchouli suave', 'Pera suculenta com frésia branca. Leve, fresca e fácil de gostar.', 'No dia a dia, na sala de estar e no lavabo. Agrada a quase todo mundo.', 'receber,energizar', 200, 45, 104.90, NULL, 29, 0, 1, 1, NULL, 'https://images.unsplash.com/photo-1714228499605-367154d9bdb2?auto=format&fit=crop&w=900&h=1125&q=80', 'https://images.unsplash.com/photo-1636714560735-9f84a7c06d16?auto=format&fit=crop&w=900&h=1125&q=80'),
  (11, 8, 'Algodão Limpo', 'algodao-limpo', 'aldeídos', 'flor de algodão, lírio', 'almíscar branco', 'Cheiro de roupa limpa secando ao sol. Suave, confortável e aconchegante.', 'No quarto, no closet e depois da faxina, para deixar a sensação de casa arrumada.', 'relaxar,aconchego', 200, 45, 79.90, NULL, 51, 0, 0, 1, NULL, 'https://images.unsplash.com/photo-1616172890963-a45e7da8de31?auto=format&fit=crop&w=900&h=1125&q=80', 'https://images.unsplash.com/photo-1758077223826-74f8ec22ebfd?auto=format&fit=crop&w=900&h=1125&q=80'),
  (12, 6, 'Figo & Folhas Verdes', 'figo-folhas-verdes', 'folha de figueira', 'figo, leite de coco', 'cedro', 'Figo maduro com folhas verdes amassadas. Verde, cremoso e diferente de tudo.', 'Para ler na varanda, no verão e no escritório de casa.', 'concentrar,relaxar', 200, 45, 109.90, NULL, 4, 0, 1, 1, NULL, 'https://images.unsplash.com/photo-1774364830980-df7c1b588395?auto=format&fit=crop&w=900&h=1125&q=80', 'https://images.unsplash.com/photo-1611090480455-fc0ea8ef5792?auto=format&fit=crop&w=900&h=1125&q=80'),
  (13, 4, 'Café & Cacau', 'cafe-cacau', 'café torrado', 'cacau, avelã', 'baunilha, caramelo', 'Café passado na hora com cacau e avelã. Encorpado e reconfortante.', 'Em manhãs de trabalho, horas de estudo e tardes frias com um livro.', 'concentrar,aconchego', 200, 45, 89.90, NULL, 40, 0, 0, 1, NULL, 'https://images.unsplash.com/photo-1756809346745-dd928dbf50c1?auto=format&fit=crop&w=900&h=1125&q=80', 'https://images.unsplash.com/photo-1634391922288-1a8e9661ee18?auto=format&fit=crop&w=900&h=1125&q=80'),
  (14, 8, 'Chá Branco & Bambu', 'cha-branco-bambu', 'chá branco', 'bambu, gengibre', 'almíscar', 'Chá branco e bambu: um aroma zen, limpo e silencioso.', 'Na yoga, na meditação e num fim de tarde de spa em casa.', 'relaxar', 200, 45, 94.90, NULL, 35, 0, 0, 1, NULL, 'https://images.unsplash.com/photo-1658915304996-7784292b22e9?auto=format&fit=crop&w=900&h=1125&q=80', 'https://images.unsplash.com/photo-1640095889747-2090ee12fa7d?auto=format&fit=crop&w=900&h=1125&q=80'),
  (15, 4, 'Vinho Quente', 'vinho-quente', 'vinho tinto, laranja', 'cravo, canela', 'açúcar queimado', 'Vinho tinto com laranja, cravo e canela, como o quentão das festas de julho.', 'Em festas juninas, noites frias e encontros com amigos.', 'receber,aconchego', 200, 45, 89.90, '99.90', 27, 0, 0, 1, NULL, 'https://images.unsplash.com/photo-1671118417981-59bbf11aac10?auto=format&fit=crop&w=900&h=1125&q=80', 'https://images.unsplash.com/photo-1766185670871-18c5d57de258?auto=format&fit=crop&w=900&h=1125&q=80'),
  (16, 2, 'Pinho & Floresta', 'pinho-floresta', 'pinho, abeto', 'musgo de carvalho', 'cedro, resina', 'Agulhas de pinho e madeira úmida de chuva. A floresta dentro de casa.', 'No Natal, no inverno e na sala de estar, quando a família se reúne.', 'aconchego,receber', 200, 45, 99.90, NULL, 46, 0, 1, 1, NULL, 'https://images.unsplash.com/photo-1506439904482-f72abad7af25?auto=format&fit=crop&w=900&h=1125&q=80', 'https://images.unsplash.com/photo-1766145465923-e72a6f4fd52a?auto=format&fit=crop&w=900&h=1125&q=80'),
  (17, 3, 'Laranja Doce & Verbena', 'laranja-doce-verbena', 'laranja doce, mandarina', 'verbena', 'manjericão, cedro', 'Laranja doce espremida com verbena. Alegre, solar e cheia de energia.', 'Na cozinha, de manhã e nos dias em que você precisa de ânimo.', 'energizar', 200, 45, 79.90, NULL, 39, 0, 0, 1, NULL, 'https://images.unsplash.com/photo-1787020546612-eafe4080ce43?auto=format&fit=crop&w=900&h=1125&q=80', 'https://images.unsplash.com/photo-1625055887171-4a3186a42b39?auto=format&fit=crop&w=900&h=1125&q=80'),
  (18, 6, 'Coco & Baunilha', 'coco-baunilha', 'coco', 'flor de tiaré', 'baunilha', 'Coco cremoso com flor de tiaré e baunilha. Cheiro de férias na praia.', 'No verão, depois do banho e em fins de semana de descanso.', 'relaxar,aconchego', 200, 45, 84.90, NULL, 31, 0, 0, 1, NULL, 'https://images.unsplash.com/photo-1611090478524-67b7ce1fb315?auto=format&fit=crop&w=900&h=1125&q=80', 'https://images.unsplash.com/photo-1759262151253-375c311347c7?auto=format&fit=crop&w=900&h=1125&q=80'),
  (19, 5, 'Citronela & Alecrim', 'citronela-alecrim', 'citronela, limão', 'alecrim', 'eucalipto', 'Herbal e cítrica, com citronela e alecrim. Fresca e perfeita para áreas abertas.', 'Na varanda, no quintal e em noites de verão ao ar livre.', 'energizar,receber', 200, 45, 74.90, NULL, 50, 0, 0, 1, NULL, 'https://images.unsplash.com/photo-1772915022472-ffc1df2d6051?auto=format&fit=crop&w=900&h=1125&q=80', 'https://images.unsplash.com/photo-1698936082463-0d26856dc7b8?auto=format&fit=crop&w=900&h=1125&q=80'),
  (20, 6, 'Frutos Vermelhos', 'frutos-vermelhos', 'morango, framboesa', 'amora, cassis', 'baunilha', 'Morango, framboesa e amora: doce, suculenta e alegre.', 'Em festas, para receber amigos e para presentear.', 'receber,romantico', 200, 45, 84.90, NULL, 37, 1, 0, 1, NULL, 'https://images.unsplash.com/photo-1638885909761-134371bc53d6?auto=format&fit=crop&w=900&h=1125&q=80', 'https://images.unsplash.com/photo-1638885910090-660c91a78084?auto=format&fit=crop&w=900&h=1125&q=80');

-- Entrada inicial de estoque (histórico para o painel)
INSERT INTO `entrada` (`produto_id`,`quantidade`,`custo_unitario`,`data`,`observacao`) VALUES
  (1, 42, 34.16, CURDATE(), 'Estoque inicial'),
  (2, 55, 34.16, CURDATE(), 'Estoque inicial'),
  (3, 30, 49.36, CURDATE(), 'Estoque inicial'),
  (4, 48, 30.36, CURDATE(), 'Estoque inicial'),
  (5, 36, 36.06, CURDATE(), 'Estoque inicial'),
  (6, 38, 37.96, CURDATE(), 'Estoque inicial'),
  (7, 44, 32.26, CURDATE(), 'Estoque inicial'),
  (8, 22, 56.96, CURDATE(), 'Estoque inicial'),
  (9, 33, 37.96, CURDATE(), 'Estoque inicial'),
  (10, 29, 39.86, CURDATE(), 'Estoque inicial'),
  (11, 51, 30.36, CURDATE(), 'Estoque inicial'),
  (12, 4, 41.76, CURDATE(), 'Estoque inicial'),
  (13, 40, 34.16, CURDATE(), 'Estoque inicial'),
  (14, 35, 36.06, CURDATE(), 'Estoque inicial'),
  (15, 27, 34.16, CURDATE(), 'Estoque inicial'),
  (16, 46, 37.96, CURDATE(), 'Estoque inicial'),
  (17, 39, 30.36, CURDATE(), 'Estoque inicial'),
  (18, 31, 32.26, CURDATE(), 'Estoque inicial'),
  (19, 50, 28.46, CURDATE(), 'Estoque inicial'),
  (20, 37, 32.26, CURDATE(), 'Estoque inicial');

SET FOREIGN_KEY_CHECKS = 1;
