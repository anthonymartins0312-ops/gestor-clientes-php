<?php
/**
 * Cadastro de novo usuário do sistema.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

exigir_visitante();

$dados = ['nome' => '', 'email' => ''];
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dados['nome']  = limpar_texto(campo($_POST, 'nome'));
    $dados['email'] = mb_strtolower(campo($_POST, 'email'));
    $senha          = is_string($_POST['senha'] ?? null) ? $_POST['senha'] : '';
    $confirmacao    = is_string($_POST['confirmar_senha'] ?? null) ? $_POST['confirmar_senha'] : '';

    if (!csrf_validar()) {
        $erros['geral'] = 'Sessão expirada. Recarregue a página e tente novamente.';
    }

    // Nome
    $tamanhoNome = mb_strlen($dados['nome']);
    if ($tamanhoNome < 3 || $tamanhoNome > 100) {
        $erros['nome'] = 'Informe seu nome (entre 3 e 100 caracteres).';
    }

    // E-mail (válido e ainda não cadastrado)
    if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($dados['email']) > 150) {
        $erros['email'] = 'Informe um e-mail válido.';
    } else {
        $stmt = db()->prepare('SELECT id FROM usuarios WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $dados['email']]);

        if ($stmt->fetch()) {
            $erros['email'] = 'Este e-mail já está cadastrado.';
        }
    }

    // Senha
    if (strlen($senha) < 8) {
        $erros['senha'] = 'A senha deve ter pelo menos 8 caracteres.';
    } elseif (strlen($senha) > 72) {
        $erros['senha'] = 'A senha deve ter no máximo 72 caracteres.';
    } elseif (!preg_match('/[A-Za-z]/', $senha) || !preg_match('/\d/', $senha)) {
        $erros['senha'] = 'A senha deve conter letras e números.';
    }

    if ($senha !== $confirmacao) {
        $erros['confirmar_senha'] = 'As senhas não conferem.';
    }

    if (!$erros) {
        try {
            $stmt = db()->prepare('INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)');
            $stmt->execute([
                ':nome'  => $dados['nome'],
                ':email' => $dados['email'],
                ':senha' => password_hash($senha, PASSWORD_DEFAULT),
            ]);

            definir_flash('sucesso', 'Conta criada com sucesso! Faça login para continuar.');
            redirecionar('login.php');
        } catch (PDOException $e) {
            if ($e->getCode() !== '23000') { // 23000 = violação de chave única (e-mail duplicado)
                throw $e;
            }
            $erros['email'] = 'Este e-mail já está cadastrado.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar conta | Gestor de Clientes</title>
    <script src="assets/js/tema.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
    <?= botao_tema('auth-theme-toggle') ?>

    <main class="auth-card">
        <div class="auth-brand">
            <span class="brand-logo"><?= icone('briefcase') ?></span>
            <span class="brand-text">Gestor<strong>Clientes</strong></span>
        </div>

        <h1>Criar conta</h1>
        <p class="auth-subtitle">Cadastre-se para gerenciar seus clientes.</p>

        <?php if (isset($erros['geral'])): ?>
            <div class="alert alert-erro" role="alert"><?= e($erros['geral']) ?></div>
        <?php endif; ?>

        <form method="post" class="form">
            <?= csrf_campo() ?>

            <div class="form-group">
                <label for="nome" class="form-label">Nome completo</label>
                <input type="text" id="nome" name="nome" class="form-control<?= classe_erro($erros, 'nome') ?>"
                       value="<?= e($dados['nome']) ?>" maxlength="100" required autocomplete="name"
                       placeholder="Seu nome">
                <?= mensagem_erro($erros, 'nome') ?>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" id="email" name="email" class="form-control<?= classe_erro($erros, 'email') ?>"
                       value="<?= e($dados['email']) ?>" maxlength="150" required autocomplete="email"
                       placeholder="seuemail@exemplo.com">
                <?= mensagem_erro($erros, 'email') ?>
            </div>

            <div class="form-group">
                <label for="senha" class="form-label">Senha</label>
                <div class="input-password">
                    <input type="password" id="senha" name="senha" class="form-control<?= classe_erro($erros, 'senha') ?>"
                           minlength="8" maxlength="72" required autocomplete="new-password"
                           placeholder="Mínimo de 8 caracteres">
                    <button type="button" class="toggle-password" data-toggle-password="senha" aria-label="Mostrar senha">
                        <?= icone('eye') ?>
                    </button>
                </div>
                <?= mensagem_erro($erros, 'senha') ?>
                <small class="form-hint">Use pelo menos 8 caracteres, com letras e números.</small>
            </div>

            <div class="form-group">
                <label for="confirmar_senha" class="form-label">Confirmar senha</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha"
                       class="form-control<?= classe_erro($erros, 'confirmar_senha') ?>"
                       minlength="8" maxlength="72" required autocomplete="new-password"
                       placeholder="Repita a senha">
                <?= mensagem_erro($erros, 'confirmar_senha') ?>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Criar conta</button>
        </form>

        <p class="auth-footer">Já tem uma conta? <a href="login.php">Entrar</a></p>
    </main>

    <script src="assets/js/script.js"></script>
</body>
</html>
