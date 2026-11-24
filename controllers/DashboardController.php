<?php
// Painel: visão geral da loja.
class DashboardController
{
    public function index(): void
    {
        exige_admin();
        $m = new Metricas();
        renderiza('admin/dashboard', [
            'titulo'       => 'Dashboard',
            'secao'        => 'dashboard',
            'resumo'       => $m->resumoMes(),
            'serie'        => $m->vendasPorDia(30),
            'maisVendidos' => $m->maisVendidos(5),
            'pagamentos'   => $m->porPagamento(),
            'ultimos'      => $m->ultimosPedidos(6),
            'estoqueBaixo' => (new Produto())->estoqueBaixo(),
            'scripts'      => [asset('public/assets/js/vendor/chart.umd.min.js'), asset('public/assets/js/admin-graficos.js')],
        ], 'admin');
    }
}
