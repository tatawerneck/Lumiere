<?php
// Painel: relatório de vendas por período.
class RelatorioController
{
    public function index(): void
    {
        exige_admin();
        $de = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($_GET['de'] ?? '')) ? $_GET['de'] : date('Y-m-01');
        $ate = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)($_GET['ate'] ?? '')) ? $_GET['ate'] : date('Y-m-d');
        if ($de > $ate) {
            [$de, $ate] = [$ate, $de];
        }
        renderiza('admin/relatorio', [
            'titulo' => 'Relatório de vendas',
            'secao'  => 'relatorio',
            'de'     => $de,
            'ate'    => $ate,
            'periodo' => (new Metricas())->periodo($de, $ate),
        ], 'admin');
    }
}
