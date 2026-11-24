/* Acesso e cadastro: mostrar/ocultar senha, confirmação de senha, lembrar e-mail, estado de envio e inclinação do cartão. */
(function () {
    'use strict';

    var reduzir = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var ponteiroFino = window.matchMedia('(pointer: fine)').matches;
    var CHAVE_EMAIL = 'lumiere.email';

    // localStorage pode estar bloqueado (janela privada, políticas do navegador): tudo é opcional.
    function lerEmailSalvo() {
        try { return window.localStorage.getItem(CHAVE_EMAIL) || ''; } catch (_) { return ''; }
    }
    function salvarEmail(valor) {
        try {
            if (valor) window.localStorage.setItem(CHAVE_EMAIL, valor);
            else window.localStorage.removeItem(CHAVE_EMAIL);
        } catch (_) { /* sem armazenamento: apenas não lembra */ }
    }

    document.querySelectorAll('[data-auth-form]').forEach(function (form) {
        var email = form.querySelector('[data-email]');
        var senha = form.querySelector('[data-senha]');
        var confirmar = form.querySelector('[data-confirmar]');
        var ajuda = form.querySelector('[data-ajuda-confirmar]');
        var lembrar = form.querySelector('[data-lembrar]');
        var botao = form.querySelector('[data-enviar]');
        var texto = botao ? botao.querySelector('[data-texto]') : null;
        var girando = botao ? botao.querySelector('.girando') : null;
        var textoOriginal = texto ? texto.textContent : '';

        // Lembrar e-mail: preenche no login quando a pessoa pediu para lembrar.
        if (lembrar && email && !email.value) {
            var salvo = lerEmailSalvo();
            if (salvo) {
                email.value = salvo;
                lembrar.checked = true;
            }
        }

        // Mostrar ou ocultar senha.
        form.querySelectorAll('[data-mostrar-senha]').forEach(function (b) {
            b.addEventListener('click', function () {
                var campo = b.parentElement.querySelector('input');
                if (!campo) return;
                var mostrar = campo.type === 'password';
                campo.type = mostrar ? 'text' : 'password';
                b.setAttribute('aria-pressed', String(mostrar));
                b.setAttribute('aria-label', mostrar ? 'Ocultar senha' : 'Mostrar senha');
                campo.focus();
            });
        });

        // Confirmação de senha em tempo real.
        var conferirSenhas = function () {
            if (!confirmar || !senha) return true;
            var vazio = confirmar.value === '';
            var igual = confirmar.value === senha.value;
            confirmar.setCustomValidity(igual || vazio ? '' : 'As senhas não conferem.');
            if (ajuda) ajuda.hidden = igual || vazio;
            return igual || vazio;
        };
        if (confirmar) confirmar.addEventListener('input', conferirSenhas);
        if (senha) senha.addEventListener('input', conferirSenhas);

        form.addEventListener('submit', function (ev) {
            var valido = form.checkValidity() && conferirSenhas();
            if (!valido) {
                ev.preventDefault();
                form.reportValidity();
                var primeiro = form.querySelector(':invalid');
                if (primeiro) primeiro.focus();
                return;
            }

            if (lembrar && email) salvarEmail(lembrar.checked ? email.value.trim() : '');

            if (botao) {
                botao.disabled = true;
                if (texto) texto.textContent = form.hasAttribute('data-cadastro') ? 'Criando sua conta…' : 'Entrando…';
                if (girando) girando.hidden = false;
            }
        });

        window.addEventListener('pageshow', function () {
            if (botao) botao.disabled = false;
            if (texto) texto.textContent = textoOriginal;
            if (girando) girando.hidden = true;
        });
    });

    // Cartão com leve inclinação e brilho que segue o ponteiro.
    var cartao = document.querySelector('[data-tilt]');
    if (cartao && ponteiroFino && !reduzir) {
        cartao.addEventListener('pointermove', function (ev) {
            var r = cartao.getBoundingClientRect();
            var px = (ev.clientX - r.left) / r.width;
            var py = (ev.clientY - r.top) / r.height;
            cartao.style.transform = 'perspective(900px) rotateX(' + ((0.5 - py) * 7).toFixed(2) +
                'deg) rotateY(' + ((px - 0.5) * 9).toFixed(2) + 'deg)';
            cartao.style.setProperty('--mx', (px * 100).toFixed(1) + '%');
            cartao.style.setProperty('--my', (py * 100).toFixed(1) + '%');
        });
        cartao.addEventListener('pointerleave', function () { cartao.style.transform = ''; });
    }
})();
