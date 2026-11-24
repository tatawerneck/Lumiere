/* Lumière · confirmação do pedido: desenha o QR Code do PIX, copia o código e confere o pagamento sozinho. */
(() => {
    'use strict';
    const caixa = document.querySelector('[data-pix]');
    if (!caixa) return;

    const alvoQr = caixa.querySelector('[data-pix-qr]');
    if (alvoQr && typeof window.qrcode === 'function') {
        const qr = qrcode(0, 'M');
        qr.addData(alvoQr.dataset.payload);
        qr.make();
        alvoQr.innerHTML = qr.createSvgTag({ cellSize: 4, margin: 0, scalable: true, alt: 'QR Code do PIX' });
    }

    const botao = caixa.querySelector('[data-copiar]');
    const campo = caixa.querySelector('[data-pix-texto]');
    botao?.addEventListener('click', async () => {
        try { await navigator.clipboard.writeText(campo.value); }
        catch (e) { campo.select(); document.execCommand('copy'); }
        botao.classList.add('copiado');
        botao.querySelector('span').textContent = 'Copiado';
        setTimeout(() => { botao.classList.remove('copiado'); botao.querySelector('span').textContent = 'Copiar'; }, 2500);
    });

    // confere o status a cada 5 segundos por até 30 minutos
    const url = caixa.dataset.statusUrl;
    let tentativas = 0;
    const conferir = async () => {
        tentativas++;
        try {
            const r = await fetch(url, { headers: { 'X-Requested-With': 'fetch' }, credentials: 'same-origin' });
            const j = await r.json();
            if (j.status && j.status !== 'aguardando_pagamento') { location.reload(); return; }
        } catch (e) { /* tenta de novo */ }
        if (tentativas < 360) setTimeout(conferir, 5000);
    };
    setTimeout(conferir, 5000);
})();
