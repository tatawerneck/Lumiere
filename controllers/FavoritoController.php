<?php
// Lista de desejos: clientes logados guardam no banco; visitantes, no navegador.
class FavoritoController
{
    public function alternar(): void
    {
        $u = usuario_logado();
        if (!$u) {
            json_resposta(['ok' => true, 'local' => true]);
        }
        $id = (int)($_POST['produto_id'] ?? 0);
        if (!(new Produto())->buscarPublico($id)) {
            json_resposta(['ok' => false], 404);
        }
        json_resposta(['ok' => true, 'favorito' => (new Favorito())->alternar((int)$u['id'], $id)]);
    }

    public function lista(): void
    {
        $u = usuario_logado();
        json_resposta(['ids' => $u ? (new Favorito())->ids((int)$u['id']) : null]);
    }
}
