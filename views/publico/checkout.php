<?php
// Finalizar compra. Recebe $resumo, $conta (usuário logado ou null) e $ufs.
$v = fn(string $campo, string $padrao = '') => (string)old($campo, $padrao);
$nomePadrao  = $conta['nome'] ?? '';
$emailPadrao = $conta['email'] ?? '';
$cpfPadrao   = !empty($conta['cpf']) ? cpf_formatado($conta['cpf']) : '';
$telPadrao   = $conta['telefone'] ?? '';
$pagamento   = $v('pagamento', 'credito');
$totalCartao = (float)$resumo['total'];
$maxParcelas = max(1, min((int)config('parcelas_max'), (int)floor($totalCartao / (float)config('parcela_minima'))));
$pixDesconto = round($resumo['subtotal'] * (float)config('pix_desconto'), 2);
?>
<section class="wrap pagina-cabeca">
    <ol class="trilha" aria-label="Você está em">
        <li><a href="index.php">Início</a></li>
        <li><a href="<?= e(url('carrinho')) ?>">Carrinho</a></li>
        <li aria-current="page">Finalizar compra</li>
    </ol>
    <h1>Finalizar compra</h1>
</section>

<div class="wrap">
    <div class="aviso-demo" role="note">
        <?= icone('escudo', 22) ?>
        <p><strong>Loja em modo de demonstração.</strong> Nenhum valor é cobrado de verdade. Para testar, use o cartão <strong>4111 1111 1111 1111</strong> (aprovado) ou <strong>4000 0000 0000 0002</strong> (recusado), com qualquer validade futura e CVV de 3 números. No PIX, um botão simula o pagamento. A nota fiscal gerada não tem valor fiscal.</p>
    </div>
</div>

