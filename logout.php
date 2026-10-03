<?php
/**
 * Logout: aceita somente POST com token CSRF válido.
 */

require_once __DIR__ . '/includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validar()) {
    redirecionar(usuario_logado() ? 'dashboard.php' : 'login.php');
}

fazer_logout();
definir_flash('sucesso', 'Você saiu do sistema com segurança.');
redirecionar('login.php');
