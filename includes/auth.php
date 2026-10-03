<?php
/**
 * Autenticação: login, logout e proteção das páginas privadas.
 */

require_once __DIR__ . '/functions.php';

// Tempo máximo de inatividade da sessão (em segundos)
const SESSAO_TEMPO_LIMITE = 1800; // 30 minutos

/**
 * Verifica se existe um usuário autenticado.
 */
function usuario_logado(): bool
{
    return !empty($_SESSION['usuario_id']);
}

/**
 * Retorna o ID do usuário logado (dono dos clientes).
 */
function usuario_id_logado(): int
{
    return (int) ($_SESSION['usuario_id'] ?? 0);
}

/**
 * Protege páginas privadas: redireciona para o login se necessário.
 */
function exigir_login(): void
{
    if (!usuario_logado()) {
        definir_flash('erro', 'Faça login para acessar esta página.');
        redirecionar('login.php');
    }

    $ultimoAcesso = $_SESSION['ultimo_acesso'] ?? time();

    if (time() - $ultimoAcesso > SESSAO_TEMPO_LIMITE) {
        fazer_logout();
        definir_flash('info', 'Sua sessão expirou por inatividade. Entre novamente.');
        redirecionar('login.php');
    }

    $_SESSION['ultimo_acesso'] = time();
}

/**
 * Usuário já logado não precisa ver as telas de login/cadastro.
 */
function exigir_visitante(): void
{
    if (usuario_logado()) {
        redirecionar('dashboard.php');
    }
}

/**
 * Registra o usuário na sessão após validar a senha.
 */
function fazer_login(array $usuario): void
{
    session_regenerate_id(true); // evita fixação de sessão
    unset($_SESSION['csrf_token']);

    $_SESSION['usuario_id']    = (int) $usuario['id'];
    $_SESSION['usuario_nome']  = $usuario['nome'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['ultimo_acesso'] = time();
}

/**
 * Limpa todos os dados da sessão e gera um novo ID.
 */
function fazer_logout(): void
{
    $_SESSION = [];
    session_regenerate_id(true);
}
