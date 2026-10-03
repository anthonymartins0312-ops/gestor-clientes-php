<?php
/**
 * Dashboard: indicadores e últimos clientes cadastrados.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/cliente_funcoes.php';

exigir_login();

$usuarioId = usuario_id_logado();

// Indicadores dos clientes do usuário logado
$stmt = db()->prepare(
    "SELECT
        COUNT(*)                                               AS total,
        COALESCE(SUM(status = 'ativo'), 0)                     AS ativos,
        COALESCE(SUM(status = 'inativo'), 0)                   AS inativos,
        COALESCE(SUM(created_at >= NOW() - INTERVAL 7 DAY), 0) AS recentes
     FROM clientes
     WHERE usuario_id = :usuario_id"
);
$stmt->execute([':usuario_id' => $usuarioId]);
$resumo = $stmt->fetch();

$total    = (int) $resumo['total'];
$ativos   = (int) $resumo['ativos'];
$inativos = (int) $resumo['inativos'];
$recentes = (int) $resumo['recentes'];

$percentualAtivos   = $total > 0 ? (int) round($ativos / $total * 100) : 0;
$percentualInativos = $total > 0 ? 100 - $percentualAtivos : 0;

// Últimos clientes cadastrados pelo usuário logado
$stmt = db()->prepare(
    'SELECT id, nome, email, empresa, status, created_at
     FROM clientes
     WHERE usuario_id = :usuario_id
     ORDER BY created_at DESC, id DESC
     LIMIT 5'
);
$stmt->execute([':usuario_id' => $usuarioId]);
$ultimosClientes = $stmt->fetchAll();

$cards = [
    [
        'rotulo'  => 'Total de clientes',
        'valor'   => $total,
        'icone'   => 'users',
        'cor'     => 'primary',
        'link'    => 'clientes.php',
        'detalhe' => 'Toda a base cadastrada',
    ],
    [
        'rotulo'  => 'Clientes ativos',
        'valor'   => $ativos,
        'icone'   => 'user-check',
        'cor'     => 'success',
        'link'    => 'clientes.php?status=ativo',
        'detalhe' => $percentualAtivos . '% da base',
    ],
    [
        'rotulo'  => 'Clientes inativos',
        'valor'   => $inativos,
        'icone'   => 'user-x',
        'cor'     => 'danger',
        'link'    => 'clientes.php?status=inativo',
        'detalhe' => $percentualInativos . '% da base',
    ],
    [
        'rotulo'  => 'Novos clientes',
        'valor'   => $recentes,
        'icone'   => 'clock',
        'cor'     => 'info',
        'link'    => 'clientes.php',
        'detalhe' => 'Cadastrados nos últimos 7 dias',
    ],
];

$tituloPagina = 'Dashboard';
$paginaAtiva  = 'dashboard';
require __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h2>Visão geral</h2>
        <p>Acompanhe os principais números da sua base de clientes.</p>
    </div>
    <a href="cliente_novo.php" class="btn btn-primary"><?= icone('plus') ?> Novo cliente</a>
</div>

<div class="stats-grid">
    <?php foreach ($cards as $card): ?>
        <a href="<?= e($card['link']) ?>" class="stat-card stat-<?= e($card['cor']) ?>">
            <span class="stat-icon"><?= icone($card['icone']) ?></span>
            <span class="stat-body">
                <span class="stat-label"><?= e($card['rotulo']) ?></span>
                <strong class="stat-value"><?= $card['valor'] ?></strong>
                <small class="stat-detail"><?= e($card['detalhe']) ?></small>
            </span>
        </a>
    <?php endforeach; ?>
</div>

<div class="dashboard-grid">
    <section class="card">
        <div class="card-header">
            <h3>Clientes cadastrados recentemente</h3>
            <a href="clientes.php">Ver todos</a>
        </div>

        <?php if ($ultimosClientes): ?>
            <ul class="recent-list">
                <?php foreach ($ultimosClientes as $cliente): ?>
                    <li>
                        <span class="avatar avatar-sm"><?= e(iniciais($cliente['nome'])) ?></span>
                        <div class="recent-info">
                            <a href="cliente_ver.php?id=<?= (int) $cliente['id'] ?>"><?= e($cliente['nome']) ?></a>
                            <small><?= e($cliente['empresa'] ?: $cliente['email']) ?></small>
                        </div>
                        <div class="recent-meta">
                            <?= badge_status($cliente['status']) ?>
                            <small><?= formatar_data($cliente['created_at']) ?></small>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="empty-state">
                <?= icone('inbox') ?>
                <h3>Nenhum cliente cadastrado</h3>
                <p>Comece cadastrando o seu primeiro cliente.</p>
                <a href="cliente_novo.php" class="btn btn-primary"><?= icone('plus') ?> Cadastrar cliente</a>
            </div>
        <?php endif; ?>
    </section>

    <section class="card">
        <div class="card-header">
            <h3>Status da base</h3>
        </div>
        <div class="card-body">
            <div class="progress-label">
                <span>Ativos</span>
                <strong><?= $percentualAtivos ?>%</strong>
            </div>
            <div class="progress" role="progressbar" aria-label="Clientes ativos"
                 aria-valuenow="<?= $percentualAtivos ?>" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar" style="width: <?= $percentualAtivos ?>%"></div>
            </div>

            <div class="progress-label">
                <span>Inativos</span>
                <strong><?= $percentualInativos ?>%</strong>
            </div>
            <div class="progress" role="progressbar" aria-label="Clientes inativos"
                 aria-valuenow="<?= $percentualInativos ?>" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar progress-bar-danger" style="width: <?= $percentualInativos ?>%"></div>
            </div>

            <div class="quick-actions">
                <span class="quick-title">Atalhos</span>
                <a href="cliente_novo.php" class="btn btn-secondary btn-block"><?= icone('user-plus') ?> Cadastrar cliente</a>
                <a href="clientes.php?status=ativo" class="btn btn-secondary btn-block"><?= icone('user-check') ?> Ver clientes ativos</a>
                <a href="clientes.php?status=inativo" class="btn btn-secondary btn-block"><?= icone('user-x') ?> Ver clientes inativos</a>
            </div>
        </div>
    </section>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
