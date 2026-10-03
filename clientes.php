<?php
/**
 * Listagem de clientes com pesquisa, filtro por status e paginação.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/cliente_funcoes.php';

exigir_login();

$opcoesBusca = [
    'todos' => 'Nome e e-mail',
    'nome'  => 'Somente nome',
    'email' => 'Somente e-mail',
];

// ---------- Filtros recebidos pela URL ----------
$busca      = mb_substr(campo($_GET, 'busca'), 0, 100);
$campoBusca = campo($_GET, 'campo');
$status     = campo($_GET, 'status');
$pagina     = campo_id($_GET, 'pagina') ?? 1;
$porPagina  = 10;

if (!array_key_exists($campoBusca, $opcoesBusca)) {
    $campoBusca = 'todos';
}
if (!array_key_exists($status, STATUS_CLIENTE)) {
    $status = '';
}

// ---------- Condições da consulta (sempre com parâmetros) ----------
// Cada usuário enxerga somente os próprios clientes
$condicoes  = ['usuario_id = :usuario_id'];
$parametros = [':usuario_id' => usuario_id_logado()];

if ($busca !== '') {
    // Escapa os curingas do LIKE (% e _) digitados pelo usuário
    $termo = '%' . addcslashes($busca, '%_\\') . '%';

    if ($campoBusca === 'nome') {
        $condicoes[]               = 'nome LIKE :busca_nome';
        $parametros[':busca_nome'] = $termo;
    } elseif ($campoBusca === 'email') {
        $condicoes[]                = 'email LIKE :busca_email';
        $parametros[':busca_email'] = $termo;
    } else {
        $condicoes[]                = '(nome LIKE :busca_nome OR email LIKE :busca_email)';
        $parametros[':busca_nome']  = $termo;
        $parametros[':busca_email'] = $termo;
    }
}

if ($status !== '') {
    $condicoes[]           = 'status = :status';
    $parametros[':status'] = $status;
}

// $where contém apenas trechos fixos; os valores vão nos parâmetros
$where = 'WHERE ' . implode(' AND ', $condicoes);

// ---------- Total de registros (paginação) ----------
$stmt = db()->prepare("SELECT COUNT(*) FROM clientes {$where}");
$stmt->execute($parametros);
$totalRegistros = (int) $stmt->fetchColumn();

$totalPaginas = max(1, (int) ceil($totalRegistros / $porPagina));
$pagina       = min($pagina, $totalPaginas);
$offset       = ($pagina - 1) * $porPagina;

// ---------- Clientes da página atual ----------
$stmt = db()->prepare(
    "SELECT id, nome, email, telefone, empresa, status, created_at
     FROM clientes {$where}
     ORDER BY created_at DESC, id DESC
     LIMIT :limite OFFSET :offset"
);
foreach ($parametros as $chave => $valor) {
    $stmt->bindValue($chave, $valor);
}
$stmt->bindValue(':limite', $porPagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$clientes = $stmt->fetchAll();

// ---------- Dados auxiliares da view ----------
$temFiltro = $busca !== '' || $status !== '';

$filtrosUrl = array_filter([
    'busca'  => $busca,
    'campo'  => $campoBusca !== 'todos' ? $campoBusca : '',
    'status' => $status,
], static fn(string $valor): bool => $valor !== '');

$urlPagina = static fn(int $numero): string => 'clientes.php?' . http_build_query($filtrosUrl + ['pagina' => $numero]);

$primeiroItem = $totalRegistros > 0 ? $offset + 1 : 0;
$ultimoItem   = $offset + count($clientes);

$tituloPagina = 'Clientes';
$paginaAtiva  = 'clientes';
require __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h2>Lista de clientes</h2>
        <p>Pesquise, filtre e gerencie todos os clientes cadastrados.</p>
    </div>
    <a href="cliente_novo.php" class="btn btn-primary"><?= icone('plus') ?> Novo cliente</a>
</div>

<section class="card">
    <form method="get" action="clientes.php" class="filters" role="search">
        <div class="filter-search">
            <?= icone('search') ?>
            <input type="search" name="busca" class="form-control" value="<?= e($busca) ?>" maxlength="100"
                   placeholder="Pesquisar cliente..." aria-label="Pesquisar cliente">
        </div>

        <select name="campo" class="form-control" aria-label="Pesquisar em">
            <?php foreach ($opcoesBusca as $valor => $rotulo): ?>
                <option value="<?= e($valor) ?>" <?= $campoBusca === $valor ? 'selected' : '' ?>><?= e($rotulo) ?></option>
            <?php endforeach; ?>
        </select>

        <select name="status" class="form-control" aria-label="Filtrar por status" data-auto-submit>
            <option value="">Todos os status</option>
            <?php foreach (STATUS_CLIENTE as $valor => $rotulo): ?>
                <option value="<?= e($valor) ?>" <?= $status === $valor ? 'selected' : '' ?>><?= e($rotulo) ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn btn-primary"><?= icone('search') ?> Filtrar</button>
        <a href="clientes.php" class="btn btn-secondary">Limpar filtros</a>
    </form>

    <?php if ($clientes): ?>
        <div class="table-wrapper">
            <table class="table table-responsive">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Telefone</th>
                        <th>Empresa</th>
                        <th>Status</th>
                        <th>Cadastro</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cliente): ?>
                        <tr>
                            <td data-label="Cliente">
                                <div class="client-cell">
                                    <span class="avatar avatar-sm"><?= e(iniciais($cliente['nome'])) ?></span>
                                    <div>
                                        <a href="cliente_ver.php?id=<?= (int) $cliente['id'] ?>" class="client-name">
                                            <?= e($cliente['nome']) ?>
                                        </a>
                                        <small><?= e($cliente['email']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Telefone"><?= e($cliente['telefone'] ?: '—') ?></td>
                            <td data-label="Empresa"><?= e($cliente['empresa'] ?: '—') ?></td>
                            <td data-label="Status"><?= badge_status($cliente['status']) ?></td>
                            <td data-label="Cadastro"><?= formatar_data($cliente['created_at']) ?></td>
                            <td data-label="Ações">
                                <div class="actions">
                                    <a href="cliente_ver.php?id=<?= (int) $cliente['id'] ?>" class="btn-icon"
                                       title="Visualizar" aria-label="Visualizar <?= e($cliente['nome']) ?>">
                                        <?= icone('eye') ?>
                                    </a>
                                    <a href="cliente_editar.php?id=<?= (int) $cliente['id'] ?>" class="btn-icon"
                                       title="Editar" aria-label="Editar <?= e($cliente['nome']) ?>">
                                        <?= icone('edit') ?>
                                    </a>
                                    <form action="cliente_excluir.php" method="post" data-confirm
                                          data-nome="<?= e($cliente['nome']) ?>">
                                        <?= csrf_campo() ?>
                                        <input type="hidden" name="id" value="<?= (int) $cliente['id'] ?>">
                                        <button type="submit" class="btn-icon btn-icon-danger"
                                                title="Excluir" aria-label="Excluir <?= e($cliente['nome']) ?>">
                                            <?= icone('trash') ?>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <span>Exibindo <?= $primeiroItem ?>–<?= $ultimoItem ?> de <?= $totalRegistros ?> cliente(s)</span>

            <?php if ($totalPaginas > 1): ?>
                <nav class="pagination" aria-label="Paginação">
                    <a href="<?= e($urlPagina($pagina - 1)) ?>" class="page-link<?= $pagina === 1 ? ' disabled' : '' ?>"
                       aria-label="Página anterior">&laquo;</a>

                    <?php for ($i = max(1, $pagina - 2); $i <= min($totalPaginas, $pagina + 2); $i++): ?>
                        <a href="<?= e($urlPagina($i)) ?>" class="page-link<?= $i === $pagina ? ' active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>

                    <a href="<?= e($urlPagina($pagina + 1)) ?>" class="page-link<?= $pagina === $totalPaginas ? ' disabled' : '' ?>"
                       aria-label="Próxima página">&raquo;</a>
                </nav>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <?= icone('inbox') ?>
            <?php if ($temFiltro): ?>
                <h3>Nenhum cliente encontrado</h3>
                <p>Tente outros termos de pesquisa ou limpe os filtros.</p>
                <a href="clientes.php" class="btn btn-secondary">Limpar filtros</a>
            <?php else: ?>
                <h3>Nenhum cliente cadastrado ainda</h3>
                <p>Cadastre o primeiro cliente para começar.</p>
                <a href="cliente_novo.php" class="btn btn-primary"><?= icone('plus') ?> Novo cliente</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
