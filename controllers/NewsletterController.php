<?php
class NewsletterController
{
    public function inscrever(): void
    {
        $email = mb_strtolower(texto_limpo($_POST['email'] ?? '', 120));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_resposta(['ok' => false, 'mensagem' => 'Digite um e-mail válido.'], 422);
        }
        $novo = (new Newsletter())->inscrever($email);
        json_resposta(['ok' => true, 'mensagem' => $novo ? 'Pronto! Você vai receber nossas novidades.' : 'Este e-mail já está na lista.']);
    }
}
