/* Lumière · gráficos do dashboard (Chart.js). Os dados vêm em JSON no atributo data-grafico. */
(() => {
    'use strict';
    if (typeof window.Chart === 'undefined') return;
    const reduzir = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const ler = (el) => { try { return JSON.parse(el.dataset.grafico); } catch (e) { return null; } };
    const moeda = (v) => 'R$ ' + Number(v).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    Chart.defaults.font.family = '"Hanken Grotesk", system-ui, sans-serif';
    Chart.defaults.color = '#7C7686';
    if (reduzir) Chart.defaults.animation = false;

    const linha = document.getElementById('grafico-vendas');
    const dl = linha && ler(linha);
    if (dl) {
        const ctx = linha.getContext('2d');
        const grad = ctx.createLinearGradient(0, 0, 0, 280);
        grad.addColorStop(0, 'rgba(217, 63, 116, .28)');
        grad.addColorStop(1, 'rgba(217, 63, 116, 0)');
        new Chart(ctx, {
            type: 'line',
            data: { labels: dl.rotulos, datasets: [{ label: 'Faturamento', data: dl.valores, borderColor: '#D93F74', backgroundColor: grad, fill: true, tension: .35, borderWidth: 2.5, pointRadius: 0, pointHoverRadius: 5, pointBackgroundColor: '#D93F74' }] },
            options: {
                responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
                plugins: { legend: { display: false }, tooltip: { backgroundColor: '#0F1B3D', padding: 12, callbacks: { label: (c) => moeda(c.parsed.y) } } },
                scales: {
                    x: { grid: { display: false }, ticks: { maxTicksLimit: 8 } },
                    y: { beginAtZero: true, grid: { color: '#F6E4E8' }, border: { display: false }, ticks: { callback: (v) => 'R$ ' + v } },
                },
            },
        });
    }

    const pizza = document.getElementById('grafico-pagamentos');
    const dp = pizza && ler(pizza);
    if (dp) {
        new Chart(pizza, {
            type: 'doughnut',
            data: { labels: dp.rotulos, datasets: [{ data: dp.valores, backgroundColor: ['#0F1B3D', '#D93F74', '#C9A061'], borderColor: '#fff', borderWidth: 3, hoverOffset: 6 }] },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '64%',
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16 } }, tooltip: { callbacks: { label: (c) => ' ' + c.label + ': ' + moeda(c.parsed) } } },
            },
        });
    }
})();
