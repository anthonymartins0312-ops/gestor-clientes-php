<?php
/**
 * Tela de login.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

exigir_visitante();

$email = '';
$erro  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = mb_strtolower(campo($_POST, 'email'));
    $senha = is_string($_POST['senha'] ?? null) ? $_POST['senha'] : '';

    if (!csrf_validar()) {
        $erro = 'Sessão expirada. Recarregue a página e tente novamente.';
    } elseif ($email === '' || $senha === '') {
        $erro = 'Informe seu e-mail e sua senha.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Informe um e-mail válido.';
    } else {
        $stmt = db()->prepare('SELECT id, nome, email, senha FROM usuarios WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($senha, $usuario['senha'])) {
            // Atualiza o hash caso o algoritmo padrão do PHP tenha mudado
            if (password_needs_rehash($usuario['senha'], PASSWORD_DEFAULT)) {
                $atualizar = db()->prepare('UPDATE usuarios SET senha = :senha WHERE id = :id');
                $atualizar->execute([
                    ':senha' => password_hash($senha, PASSWORD_DEFAULT),
                    ':id'    => $usuario['id'],
                ]);
            }

            fazer_login($usuario);
            definir_flash('sucesso', 'Bem-vindo(a), ' . $usuario['nome'] . '!');
            redirecionar('dashboard.php');
        }

        // Mensagem genérica: não revela se o e-mail existe
        $erro = 'E-mail ou senha incorretos.';
    }
}

$flash = obter_flash();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar | Gestor de Clientes</title>
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

        <h1>Bem-vindo de volta</h1>
        <p class="auth-subtitle">Entre com sua conta para acessar o painel.</p>

        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['tipo']) ?>" role="alert" data-alert>
                <span><?= e($flash['mensagem']) ?></span>
                <button type="button" class="alert-close" aria-label="Fechar mensagem">&times;</button>
            </div>
        <?php endif; ?>

        <?php if ($erro): ?>
            <div class="alert alert-erro" role="alert"><?= e($erro) ?></div>
        <?php endif; ?>

        <form method="post" class="form">
            <?= csrf_campo() ?>

            <div class="form-group">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= e($email) ?>"
                       placeholder="seuemail@exemplo.com" required autofocus autocomplete="email">
            </div>

            <div class="form-group">
                <label for="senha" class="form-label">Senha</label>
                <div class="input-password">
                    <input type="password" id="senha" name="senha" class="form-control"
                           placeholder="Sua senha" required autocomplete="current-password">
                    <button type="button" class="toggle-password" data-toggle-password="senha" aria-label="Mostrar senha">
                        <?= icone('eye') ?>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Entrar</button>
        </form>

        <p class="auth-footer">Ainda não tem conta? <a href="cadastro.php">Cadastre-se</a></p>

        <div class="demo-box">
            <strong>Acesso de demonstração</strong>
            <span>E-mail: admin@example.com</span>
            <span>Senha: admin123</span>
        </div>
    </main>

    <script src="assets/js/script.js"></script>
</body>
</html>
