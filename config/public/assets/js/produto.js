/* Lumière · página do produto: troca de fotos e seletor de quantidade. */
(() => {
    'use strict';
    const principal = document.querySelector('[data-foto-principal]');
    document.querySelectorAll('[data-foto]').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (btn.getAttribute('aria-current') === 'true') return;
            document.querySelectorAll('[data-foto]').forEach((b) => b.setAttribute('aria-current', b === btn ? 'true' : 'false'));
            principal.classList.add('saindo');
            const nova = new Image();
            nova.onload = () => { principal.src = btn.dataset.foto; requestAnimationFrame(() => principal.classList.remove('saindo')); };
            nova.onerror = nova.onload;
            setTimeout(() => { nova.src = btn.dataset.foto; }, 180);
        });
    });

    document.querySelectorAll('[data-stepper]').forEach((st) => {
        const campo = st.querySelector('input');
        const max = () => Number(campo.max) || 20;
        const ajustar = (n) => { campo.value = Math.max(1, Math.min(max(), n)); };
        st.querySelector('[data-menos]').addEventListener('click', () => ajustar(Number(campo.value) - 1));
        st.querySelector('[data-mais]').addEventListener('click', () => ajustar(Number(campo.value) + 1));
        campo.addEventListener('change', () => ajustar(parseInt(campo.value, 10) || 1));
    });
})();
