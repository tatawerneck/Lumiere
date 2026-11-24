/* Lumière · catálogo: filtros aplicam sozinhos, faixa de preço, ordenação e gaveta de filtros no celular. */
(() => {
    'use strict';
    const form = document.querySelector('[data-filtros-form]');
    if (!form) return;
    const preco = form.querySelector('[data-preco]');
    const saida = form.querySelector('[data-preco-saida]');
    const aside = document.querySelector('[data-filtros]');
    const btnAbrir = document.querySelector('[data-filtros-abrir]');
    const moeda = (v) => 'R$ ' + Number(v).toFixed(2).replace('.', ',');

    // sem filtro de preço quando a faixa está no máximo
    form.addEventListener('submit', () => {
        if (preco && Number(preco.value) >= Number(preco.max)) preco.disabled = true;
        form.querySelectorAll('input[type="radio"]:checked').forEach((r) => { if (r.value === '') r.disabled = true; });
    });

    // aplica ao mudar (no computador); no celular espera o botão "Aplicar"
    const celular = () => window.matchMedia('(max-width: 900px)').matches;
    form.addEventListener('change', (e) => {
        if (e.target === preco || celular()) return;
        form.requestSubmit();
    });
    preco?.addEventListener('input', () => { saida.textContent = moeda(preco.value); });
    preco?.addEventListener('change', () => { if (!celular()) form.requestSubmit(); });

    // ordenação: cada opção já traz o endereço com os filtros atuais
    document.querySelector('[data-ordenar]')?.addEventListener('change', (e) => { location.href = e.target.value; });

    // marca visualmente a opção escolhida antes do recarregamento
    form.querySelectorAll('.filtro-opcao input').forEach((r) => {
        r.addEventListener('change', () => {
            form.querySelectorAll(`input[name="${r.name}"]`).forEach((o) => o.closest('.filtro-opcao').classList.toggle('ativo', o.checked));
        });
    });

    // gaveta de filtros no celular
    const fundo = document.querySelector('[data-gaveta-fundo]');
    const fechar = () => {
        aside.classList.remove('aberto');
        btnAbrir?.setAttribute('aria-expanded', 'false');
        fundo?.classList.remove('visivel');
        setTimeout(() => { if (fundo && !document.querySelector('.gaveta.aberta')) fundo.hidden = true; }, 400);
        document.body.style.overflow = '';
    };
    btnAbrir?.addEventListener('click', () => {
        aside.classList.add('aberto');
        btnAbrir.setAttribute('aria-expanded', 'true');
        if (fundo) { fundo.hidden = false; requestAnimationFrame(() => fundo.classList.add('visivel')); }
        document.body.style.overflow = 'hidden';
        aside.querySelector('input, button')?.focus();
    });
    fundo?.addEventListener('click', () => { if (aside.classList.contains('aberto')) fechar(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && aside.classList.contains('aberto')) fechar(); });
})();
