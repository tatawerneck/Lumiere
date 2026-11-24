/* Lumière · painel: menu no celular, confirmações, prévia de foto, contador de caracteres e envio único. */
(() => {
    'use strict';
    const lateral = document.querySelector('[data-adm-lateral]');
    const btn = document.querySelector('[data-adm-menu]');
    btn?.addEventListener('click', () => {
        const abrir = !lateral.classList.contains('aberto');
        lateral.classList.toggle('aberto', abrir);
        btn.setAttribute('aria-expanded', abrir ? 'true' : 'false');
    });
    document.addEventListener('click', (e) => {
        if (lateral?.classList.contains('aberto') && !lateral.contains(e.target) && !btn.contains(e.target)) {
            lateral.classList.remove('aberto'); btn.setAttribute('aria-expanded', 'false');
        }
    });

    // pede confirmação antes de ações sensíveis (excluir, cancelar...)
    document.querySelectorAll('form[data-confirmar]').forEach((f) => {
        f.addEventListener('submit', (e) => { if (!window.confirm(f.dataset.confirmar)) e.preventDefault(); });
    });

    // evita clique duplo nos formulários do painel
    document.querySelectorAll('.adm-conteudo form[method="post"]').forEach((f) => {
        f.addEventListener('submit', (e) => {
            if (e.defaultPrevented) return;
            const b = f.querySelector('[type="submit"]');
            if (b) setTimeout(() => { b.disabled = true; }, 0);
        });
    });

    // prévia da foto escolhida e área de arrastar
    const upload = document.querySelector('[data-upload]');
    if (upload) {
        const input = upload.querySelector('input[type="file"]');
        const previa = document.querySelector('[data-previa]');
        const nomeArq = upload.querySelector('[data-nome-arquivo]');
        const mostrar = (arq) => {
            if (!arq) return;
            nomeArq.textContent = arq.name + ' (' + Math.round(arq.size / 1024) + ' KB)';
            if (previa && arq.type.startsWith('image/')) previa.src = URL.createObjectURL(arq);
        };
        input.addEventListener('change', () => mostrar(input.files[0]));
        ['dragenter', 'dragover'].forEach((ev) => upload.addEventListener(ev, (e) => { e.preventDefault(); upload.classList.add('arrastando'); }));
        ['dragleave', 'drop'].forEach((ev) => upload.addEventListener(ev, (e) => { e.preventDefault(); upload.classList.remove('arrastando'); }));
        upload.addEventListener('drop', (e) => {
            if (!e.dataTransfer.files.length) return;
            input.files = e.dataTransfer.files;
            mostrar(input.files[0]);
        });
        document.querySelector('[data-url-foto]')?.addEventListener('change', (e) => {
            if (previa && /^https:\/\//.test(e.target.value)) previa.src = e.target.value;
        });
    }

    // contador de caracteres
    document.querySelectorAll('textarea[maxlength][data-contar]').forEach((t) => {
        const c = document.createElement('p');
        c.className = 'contador';
        const at = () => { c.textContent = t.value.length + '/' + t.maxLength; };
        t.after(c); t.addEventListener('input', at); at();
    });

    // selects que navegam ao mudar (filtros)
    document.querySelectorAll('select[data-auto]').forEach((s) => s.addEventListener('change', () => s.form.requestSubmit()));
})();
