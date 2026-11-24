/* Lumière · comportamento do site público.
   Rolagem suave (Lenis) + GSAP/ScrollTrigger para hero, parallax e revelações;
   carrinho via fetch, favoritos, busca, gavetas, abas e carrosséis. */
(() => {
    'use strict';

    const $ = (s, el = document) => el.querySelector(s);
    const $$ = (s, el = document) => Array.from(el.querySelectorAll(s));
    const raiz = document.documentElement;
    const reduzir = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const temGsap = typeof window.gsap !== 'undefined' && typeof window.ScrollTrigger !== 'undefined';
    const csrf = $('meta[name="csrf"]')?.content || '';
    if (reduzir) raiz.classList.add('sem-movimento');

    // ---------------- utilitários ----------------
    const postar = async (url, dados) => {
        const resp = await fetch(url, {
            method: 'POST',
            body: dados,
            headers: { 'X-Requested-With': 'fetch', 'X-CSRF': csrf },
            credentials: 'same-origin',
        });
        let json = {};
        try { json = await resp.json(); } catch (e) { /* resposta sem JSON */ }
        return { ok: resp.ok && json.ok !== false, json };
    };

    let lenis = null;
    const travarRolagem = (sim) => {
        if (lenis) sim ? lenis.stop() : lenis.start();
        document.body.style.overflow = sim ? 'hidden' : '';
    };

    // ---------------- rolagem suave ----------------
    $$('.gaveta-corpo, .busca, .filtros, .menu-movel, .mega, .carrossel, .abas, .resumo-pedido .gaveta-lista')
        .forEach((el) => el.setAttribute('data-lenis-prevent', ''));

    if (temGsap) {
        gsap.registerPlugin(ScrollTrigger);
        if (!reduzir && typeof window.Lenis !== 'undefined') {
            lenis = new Lenis({ lerp: 0.11, smoothWheel: true });
            lenis.on('scroll', ScrollTrigger.update);
            gsap.ticker.add((t) => lenis.raf(t * 1000));
            gsap.ticker.lagSmoothing(0);
            // âncoras internas com rolagem suave
            $$('a[href*="#"]').forEach((a) => {
                a.addEventListener('click', (e) => {
                    const url = new URL(a.href, location.href);
                    if (url.pathname !== location.pathname || !url.hash) return;
                    const alvo = document.getElementById(url.hash.slice(1));
                    if (!alvo) return;
                    e.preventDefault();
                    fecharMenuMovel();
                    lenis.scrollTo(alvo, { offset: -70, duration: 1.4 });
                    history.replaceState(null, '', url.hash);
                });
            });
        }
    }

    // ---------------- topo: fundo ao rolar e esconder ao descer ----------------
    const topo = $('[data-topo]');
    let ultimoY = window.scrollY;
    const aoRolar = () => {
        const y = window.scrollY;
        topo?.classList.toggle('rolou', y > 24);
        const algoAberto = document.body.dataset.travado === '1' || $('.tem-mega.aberto');
        if (!algoAberto && y > 420 && y > ultimoY + 4) topo?.classList.add('escondido');
        else if (y < ultimoY - 4 || y < 420) topo?.classList.remove('escondido');
        ultimoY = y;
    };
    window.addEventListener('scroll', aoRolar, { passive: true });
    aoRolar();

    // ---------------- mega menu "Coleções" ----------------
    const megaItem = $('[data-mega-item]');
    const megaBtn = $('[data-mega-btn]');
    const abrirMega = (sim) => {
        if (!megaItem) return;
        megaItem.classList.toggle('aberto', sim);
        megaBtn.setAttribute('aria-expanded', sim ? 'true' : 'false');
        if (sim) topo.classList.add('rolou');
        else aoRolar();
    };
    if (megaItem) {
        let tempo;
        const ponteiroFino = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
        megaBtn.addEventListener('click', () => abrirMega(!megaItem.classList.contains('aberto')));
        if (ponteiroFino) {
            megaItem.addEventListener('mouseenter', () => { clearTimeout(tempo); abrirMega(true); });
            megaItem.addEventListener('mouseleave', () => { tempo = setTimeout(() => abrirMega(false), 180); });
        }
        document.addEventListener('click', (e) => { if (!megaItem.contains(e.target)) abrirMega(false); });
        megaItem.addEventListener('focusout', (e) => { if (!megaItem.contains(e.relatedTarget)) abrirMega(false); });
    }

    // ---------------- menu do celular ----------------
    const menuMovel = $('[data-menu-movel]');
    const btnMenu = $('[data-menu-abrir]');
    function fecharMenuMovel() {
        if (!menuMovel || menuMovel.hidden) return;
        menuMovel.hidden = true;
        btnMenu?.setAttribute('aria-expanded', 'false');
        travarRolagem(false);
        document.body.dataset.travado = '0';
        btnMenu?.focus();
    }
    btnMenu?.addEventListener('click', () => {
        menuMovel.hidden = false;
        btnMenu.setAttribute('aria-expanded', 'true');
        travarRolagem(true);
        document.body.dataset.travado = '1';
        if (temGsap && !reduzir) {
            gsap.fromTo(menuMovel, { clipPath: 'inset(0 0 100% 0)' }, { clipPath: 'inset(0 0 0% 0)', duration: .6, ease: 'expo.out' });
            gsap.from($$('nav > *', menuMovel), { yPercent: 60, opacity: 0, duration: .7, stagger: .05, ease: 'expo.out', delay: .1 });
        }
        $('[data-menu-fechar]', menuMovel)?.focus();
    });
    $('[data-menu-fechar]')?.addEventListener('click', fecharMenuMovel);
    menuMovel?.addEventListener('click', (e) => { if (e.target.closest('[data-gaveta-abrir]')) fecharMenuMovel(); });

    // ---------------- gavetas (carrinho e favoritos) ----------------
    const fundo = $('[data-gaveta-fundo]');
    let gavetaAberta = null;
    let focoAntes = null;

    const abrirGaveta = (nome) => {
        const g = $(`[data-gaveta="${nome}"]`);
        if (!g) return;
        if (gavetaAberta && gavetaAberta !== g) fecharGaveta(false);
        focoAntes = document.activeElement;
        g.hidden = false; fundo.hidden = false;
        requestAnimationFrame(() => { g.classList.add('aberta'); fundo.classList.add('visivel'); });
        gavetaAberta = g;
        travarRolagem(true);
        document.body.dataset.travado = '1';
        if (nome === 'favoritos') desenharFavoritos();
        setTimeout(() => $('[data-gaveta-fechar]', g)?.focus(), 60);
    };
    function fecharGaveta(devolverFoco = true) {
        if (!gavetaAberta) return;
        const g = gavetaAberta;
        g.classList.remove('aberta'); fundo.classList.remove('visivel');
        setTimeout(() => { g.hidden = true; if (!gavetaAberta) fundo.hidden = true; }, 450);
        gavetaAberta = null;
        travarRolagem(false);
        document.body.dataset.travado = '0';
        if (devolverFoco && focoAntes) focoAntes.focus();
    }
    document.addEventListener('click', (e) => {
        const abrir = e.target.closest('[data-gaveta-abrir]');
        if (abrir) { e.preventDefault(); abrirGaveta(abrir.dataset.gavetaAbrir); return; }
        if (e.target.closest('[data-gaveta-fechar]') || e.target === fundo) fecharGaveta();
    });
    // mantém o foco dentro da gaveta aberta
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') { fecharGaveta(); fecharBusca(); fecharMenuMovel(); abrirMega(false); }
        if (e.key !== 'Tab' || !gavetaAberta) return;
        const focaveis = $$('a[href], button:not([disabled]), input:not([type="hidden"])', gavetaAberta).filter((el) => el.offsetParent !== null);
        if (!focaveis.length) return;
        const primeiro = focaveis[0], ultimo = focaveis[focaveis.length - 1];
        if (e.shiftKey && document.activeElement === primeiro) { e.preventDefault(); ultimo.focus(); }
        else if (!e.shiftKey && document.activeElement === ultimo) { e.preventDefault(); primeiro.focus(); }
    });

    // ---------------- aviso flutuante ----------------
    const toast = $('[data-toast]');
    let tempoToast;
    const avisar = (texto, { img = null, acao = false, erro = false } = {}) => {
        if (!toast) return;
        $('[data-toast-txt]', toast).textContent = texto;
        const im = $('[data-toast-img]', toast);
        im.hidden = !img; if (img) im.src = img;
        $('[data-toast-acao]', toast).hidden = !acao;
        toast.classList.toggle('erro', erro);
        toast.hidden = false;
        requestAnimationFrame(() => toast.classList.add('visivel'));
        clearTimeout(tempoToast);
        tempoToast = setTimeout(() => {
            toast.classList.remove('visivel');
            setTimeout(() => { toast.hidden = true; }, 500);
        }, 3800);
    };

    // ---------------- carrinho ----------------
    const conteudoCarrinho = $('[data-carrinho-conteudo]');
    const atualizarContadores = (qtd) => {
        $$('[data-carrinho-qtd]').forEach((el) => { el.textContent = qtd; });
        const btn = $('.topo-carrinho');
        if (btn) {
            btn.setAttribute('aria-label', `Abrir carrinho, ${qtd} itens`);
            btn.classList.remove('pulsou'); void btn.offsetWidth; btn.classList.add('pulsou');
        }
    };
    const trocarConteudoCarrinho = (html) => {
        if (!conteudoCarrinho || typeof html !== 'string') return;
        conteudoCarrinho.innerHTML = html;
    };

    // imagem do produto "voa" até o ícone do carrinho
    const voarAteCarrinho = (origem) => {
        const destino = $('.topo-carrinho .ic');
        if (!origem || !destino || reduzir) return;
        const a = origem.getBoundingClientRect();
        const b = destino.getBoundingClientRect();
        if (!a.width) return;
        const clone = origem.cloneNode();
        clone.className = 'voando';
        Object.assign(clone.style, { left: a.left + 'px', top: a.top + 'px', width: a.width + 'px', height: a.height + 'px' });
        document.body.appendChild(clone);
        topo?.classList.remove('escondido');
        const dx = b.left + b.width / 2 - (a.left + a.width / 2);
        const dy = b.top + b.height / 2 - (a.top + a.height / 2);
        clone.animate([
            { transform: 'translate(0,0) scale(1)', opacity: 1 },
            { transform: `translate(${dx * .6}px, ${dy * .4 - 60}px) scale(.45)`, opacity: .95, offset: .55 },
            { transform: `translate(${dx}px, ${dy}px) scale(.08)`, opacity: .2 },
        ], { duration: 850, easing: 'cubic-bezier(.5,0,.2,1)' }).onfinish = () => clone.remove();
    };

    document.addEventListener('submit', async (e) => {
        const form = e.target;

        // adicionar ao carrinho (cartões e página do produto)
        if (form.matches('[data-add-carrinho]')) {
            e.preventDefault();
            const btn = form.querySelector('[type="submit"]');
            btn?.classList.add('carregando');
            try {
                const { ok, json } = await postar(form.action, new FormData(form));
                if (!ok) { avisar(json.mensagem || json.erro || 'Não foi possível adicionar agora.', { erro: true }); return; }
                const card = form.closest('[data-card]') || $('[data-produto-pagina]');
                voarAteCarrinho(card?.querySelector('.card-img, .produto-foto img'));
                trocarConteudoCarrinho(json.html);
                atualizarContadores(json.quantidade);
                if (btn) { btn.classList.add('ok'); setTimeout(() => btn.classList.remove('ok'), 1400); }
                if (form.hasAttribute('data-abrir-gaveta')) setTimeout(() => abrirGaveta('carrinho'), 500);
                else avisar(json.mensagem, { img: json.produto?.imagem, acao: true });
            } catch (err) {
                avisar('Sem conexão. Tente de novo.', { erro: true });
            } finally {
                btn?.classList.remove('carregando');
            }
            return;
        }

        // alterar quantidade ou remover (gaveta e página do carrinho)
        if (form.matches('[data-carrinho-form]')) {
            e.preventDefault();
            const dados = new FormData(form);
            if (e.submitter?.name) dados.set(e.submitter.name, e.submitter.value);
            conteudoCarrinho?.classList.add('carregando-gaveta');
            const { ok, json } = await postar(form.action, dados);
            if (document.body.classList.contains('pagina-carrinho')) { location.reload(); return; }
            conteudoCarrinho?.classList.remove('carregando-gaveta');
            if (ok || json.html) { trocarConteudoCarrinho(json.html); atualizarContadores(json.quantidade); }
            return;
        }

        // newsletter
        if (form.matches('[data-news]')) {
            e.preventDefault();
            const retorno = $('[data-news-retorno]', form);
            const { ok, json } = await postar(form.action, new FormData(form));
            retorno.textContent = json.mensagem || (ok ? 'Inscrição feita.' : 'Não foi possível inscrever agora.');
            retorno.className = 'news-retorno ' + (ok ? 'ok' : 'erro');
            if (ok) form.reset();
        }
    });

    // ---------------- favoritos ----------------
    const CHAVE_FAV = 'lumiere_favoritos';
    const lerFavs = () => { try { return JSON.parse(localStorage.getItem(CHAVE_FAV)) || {}; } catch (e) { return {}; } };
    const gravarFavs = (f) => { try { localStorage.setItem(CHAVE_FAV, JSON.stringify(f)); } catch (e) { /* modo privado */ } };
    let favs = lerFavs();

    const marcarFavs = () => {
        $$('[data-fav]').forEach((b) => {
            let d; try { d = JSON.parse(b.dataset.fav); } catch (e) { return; }
            const sim = !!favs[d.id];
            b.setAttribute('aria-pressed', sim ? 'true' : 'false');
            b.setAttribute('aria-label', (sim ? 'Remover ' : 'Guardar ') + d.nome + (sim ? ' dos favoritos' : ' nos favoritos'));
        });
        const n = Object.keys(favs).length;
        $$('[data-fav-qtd]').forEach((el) => { el.textContent = n; el.hidden = n === 0; });
    };
    function desenharFavoritos() {
        const lista = $('[data-favoritos-lista]');
        const vazio = $('[data-favoritos-vazio]');
        if (!lista) return;
        const itens = Object.values(favs);
        lista.innerHTML = '';
        vazio.hidden = itens.length > 0;
        itens.forEach((d) => {
            const li = document.createElement('li');
            li.className = 'fav-item';
            const img = document.createElement('img');
            img.src = d.img; img.alt = ''; img.width = 64; img.height = 80; img.loading = 'lazy';
            const meio = document.createElement('div');
            const a = document.createElement('a'); a.href = d.url; a.textContent = d.nome;
            const p = document.createElement('p'); p.textContent = d.preco;
            meio.append(a, p);
            const rem = document.createElement('button');
            rem.type = 'button'; rem.className = 'fav-remover'; rem.setAttribute('aria-label', 'Remover ' + d.nome);
            rem.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>';
            rem.addEventListener('click', () => alternarFav(d));
            li.append(img, meio, rem);
            lista.appendChild(li);
        });
    }
    const alternarFav = (d) => {
        if (favs[d.id]) delete favs[d.id]; else favs[d.id] = d;
        gravarFavs(favs);
        marcarFavs();
        if (gavetaAberta?.dataset.gaveta === 'favoritos') desenharFavoritos();
        const fd = new FormData(); fd.set('produto_id', d.id);
        postar('index.php?controller=favorito&action=alternar', fd).catch(() => {});
        if (favs[d.id]) avisar(d.nome + ' foi para os favoritos.', { img: d.img });
    };
    document.addEventListener('click', (e) => {
        const b = e.target.closest('[data-fav]');
        if (!b) return;
        e.preventDefault();
        try { alternarFav(JSON.parse(b.dataset.fav)); } catch (err) { /* ignora */ }
    });
    marcarFavs();

    // ---------------- busca com sugestões ----------------
    const busca = $('[data-busca]');
    const campoBusca = $('[data-busca-campo]');
    const resultados = $('[data-busca-resultados]');
    let tempoBusca, ultimaBusca = '';
    const abrirBusca = () => {
        busca.hidden = false;
        requestAnimationFrame(() => busca.classList.add('aberta'));
        travarRolagem(true);
        document.body.dataset.travado = '1';
        setTimeout(() => campoBusca.focus(), 50);
    };
    function fecharBusca() {
        if (!busca || busca.hidden) return;
        busca.classList.remove('aberta');
        setTimeout(() => { busca.hidden = true; }, 350);
        travarRolagem(false);
        document.body.dataset.travado = '0';
    }
    $$('[data-busca-abrir]').forEach((b) => b.addEventListener('click', abrirBusca));
    $('[data-busca-fechar]')?.addEventListener('click', fecharBusca);
    busca?.addEventListener('click', (e) => { if (e.target === busca) fecharBusca(); });
    campoBusca?.addEventListener('input', () => {
        clearTimeout(tempoBusca);
        const termo = campoBusca.value.trim();
        tempoBusca = setTimeout(async () => {
            if (termo === ultimaBusca) return;
            ultimaBusca = termo;
            if (termo.length < 2) { resultados.innerHTML = ''; return; }
            try {
                const r = await fetch('index.php?controller=catalogo&action=sugestoes&q=' + encodeURIComponent(termo), { headers: { 'X-Requested-With': 'fetch' } });
                const json = await r.json();
                resultados.innerHTML = '';
                if (!json.itens.length) {
                    const p = document.createElement('p');
                    p.textContent = 'Nenhuma vela encontrada para "' + termo + '". Tente outra nota, como baunilha ou madeira.';
                    resultados.appendChild(p);
                    return;
                }
                json.itens.forEach((it, i) => {
                    const a = document.createElement('a');
                    a.className = 'busca-item'; a.href = it.url; a.style.animationDelay = (i * 40) + 'ms';
                    const img = document.createElement('img'); img.src = it.imagem; img.alt = ''; img.width = 56; img.height = 70;
                    const div = document.createElement('div');
                    const s = document.createElement('strong'); s.textContent = it.nome;
                    const sp = document.createElement('span'); sp.textContent = it.familia + ', ' + it.preco;
                    div.append(s, sp); a.append(img, div);
                    resultados.appendChild(a);
                });
            } catch (err) { resultados.textContent = 'Não foi possível buscar agora.'; }
        }, 220);
    });

    // ---------------- carrosséis (setas + arrastar com o mouse) ----------------
    $$('[data-carrossel]').forEach((trilho) => {
        const nome = trilho.dataset.carrossel;
        const ant = $(`[data-carrossel-ant="${nome}"]`);
        const prox = $(`[data-carrossel-prox="${nome}"]`);
        const passo = () => trilho.clientWidth * 0.8;
        const estado = () => {
            const max = trilho.scrollWidth - trilho.clientWidth - 4;
            if (ant) ant.disabled = trilho.scrollLeft <= 4;
            if (prox) prox.disabled = trilho.scrollLeft >= max;
        };
        ant?.addEventListener('click', () => trilho.scrollBy({ left: -passo(), behavior: reduzir ? 'auto' : 'smooth' }));
        prox?.addEventListener('click', () => trilho.scrollBy({ left: passo(), behavior: reduzir ? 'auto' : 'smooth' }));
        trilho.addEventListener('scroll', estado, { passive: true });
        window.addEventListener('resize', estado);
        estado();

        let x0 = 0, s0 = 0, arrastou = false, apertado = false;
        trilho.addEventListener('pointerdown', (e) => {
            if (e.pointerType !== 'mouse' || e.button !== 0) return;
            apertado = true; arrastou = false; x0 = e.clientX; s0 = trilho.scrollLeft;
        });
        window.addEventListener('pointermove', (e) => {
            if (!apertado) return;
            const dx = e.clientX - x0;
            if (Math.abs(dx) > 6) { arrastou = true; trilho.classList.add('arrastando'); }
            if (arrastou) trilho.scrollLeft = s0 - dx;
        });
        window.addEventListener('pointerup', () => {
            if (!apertado) return;
            apertado = false;
            setTimeout(() => trilho.classList.remove('arrastando'), 0);
        });
        trilho.addEventListener('click', (e) => { if (arrastou) { e.preventDefault(); e.stopPropagation(); arrastou = false; } }, true);
        trilho.addEventListener('dragstart', (e) => e.preventDefault());
    });

    // ---------------- abas (momentos e página do produto) ----------------
    $$('[role="tablist"]').forEach((lista) => {
        const abas = $$('[role="tab"]', lista);
        const indicador = $('[data-abas-indicador]', lista);
        const moverIndicador = (aba) => {
            if (!indicador) return;
            indicador.style.width = aba.offsetWidth + 'px';
            indicador.style.transform = `translateX(${aba.offsetLeft}px)`;
        };
        const ativar = (aba, focar = true) => {
            abas.forEach((a) => {
                const sel = a === aba;
                a.setAttribute('aria-selected', sel ? 'true' : 'false');
                a.tabIndex = sel ? 0 : -1;
                const painel = document.getElementById(a.getAttribute('aria-controls'));
                if (painel) painel.hidden = !sel;
            });
            moverIndicador(aba);
            if (focar) aba.focus();
            const painel = document.getElementById(aba.getAttribute('aria-controls'));
            if (temGsap && !reduzir && painel) {
                gsap.fromTo($$('.card, p', painel), { opacity: 0, y: 26 }, { opacity: 1, y: 0, duration: .7, stagger: .06, ease: 'expo.out', clearProps: 'transform' });
                ScrollTrigger.refresh();
            }
            aba.scrollIntoView({ block: 'nearest', inline: 'center', behavior: reduzir ? 'auto' : 'smooth' });
        };
        abas.forEach((aba, i) => {
            aba.addEventListener('click', () => ativar(aba, false));
            aba.addEventListener('keydown', (e) => {
                let alvo = null;
                if (e.key === 'ArrowRight') alvo = abas[(i + 1) % abas.length];
                if (e.key === 'ArrowLeft') alvo = abas[(i - 1 + abas.length) % abas.length];
                if (e.key === 'Home') alvo = abas[0];
                if (e.key === 'End') alvo = abas[abas.length - 1];
                if (alvo) { e.preventDefault(); ativar(alvo); }
            });
        });
        const atual = abas.find((a) => a.getAttribute('aria-selected') === 'true') || abas[0];
        if (atual) {
            moverIndicador(atual);
            window.addEventListener('resize', () => moverIndicador(abas.find((a) => a.getAttribute('aria-selected') === 'true')));
            document.fonts?.ready.then(() => moverIndicador(atual));
        }
    });

    // ---------------- vídeos: tocam só quando aparecem ----------------
    const videos = $$('video[data-video-lazy]');
    if (reduzir) {
        videos.forEach((v) => { v.removeAttribute('autoplay'); v.pause(); });
    } else if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver((entradas) => {
            entradas.forEach((en) => {
                const v = en.target;
                if (en.isIntersecting) { v.preload = 'auto'; const p = v.play(); if (p) p.catch(() => {}); }
                else v.pause();
            });
        }, { rootMargin: '200px 0px' });
        videos.forEach((v) => { v.pause(); io.observe(v); });
    }

    // ---------------- letreiro do hero: ajusta a caixa ao desenho real das letras ----------------
    const marca = $('[data-hero-marca]');
    if (marca) {
        (document.fonts ? document.fonts.ready : Promise.resolve()).then(() => {
            try {
                const bb = marca.querySelector('g[clip-path]').getBBox();
                if (bb.width > 1000) marca.setAttribute('viewBox', `${bb.x.toFixed(0)} 0 ${bb.width.toFixed(0)} 1520`);
            } catch (e) { /* mantém a caixa padrão */ }
        });
    }

    // ================= ANIMAÇÕES (GSAP) =================
    if (!temGsap) { $('[data-hero]')?.classList.add('pronto'); return; }
    if (reduzir) { $('[data-hero]')?.classList.add('pronto'); return; }

    // ---- hero: letras sobem uma a uma, vela e texto entram em seguida ----
    const hero = $('[data-hero]');
    if (hero) {
        const letras = $$('.hero-letra', hero);
        const vela = $('[data-hero-vela]', hero);
        const texto = $$('[data-hero-texto] > *', hero);
        gsap.set(letras, { y: 1700 });
        const intro = () => {
            hero.classList.add('pronto');
            gsap.timeline({ defaults: { ease: 'expo.out' } })
                .to(letras, { y: 0, duration: 1.5, stagger: 0.07 })
                .from(vela.querySelector('img'), { opacity: 0, y: 60, scale: 1.04, duration: 1.6, transformOrigin: '70% 100%' }, 0.35)
                .from(texto, { opacity: 0, y: 24, duration: 1, stagger: 0.08 }, 0.7);
        };
        (document.fonts ? document.fonts.ready : Promise.resolve()).then(intro);

        // parallax: letreiro sobe mais devagar que a vela
        gsap.to('[data-hero-marca]', {
            yPercent: -18, ease: 'none',
            scrollTrigger: { trigger: hero, start: 'top top', end: 'bottom top', scrub: true },
        });
        gsap.to(vela, {
            yPercent: 10, scale: 1.05, ease: 'none', transformOrigin: '60% 100%',
            scrollTrigger: { trigger: hero, start: 'top top', end: 'bottom top', scrub: true },
        });
        gsap.to('[data-hero-texto]', {
            yPercent: -40, opacity: 0, ease: 'none',
            scrollTrigger: { trigger: hero, start: '35% top', end: 'bottom top', scrub: true },
        });
    }

    // ---- faixa correndo: acelera conforme a velocidade da rolagem ----
    const faixa = $('[data-faixa] .faixa-trilho');
    if (faixa) {
        faixa.style.animation = 'none';
        const loop = gsap.to(faixa, { xPercent: -50, duration: 38, ease: 'none', repeat: -1 });
        ScrollTrigger.create({
            onUpdate: (st) => {
                const v = Math.min(Math.abs(st.getVelocity()) / 300, 6);
                gsap.to(loop, { timeScale: (st.direction === 1 ? 1 : -1) * (1 + v), duration: .3, overwrite: true });
                gsap.to(loop, { timeScale: st.direction === 1 ? 1 : -1, duration: 1.2, delay: .3, overwrite: false });
            },
        });
    }

    // ---- títulos revelados por máscara ----
    $$('[data-revelar]').forEach((t) => {
        const span = document.createElement('span');
        span.className = 'linha-rev';
        while (t.firstChild) span.appendChild(t.firstChild);
        t.appendChild(span);
        gsap.from(span, {
            yPercent: 105, duration: 1.2, ease: 'expo.out',
            scrollTrigger: { trigger: t, start: 'top 88%' },
        });
    });

    // ---- parallax nas fotos e vídeos ----
    $$('[data-parallax]').forEach((caixa) => {
        const forca = parseFloat(caixa.dataset.parallax) || 8;
        const alvo = caixa.querySelector('img, video') || caixa;
        gsap.fromTo(alvo, { yPercent: -forca / 2 }, {
            yPercent: forca / 2, ease: 'none',
            scrollTrigger: { trigger: caixa, start: 'top bottom', end: 'bottom top', scrub: true },
        });
    });

    // ---- cartões entram em lote (só na primeira vez) ----
    ScrollTrigger.batch('.grade-cards .card, .carrossel .card, .familia', {
        start: 'top 92%', once: true,
        onEnter: (lote) => gsap.from(lote, { opacity: 0, y: 32, duration: .9, stagger: .07, ease: 'expo.out', clearProps: 'transform,opacity' }),
    });

    // ---- inspiração: vídeo abre até a tela cheia e o texto acende palavra por palavra ----
    const inspira = $('[data-inspira]');
    if (inspira) {
        const frase = $('[data-palavras]', inspira);
        const palavras = frase.textContent.trim().split(/\s+/);
        frase.innerHTML = '';
        palavras.forEach((p, i) => {
            const s = document.createElement('span');
            s.className = 'palavra'; s.textContent = p;
            frase.appendChild(s);
            if (i < palavras.length - 1) frase.appendChild(document.createTextNode(' '));
        });
        const spans = $$('.palavra', frase);
        const tl = gsap.timeline({
            scrollTrigger: { trigger: inspira, start: 'top top', end: 'bottom bottom', scrub: 0.6 },
        });
        tl.to('[data-inspira-video]', { clipPath: 'inset(0% 0% round 0px)', ease: 'none', duration: 1 })
          .to(spans, { opacity: 1, stagger: 0.06, duration: 0.3, ease: 'none' }, 0.25)
          .from($('.botao', inspira), { opacity: 0, y: 20, duration: .4 }, '>-0.2');
    }

    // ---- passos do ritual: linha superior se desenha ----
    $$('[data-passo]').forEach((p) => {
        gsap.from(p, {
            opacity: 0, x: 30, duration: 1, ease: 'expo.out',
            scrollTrigger: { trigger: p, start: 'top 88%' },
        });
        gsap.from(p.querySelector('.passo-num'), {
            yPercent: 100, opacity: 0, duration: 1, ease: 'expo.out',
            scrollTrigger: { trigger: p, start: 'top 88%' },
        });
    });

    // recalcula posições quando imagens e fontes terminam de carregar
    window.addEventListener('load', () => ScrollTrigger.refresh());
})();
