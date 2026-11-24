<?php
// Login, cadastro e logout. Perfil público é sempre "cliente"; o formulário nunca decide permissões.
class AuthController
{
    private const HASH_FICTICIO = '$2y$10$abcdefghijklmnopqrstuuJ5ZsH9J9oWZ7wPtlkn3ZC4QkZ3pC0Ka';

    public function form(): void
    {
        if (usuario_logado()) {
            redirecionar(destino_pos_login());
        }
        renderiza('publico/login', [
            'titulo'   => 'Entrar · Lumière',
            'metaDesc' => 'Entre na sua conta Lumière.',
            'scripts'  => ['public/assets/js/auth.js'],
        ]);
    }

    public function login(): void
    {
        $email = mb_strtolower(texto_limpo($_POST['email'] ?? '', 120));
        $senha = (string)($_POST['senha'] ?? '');
        stash_old(['email' => $email]);

        if ($this->bloqueado()) {
            flash('erro', 'Muitas tentativas. Aguarde alguns minutos e tente novamente.');
            redirecionar(url('auth', 'form'));
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $senha === '') {
            flash('erro', 'Informe um e-mail válido e a senha.');
            redirecionar(url('auth', 'form'));
        }

        $usuarioModel = new Usuario();
        $conta = $usuarioModel->buscarPorEmail($email);
        $senhaOk = password_verify($senha, $conta['senha'] ?? self::HASH_FICTICIO);

        if (!$conta || !$senhaOk) {
            $this->registraFalha();
            flash('erro', 'Não foi possível entrar. Confira e-mail e senha.');
            redirecionar(url('auth', 'form'));
        }
        if ((int)$conta['ativo'] !== 1) {
            flash('erro', 'Esta conta está inativa. Fale com a equipe pelo formulário de contato.');
            redirecionar(url('auth', 'form'));
        }

        $this->iniciaSessao($conta);
        limpa_old();
        if ($conta['perfil'] === 'admin') {
            flash('ok', 'Bem-vinda ao painel, ' . $conta['nome'] . '.');
            redirecionar(url('dashboard'));
        }
        flash('ok', 'Bem-vinda de volta, ' . $conta['nome'] . '.');
        redirecionar(destino_pos_login());
    }

    public function cadastro(): void
    {
        if (usuario_logado()) {
            redirecionar(destino_pos_login());
        }
        renderiza('publico/cadastro', [
            'titulo'   => 'Criar conta · Lumière',
            'metaDesc' => 'Crie sua conta Lumière para acompanhar pedidos e notas fiscais.',
            'scripts'  => ['public/assets/js/auth.js'],
        ]);
    }

    public function cadastrar(): void
    {
        $nome = texto_limpo($_POST['nome'] ?? '', 80);
        $email = mb_strtolower(texto_limpo($_POST['email'] ?? '', 120));
        $senha = (string)($_POST['senha'] ?? '');
        $confirmar = (string)($_POST['confirmar'] ?? '');
        stash_old(['nome' => $nome, 'email' => $email]);

        $erro = null;
        if (mb_strlen($nome) < 2) {
            $erro = 'Informe seu nome completo.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erro = 'Informe um e-mail válido.';
        } elseif (mb_strlen($senha) < 8 || mb_strlen($senha) > 200) {
            $erro = 'A senha precisa ter entre 8 e 200 caracteres.';
        } elseif (!hash_equals($senha, $confirmar)) {
            $erro = 'A confirmação da senha não confere.';
        }
        if ($erro !== null) {
            flash('erro', $erro);
            redirecionar(url('auth', 'cadastro'));
        }

        $usuarioModel = new Usuario();
        if ($usuarioModel->buscarPorEmail($email)) {
            flash('erro', 'Já existe uma conta com este e-mail. Entre para continuar.');
            redirecionar(url('auth', 'form'));
        }
        try {
            $id = $usuarioModel->criarCliente($nome, $email, password_hash($senha, PASSWORD_DEFAULT));
        } catch (PDOException $e) {
            // Corrida: outro cadastro com o mesmo e-mail chegou primeiro (UNIQUE no banco).
            if ($e->getCode() === '23000') {
                flash('erro', 'Já existe uma conta com este e-mail. Entre para continuar.');
                redirecionar(url('auth', 'form'));
            }
            throw $e;
        }

        $this->iniciaSessao(['id' => $id, 'nome' => $nome, 'email' => $email, 'perfil' => 'cliente']);
        limpa_old();
        flash('ok', 'Conta criada. Bem-vinda à Lumière!');
        redirecionar(destino_pos_login());
    }

    public function sair(): void
    {
        $_SESSION = [];
        session_destroy();
        session_start();
        session_regenerate_id(true);
        flash('ok', 'Você saiu da sua conta.');
        redirecionar(url('home'));
    }

    // ---------- internos ----------
    private function iniciaSessao(array $conta): void
    {
        session_regenerate_id(true);
        $_SESSION['usuario'] = [
            'id'     => (int)$conta['id'],
            'nome'   => $conta['nome'],
            'email'  => $conta['email'],
            'perfil' => $conta['perfil'],
        ];
        unset($_SESSION['login_falhas'], $_SESSION['login_bloqueio']);
    }

    // Limite simples por sessão: após N falhas, bloqueia o login por um período.
    private function bloqueado(): bool
    {
        $ate = $_SESSION['login_bloqueio'] ?? 0;
        if ($ate > time()) {
            return true;
        }
        unset($_SESSION['login_bloqueio']);
        return false;
    }

    private function registraFalha(): void
    {
        $falhas = ($_SESSION['login_falhas'] ?? 0) + 1;
        $_SESSION['login_falhas'] = $falhas;
        if ($falhas >= (int)config('login_max_tentativas')) {
            $_SESSION['login_bloqueio'] = time() + (int)config('login_bloqueio_seg');
            $_SESSION['login_falhas'] = 0;
        }
    }
}
