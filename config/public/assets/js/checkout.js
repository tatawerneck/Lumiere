/* Lumière · checkout: máscaras, CEP automático (ViaCEP), troca de forma de pagamento,
   prévia do cartão, validação antes do envio e trava contra clique duplo. */
(() => {
    'use strict';
    const form = document.querySelector('[data-checkout]');
    if (!form) return;
    const $ = (s) => form.querySelector(s);
    const $$ = (s) => Array.from(form.querySelectorAll(s));
    const dig = (v) => (v || '').replace(/\D+/g, '');
    const moeda = (n) => 'R$ ' + n.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');

    // ---------- máscaras ----------
    const bandeira = (n) => {
        if (/^(4011|4312|4389|4514|4576|5041|5066|5067|509|6277|6362|6363|650|6516|6550)/.test(n)) return 'ELO';
        if (/^(606282|3841)/.test(n)) return 'HIPERCARD';
        if (/^3[47]/.test(n)) return 'AMEX';
        if (/^(5[1-5]|2[2-7])/.test(n)) return 'MASTERCARD';
        if (/^4/.test(n)) return 'VISA';
        return '';
    };
    const mascaras = {
        cpf: (v) => dig(v).slice(0, 11).replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2'),
        telefone: (v) => {
            const d = dig(v).slice(0, 11);
            if (d.length <= 10) return d.replace(/(\d{2})(\d)/, '($1) $2').replace(/(\d{4})(\d)/, '$1-$2');
            return d.replace(/(\d{2})(\d)/, '($1) $2').replace(/(\d{5})(\d)/, '$1-$2');
        },
        cep: (v) => dig(v).slice(0, 8).replace(/(\d{5})(\d)/, '$1-$2'),
        cartao: (v) => {
            const d = dig(v).slice(0, 19);
            if (bandeira(d) === 'AMEX') return d.slice(0, 15).replace(/(\d{4})(\d{0,6})(\d{0,5})/, (m, a, b, c) => [a, b, c].filter(Boolean).join(' '));
            return d.replace(/(\d{4})(?=\d)/g, '$1 ');
        },
        validade: (v) => dig(v).slice(0, 4).replace(/(\d{2})(\d)/, '$1/$2'),
        digitos: (v) => dig(v).slice(0, 4),
    };
    $$('[data-mascara]').forEach((el) => {
        const m = mascaras[el.dataset.mascara];
        const aplicar = () => { el.value = m(el.value); };
        el.addEventListener('input', aplicar);
        if (el.value) aplicar();
    });

    // ---------- CEP: preenche o endereço ----------
    const cep = $('[data-cep]');
    const statusCep = $('[data-cep-status]');
    let ultimoCep = '';
    cep?.addEventListener('input', async () => {
        const d = dig(cep.value);
        if (d.length !== 8 || d === ultimoCep) return;
        ultimoCep = d;
        statusCep.textContent = 'Buscando endereço...';
        try {
            const r = await fetch(`https://viacep.com.br/ws/${d}/json/`);
            const j = await r.json();
            if (j.erro) { statusCep.textContent = 'CEP não encontrado. Preencha o endereço abaixo.'; return; }
            const preencher = (nome, valor) => { const el = form.elements[nome]; if (el && valor && !el.value) el.value = valor; };
            preencher('endereco', j.logradouro);
            preencher('bairro', j.bairro);
            preencher('cidade', j.localidade);
            if (j.uf) form.elements.uf.value = j.uf;
            statusCep.textContent = 'Endereço encontrado. Falta só o número.';
            form.elements.numero.focus();
        } catch (e) {
            statusCep.textContent = 'Não conseguimos buscar o CEP agora. Preencha manualmente.';
        }
    });

    // ---------- forma de pagamento ----------
    const painelCartao = $('[data-painel-cartao]');
    const painelPix = $('[data-painel-pix]');
    const linhaDesc = $('[data-linha-desconto]');
    const total = $('[data-total]');
    const txtBotao = $('[data-finalizar-txt]');
    const face = $('[data-cartao-face]');
    const parcelasCampo = $('[data-parcelas-campo]');
    const subtotal = parseFloat(form.dataset.subtotal);
    const frete = parseFloat(form.dataset.frete);
    const descPix = parseFloat(form.dataset.pixDesconto);
    const forma = () => ($$('[data-forma]').find((r) => r.checked) || {}).value || 'credito';

    const atualizarForma = () => {
        const f = forma();
        const pix = f === 'pix';
        painelCartao.hidden = pix;
        painelPix.hidden = !pix;
        linhaDesc.hidden = !pix;
        parcelasCampo.hidden = f !== 'credito';
        face.classList.toggle('debito', f === 'debito');
        $('[data-cartao-tipo]').textContent = f === 'debito' ? 'DÉBITO' : 'CRÉDITO';
        total.textContent = moeda(subtotal - (pix ? descPix : 0) + frete);
        txtBotao.textContent = pix ? 'Gerar PIX de ' + total.textContent : 'Pagar ' + total.textContent;
        // reinicia a animação de entrada do painel visível
        [painelCartao, painelPix].forEach((p) => { p.style.animation = 'none'; void p.offsetWidth; p.style.animation = ''; });
    };
    $$('[data-forma]').forEach((r) => r.addEventListener('change', atualizarForma));
    atualizarForma();

    // ---------- prévia do cartão ----------
    const num = $('[data-cartao-num]');
    const nome = $('[data-cartao-nome]');
    const val = $('[data-cartao-val]');
    const cvv = $('[data-cartao-cvv]');
    const verNum = $('[data-cartao-ver-num]');
    const verNome = $('[data-cartao-ver-nome]');
    const verVal = $('[data-cartao-ver-val]');
    const verBand = $('[data-cartao-bandeira]');
    const atualizarCartao = () => {
        const d = dig(num.value);
        const modelo = (bandeira(d) === 'AMEX' ? '•••• •••••• •••••' : '•••• •••• •••• ••••').split('');
        let i = 0;
        verNum.textContent = modelo.map((c) => (c === '•' && i < d.length ? d[i++] : c)).join('');
        verNome.textContent = (nome.value || 'NOME DO TITULAR').toUpperCase();
        verVal.textContent = val.value || 'MM/AA';
        verBand.textContent = bandeira(d) || 'LUMIÈRE';
        cvv.maxLength = bandeira(d) === 'AMEX' ? 4 : 3;
    };
    [num, nome, val].forEach((el) => el?.addEventListener('input', atualizarCartao));
    // inclina o cartão ao digitar o CVV (lembra que ele fica no verso)
    cvv?.addEventListener('focus', () => { face.style.transform = 'rotateY(-14deg) rotateX(4deg)'; });
    cvv?.addEventListener('blur', () => { face.style.transform = ''; });
    $('[data-cartao-teste]')?.addEventListener('click', () => {
        num.value = mascaras.cartao('4111111111111111');
        nome.value = (form.elements.nome.value || 'CLIENTE TESTE').toUpperCase();
        const d = new Date(); val.value = '12/' + String((d.getFullYear() + 3) % 100).padStart(2, '0');
        cvv.value = '123';
        atualizarCartao();
        limparErro(num); limparErro(nome); limparErro(val); limparErro(cvv);
    });
    atualizarCartao();

    // ---------- validação ----------
    const cpfValido = (c) => {
        c = dig(c);
        if (c.length !== 11 || /^(\d)\1{10}$/.test(c)) return false;
        for (let t = 9; t < 11; t++) {
            let s = 0;
            for (let i = 0; i < t; i++) s += Number(c[i]) * (t + 1 - i);
            if (((10 * s) % 11) % 10 !== Number(c[t])) return false;
        }
        return true;
    };
    const luhn = (n) => {
        let s = 0, alt = false;
        for (let i = n.length - 1; i >= 0; i--) {
            let d = Number(n[i]);
            if (alt) { d *= 2; if (d > 9) d -= 9; }
            s += d; alt = !alt;
        }
        return n.length >= 13 && s % 10 === 0;
    };
    const mostrarErro = (el, msg) => {
        const campo = el.closest('.campo');
        campo.classList.add('invalido');
        el.setAttribute('aria-invalid', 'true');
        let p = campo.querySelector('.campo-erro');
        if (!p) { p = document.createElement('p'); p.className = 'campo-erro'; p.id = el.id + '-erro'; campo.appendChild(p); }
        p.textContent = msg;
        el.setAttribute('aria-describedby', p.id);
    };
    function limparErro(el) {
        const campo = el.closest('.campo');
        if (!campo) return;
        campo.classList.remove('invalido');
        el.removeAttribute('aria-invalid');
        campo.querySelector('.campo-erro')?.remove();
    }
    form.addEventListener('input', (e) => { if (e.target.closest('.invalido')) limparErro(e.target); });

    const validar = () => {
        const erros = [];
        const exigir = (el, ok, msg) => { if (!ok) { mostrarErro(el, msg); erros.push(el); } };
        const f = form.elements;
        exigir(f.nome, f.nome.value.trim().includes(' ') && f.nome.value.trim().length >= 5, 'Digite nome e sobrenome.');
        exigir(f.email, /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(f.email.value.trim()), 'Digite um e-mail válido.');
        exigir(f.telefone, dig(f.telefone.value).length >= 10, 'Digite o celular com DDD.');
        exigir(f.cpf, cpfValido(f.cpf.value), 'Este CPF não é válido.');
        exigir(f.cep, dig(f.cep.value).length === 8, 'O CEP tem 8 números.');
        exigir(f.endereco, f.endereco.value.trim() !== '', 'Informe a rua.');
        exigir(f.numero, f.numero.value.trim() !== '', 'Informe o número (ou S/N).');
        exigir(f.bairro, f.bairro.value.trim() !== '', 'Informe o bairro.');
        exigir(f.cidade, f.cidade.value.trim() !== '', 'Informe a cidade.');
        exigir(f.uf, f.uf.value !== '', 'Escolha o estado.');
        if (forma() !== 'pix') {
            exigir(num, luhn(dig(num.value)), 'Confira o número do cartão.');
            exigir(nome, nome.value.trim().length >= 4, 'Digite o nome como está no cartão.');
            const [mm, aa] = (val.value || '').split('/').map(Number);
            const fim = new Date(2000 + (aa || 0), mm || 0, 0, 23, 59);
            exigir(val, mm >= 1 && mm <= 12 && fim >= new Date(), 'Validade inválida ou vencida.');
            exigir(cvv, dig(cvv.value).length === cvv.maxLength, `O CVV tem ${cvv.maxLength} números.`);
        }
        return erros;
    };

    const botao = $('[data-finalizar]');
    form.addEventListener('submit', (e) => {
        const erros = validar();
        if (erros.length) {
            e.preventDefault();
            erros[0].focus();
            erros[0].scrollIntoView({ block: 'center', behavior: 'smooth' });
            return;
        }
        botao.classList.add('carregando');
        botao.querySelector('.girando').hidden = false;
        txtBotao.textContent = forma() === 'pix' ? 'Gerando PIX...' : 'Processando pagamento...';
    });
    // ao voltar pelo histórico, libera o botão
    window.addEventListener('pageshow', () => { botao.classList.remove('carregando'); botao.querySelector('.girando').hidden = true; atualizarForma(); });
})();
