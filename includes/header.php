<?php
/**
 * Cabeçalho das páginas privadas (sidebar + barra superior).
 * Antes de incluir, defina $tituloPagina e $paginaAtiva.
 */

$tituloPagina = $tituloPagina ?? 'Painel';
$paginaAtiva  = $paginaAtiva ?? '';
$flash        = obter_flash();
$nomeUsuario  = $_SESSION['usuario_nome'] ?? 'Usuário';
$emailUsuario = $_SESSION['usuario_email'] ?? '';
$primeiroNome = explode(' ', trim($nomeUsuario))[0];

$menu = [
    'dashboard' => ['url' => 'dashboard.php',    'rotulo' => 'Dashboard',    'icone' => 'grid'],
    'clientes'  => ['url' => 'clientes.php',     'rotulo' => 'Clientes',     'icone' => 'users'],
    'novo'      => ['url' => 'cliente_novo.php', 'rotulo' => 'Novo cliente', 'icone' => 'user-plus'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($tituloPagina) ?> | Gestor de Clientes</title>
    <script src="assets/js/tema.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="app">
    <aside class="sidebar" id="sidebar">
        <a href="dashboard.php" class="brand">
            <span class="brand-logo"><?= icone('briefcase') ?></span>
            <span class="brand-text">Gestor<strong>Clientes</strong></span>
        </a>

        <nav class="sidebar-nav" aria-label="Menu principal">
            <span class="nav-title">Menu</span>
            <?php foreach ($menu as $chave => $item): ?>
                <a href="<?= e($item['url']) ?>" class="nav-link<?= $paginaAtiva === $chave ? ' active' : '' ?>">
                    <?= icone($item['icone']) ?>
                    <span><?= e($item['rotulo']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="sidebar-footer">
            <div class="user-box">
                <span class="avatar"><?= e(iniciais($nomeUsuario)) ?></span>
                <div class="user-info">
                    <strong><?= e($nomeUsuario) ?></strong>
                    <small><?= e($emailUsuario) ?></small>
                </div>
            </div>
            <form action="logout.php" method="post">
                <?= csrf_campo() ?>
                <button type="submit" class="btn-logout"><?= icone('log-out') ?> Sair</button>
            </form>
        </div>
    </aside>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="main">
        <header class="topbar">
            <button type="button" class="menu-toggle" id="menuToggle" aria-label="Abrir menu"
                    aria-controls="sidebar" aria-expanded="false">
                <?= icone('menu') ?>
            </button>
            <h1 class="topbar-title"><?= e($tituloPagina) ?></h1>
            <div class="topbar-user">
                <?= botao_tema() ?>
                <span class="topbar-greeting">Olá, <strong><?= e($primeiroNome) ?></strong></span>
                <span class="avatar avatar-sm"><?= e(iniciais($nomeUsuario)) ?></span>
            </div>
        </header>

        <main class="content">
            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['tipo']) ?>" role="alert" data-alert>
                    <span><?= e($flash['mensagem']) ?></span>
                    <button type="button" class="alert-close" aria-label="Fechar mensagem">&times;</button>
                </div>
            <?php endif; ?>
