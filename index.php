<?php
/**
 * Página inicial: envia o usuário para o dashboard ou para o login.
 */

require_once __DIR__ . '/includes/auth.php';

redirecionar(usuario_logado() ? 'dashboard.php' : 'login.php');
