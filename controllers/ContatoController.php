<?php
// Formulário de contato público e caixa de mensagens da equipe.
class ContatoController
{
    public function form(): void
    {
        renderiza('publico/contato', [
            'titulo'   => 'Contato · Lumière',
            'metaDesc' => 'Fale com a equipe Lumière Velas Aromáticas.',
        ]);
    }

    public function enviar(): void
    {
        $nome = texto_limpo($_POST['nome'] ?? '', 80);
        $email = mb_strtolower(texto_limpo($_POST['email'] ?? '', 120));
        $mensagem = texto_limpo($_POST['mensagem'] ?? '', 1000);
        stash_old(['nome' => $nome, 'email' => $email, 'mensagem' => $mensagem]);

        if (mb_strlen($nome) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($mensagem) < 5) {
            flash('erro', 'Preencha nome, um e-mail válido e uma mensagem com pelo menos 5 caracteres.');
            redirecionar(url('contato', 'form'));
        }
        (new Contato())->registrar($nome, $email, $mensagem);
        limpa_old();
        flash('ok', 'Mensagem enviada. A equipe responde pelo e-mail informado.');
        redirecionar(url('contato', 'form'));
    }

    public function listar(): void
    {
        exige_admin();
        renderiza('admin/contatos', [
            'titulo'    => 'Mensagens do site',
            'secao'     => 'contato',
            'mensagens' => (new Contato())->listar(),
        ], 'admin');
    }

    public function lido(): void
    {
        exige_admin();
        (new Contato())->marcarLido((int)($_POST['id'] ?? 0));
        flash('ok', 'Mensagem marcada como lida.');
        redirecionar(url('contato', 'listar'));
    }
}
