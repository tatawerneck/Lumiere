<?php
// Painel: clientes cadastrados.
class ClienteController
{
    public function index(): void
    {
        exige_admin();
        $busca = texto_limpo($_GET['busca'] ?? '', 60);
        renderiza('admin/clientes', [
            'titulo'   => 'Clientes',
            'secao'    => 'cliente',
            'clientes' => (new Usuario())->listarClientes($busca),
            'busca'    => $busca,
            'inscritos'=> (new Newsletter())->total(),
        ], 'admin');
    }
}
