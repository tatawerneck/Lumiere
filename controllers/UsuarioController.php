<?php
// Painel: administradores e troca de senha.
class UsuarioController
{
    public function index(): void
    {
        exige_admin();
        renderiza('admin/usuarios', [
            'titulo'   => 'Usuários e acesso',
            'secao'    => 'usuario',
            'usuarios' => (new Usuario())->listar(texto_limpo($_GET['busca'] ?? '', 60)),
        ], 'admin');
    }

    public function criar(): void
    {
        exige_admin();
        $nome = texto_limpo($_POST['nome'] ?? '', 80);
        $email = mb_strtolower(texto_limpo($_POST['email'] ?? '', 120));
        $senha = (string)($_POST['senha'] ?? '');
        if (mb_strlen($nome) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($senha) < 8) {
            flash('erro', 'Informe nome, e-mail válido e senha com pelo menos 8 caracteres.');
            redirecionar(url('usuario'));
        }
        try {
            (new Usuario())->criar($nome, $email, password_hash($senha, PASSWORD_DEFAULT), 'admin');
            flash('ok', 'Administrador criado.');
        } catch (PDOException $e) {
            flash('erro', $e->getCode() === '23000' ? 'Já existe uma conta com este e-mail.' : 'Não foi possível criar.');
        }
        redirecionar(url('usuario'));
    }

    public function alternar(): void
    {
        exige_admin();
        $id = (int)($_POST['id'] ?? 0);
        $ativo = ($_POST['ativo'] ?? '0') === '1';
        if ($id === (int)usuario_logado()['id']) {
            flash('erro', 'Você não pode desativar a própria conta.');
            redirecionar(url('usuario'));
        }
        (new Usuario())->alternarAtivo($id, $ativo);
        flash('ok', $ativo ? 'Conta ativada.' : 'Conta desativada.');
        redirecionar(url('usuario'));
    }

    public function senha(): void
    {
        exige_admin();
        $u = usuario_logado();
        $conta = (new Usuario())->buscarPorEmail($u['email']);
        $atual = (string)($_POST['atual'] ?? '');
        $nova = (string)($_POST['nova'] ?? '');
        if (!$conta || !password_verify($atual, $conta['senha'])) {
            flash('erro', 'A senha atual não confere.');
        } elseif (mb_strlen($nova) < 8) {
            flash('erro', 'A nova senha precisa ter pelo menos 8 caracteres.');
        } else {
            (new Usuario())->alterarSenha((int)$conta['id'], password_hash($nova, PASSWORD_DEFAULT));
            flash('ok', 'Senha alterada.');
        }
        redirecionar(url('usuario'));
    }
}