<form class="wrap checkout" method="post" action="<?= e(url('checkout', 'finalizar')) ?>" novalidate data-checkout
      data-subtotal="<?= e(number_format($resumo['subtotal'], 2, '.', '')) ?>"
      data-frete="<?= e(number_format($resumo['frete'], 2, '.', '')) ?>"
      data-pix-desconto="<?= e(number_format($pixDesconto, 2, '.', '')) ?>">
    <?= csrf_campo() ?>

    <div>
        <fieldset class="etapa">
            <legend><span>1</span> Seus dados</legend>
            <?php if (!$conta): ?>
                <p class="campo-ajuda" style="clear: both; margin-bottom: 14px">Já comprou com a gente? <a href="<?= e(url('auth', 'form')) ?>">Entre na sua conta</a> para preencher mais rápido e acompanhar o pedido.</p>
            <?php endif; ?>
            <div class="grade-campos">
                <div class="campo c6">
                    <label for="ck-nome">Nome completo</label>
                    <input id="ck-nome" name="nome" autocomplete="name" maxlength="80" required value="<?= e($v('nome', $nomePadrao)) ?>">
                </div>
                <div class="campo c3">
                    <label for="ck-email">E-mail</label>
                    <input id="ck-email" type="email" name="email" autocomplete="email" maxlength="120" required value="<?= e($v('email', $emailPadrao)) ?>">
                    <span class="campo-ajuda">A nota fiscal é enviada para este e-mail.</span>
                </div>
                <div class="campo c3">
                    <label for="ck-tel">Celular com DDD</label>
                    <input id="ck-tel" type="tel" name="telefone" autocomplete="tel-national" inputmode="numeric" maxlength="15" required value="<?= e($v('telefone', $telPadrao)) ?>" data-mascara="telefone">
                </div>
                <div class="campo c3">
                    <label for="ck-cpf">CPF</label>
                    <input id="ck-cpf" name="cpf" inputmode="numeric" maxlength="14" required value="<?= e($v('cpf', $cpfPadrao)) ?>" data-mascara="cpf">
                    <span class="campo-ajuda">Obrigatório para emitir a nota fiscal.</span>
                </div>
            </div>
        </fieldset>

        <fieldset class="etapa">
            <legend><span>2</span> Entrega</legend>
            <div class="grade-campos">
                <div class="campo c2">
                    <label for="ck-cep">CEP</label>
                    <input id="ck-cep" name="cep" inputmode="numeric" autocomplete="postal-code" maxlength="9" required value="<?= e($v('cep')) ?>" data-mascara="cep" data-cep>
                    <span class="campo-ajuda" data-cep-status aria-live="polite">Preenchemos o endereço para você.</span>
                </div>
                <div class="campo c4">
                    <label for="ck-end">Rua ou avenida</label>
                    <input id="ck-end" name="endereco" autocomplete="address-line1" maxlength="150" required value="<?= e($v('endereco')) ?>">
                </div>
                <div class="campo c2">
                    <label for="ck-num">Número</label>
                    <input id="ck-num" name="numero" maxlength="10" required value="<?= e($v('numero')) ?>">
                </div>
                <div class="campo c4">
                    <label for="ck-comp">Complemento <span class="campo-ajuda">(opcional)</span></label>
                    <input id="ck-comp" name="complemento" autocomplete="address-line2" maxlength="60" value="<?= e($v('complemento')) ?>">
                </div>
                <div class="campo c2">
                    <label for="ck-bairro">Bairro</label>
                    <input id="ck-bairro" name="bairro" maxlength="80" required value="<?= e($v('bairro')) ?>">
                </div>
                <div class="campo c3">
                    <label for="ck-cidade">Cidade</label>
                    <input id="ck-cidade" name="cidade" autocomplete="address-level2" maxlength="80" required value="<?= e($v('cidade')) ?>">
                </div>
                <div class="campo c1">
                    <label for="ck-uf">UF</label>
                    <select id="ck-uf" name="uf" required autocomplete="address-level1">
                        <option value="">--</option>
                        <?php foreach ($ufs as $uf): ?>
                            <option <?= $v('uf') === $uf ? 'selected' : '' ?>><?= e($uf) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </fieldset>

        <fieldset class="etapa">
            <legend><span>3</span> Pagamento</legend>
            <div class="formas" role="radiogroup" aria-label="Forma de pagamento">
                <div class="forma">
                    <input type="radio" id="pg-credito" name="pagamento" value="credito" <?= $pagamento === 'credito' ? 'checked' : '' ?> data-forma>
                    <label for="pg-credito"><?= icone('cartao', 26) ?><span>Cartão de crédito<br><small>até <?= $maxParcelas ?>x sem juros</small></span></label>
                </div>
                <div class="forma">
                    <input type="radio" id="pg-debito" name="pagamento" value="debito" <?= $pagamento === 'debito' ? 'checked' : '' ?> data-forma>
                    <label for="pg-debito"><?= icone('cartao', 26) ?><span>Cartão de débito<br><small>à vista</small></span></label>
                </div>
                <div class="forma">
                    <input type="radio" id="pg-pix" name="pagamento" value="pix" <?= $pagamento === 'pix' ? 'checked' : '' ?> data-forma>
                    <label for="pg-pix"><?= icone('pix', 26) ?><span>PIX<br><small>5% de desconto</small></span></label>
                </div>
            </div>

            <div class="painel-pagamento" data-painel-cartao <?= $pagamento === 'pix' ? 'hidden' : '' ?>>
                <div class="cartao-area">
                    <div class="grade-campos">
                        <div class="campo c6">
                            <label for="ck-cartao">Número do cartão</label>
                            <input id="ck-cartao" name="cartao_numero" inputmode="numeric" autocomplete="cc-number" maxlength="23" data-mascara="cartao" data-cartao-num>
                        </div>
                        <div class="campo c6">
                            <label for="ck-titular">Nome impresso no cartão</label>
                            <input id="ck-titular" name="cartao_nome" autocomplete="cc-name" maxlength="60" data-cartao-nome style="text-transform: uppercase">
                        </div>
                        <div class="campo c3">
                            <label for="ck-validade">Validade</label>
                            <input id="ck-validade" name="cartao_validade" inputmode="numeric" autocomplete="cc-exp" placeholder="MM/AA" maxlength="5" data-mascara="validade" data-cartao-val>
                        </div>
                        <div class="campo c3">
                            <label for="ck-cvv">CVV</label>
                            <input id="ck-cvv" name="cartao_cvv" inputmode="numeric" autocomplete="cc-csc" maxlength="4" data-mascara="digitos" data-cartao-cvv>
                        </div>
                        <div class="campo c6" data-parcelas-campo <?= $pagamento === 'debito' ? 'hidden' : '' ?>>
                            <label for="ck-parcelas">Parcelas</label>
                            <select id="ck-parcelas" name="parcelas">
                                <?php for ($n = 1; $n <= $maxParcelas; $n++): ?>
                                    <option value="<?= $n ?>"><?= $n ?>x de <?= e(moeda($totalCartao / $n)) ?> sem juros</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <div class="cartao-visual" aria-hidden="true">
                        <div class="cartao-face <?= $pagamento === 'debito' ? 'debito' : '' ?>" data-cartao-face>
                            <div class="cartao-topo"><span data-cartao-tipo><?= $pagamento === 'debito' ? 'DÉBITO' : 'CRÉDITO' ?></span><span class="cartao-bandeira" data-cartao-bandeira>LUMIÈRE</span></div>
                            <div class="cartao-chip"></div>
                            <div class="cartao-numero" data-cartao-ver-num>•••• •••• •••• ••••</div>
                            <div class="cartao-baixo"><span data-cartao-ver-nome>NOME DO TITULAR</span><span data-cartao-ver-val>MM/AA</span></div>
                        </div>
                        <p class="cartao-teste">Testando a loja? <button type="button" data-cartao-teste>Preencher cartão de teste</button></p>
                    </div>
                </div>
            </div>

            <div class="painel-pagamento" data-painel-pix <?= $pagamento !== 'pix' ? 'hidden' : '' ?>>
                <div class="pix-explica">
                    <?= icone('pix', 28) ?>
                    <div>
                        <p><strong>Você ganha <?= e(moeda($pixDesconto)) ?> de desconto.</strong></p>
                        <p>Ao confirmar, mostramos o QR Code e o código copia e cola. Assim que o pagamento cair, a nota fiscal é gerada e enviada para o seu e-mail.</p>
                    </div>
                </div>
            </div>
        </fieldset>
    </div>

    <aside class="caixa caixa-sticky resumo-pedido" aria-labelledby="ck-resumo">
        <h2 id="ck-resumo">Seu pedido</h2>
        <ul class="gaveta-lista">
            <?php foreach ($resumo['itens'] as $item): $p = $item['produto']; ?>
                <li class="linha-item">
                    <span class="linha-foto"><img src="<?= e(imagemTamanho(imagemProduto($p), 120, 150)) ?>" alt="" width="60" height="75"></span>
                    <div>
                        <p class="linha-nome"><?= e($p['nome']) ?></p>
                        <p class="qtd-mini"><?= (int)$item['quantidade'] ?> × <?= e(moeda($p['preco'])) ?></p>
                    </div>
                    <p class="linha-subtotal"><?= e(moeda($item['subtotal'])) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
        <dl class="resumo-linhas">
            <div><dt>Subtotal</dt><dd><?= e(moeda($resumo['subtotal'])) ?></dd></div>
            <div data-linha-desconto <?= $pagamento !== 'pix' ? 'hidden' : '' ?>><dt>Desconto PIX</dt><dd>− <?= e(moeda($pixDesconto)) ?></dd></div>
            <div><dt>Frete</dt><dd><?= $resumo['frete'] > 0 ? e(moeda($resumo['frete'])) : 'Grátis' ?></dd></div>
            <div class="resumo-total"><dt>Total</dt><dd data-total><?= e(moeda($pagamento === 'pix' ? $resumo['total_pix'] : $resumo['total'])) ?></dd></div>
        </dl>
        <button class="botao botao-bloco botao-grande botao-finalizar" type="submit" data-finalizar>
            <span data-finalizar-txt><?= $pagamento === 'pix' ? 'Gerar PIX' : 'Pagar agora' ?></span>
            <span class="girando" aria-hidden="true" hidden></span>
        </button>
        <ul class="selos-confianca">
            <li><?= icone('escudo', 18) ?> O número completo do cartão e o CVV nunca são gravados</li>
            <li><?= icone('nota', 18) ?> Nota fiscal enviada para o seu e-mail</li>
        </ul>
    </aside>
</form>
