<?php
/**
 * Visualização completa dos dados de um cliente.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/cliente_funcoes.php';

exigir_login();

$id      = campo_id($_GET);
$cliente = $id ? buscar_cliente($id) : null;

if (!$cliente) {
    definir_flash('erro', 'Cliente não encontrado.');
    redirecionar('clientes.php');
}

$telefoneDigitos = $cliente['telefone'] ? preg_replace('/\D/', '', $cliente['telefone']) : '';

$tituloPagina = 'Detalhes do cliente';
$paginaAtiva  = 'clientes';
require __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h2>Ficha do cliente</h2>
        <p>Informações completas do cadastro.</p>
    </div>
    <a href="clientes.php" class="btn btn-secondary"><?= icone('arrow-left') ?> Voltar para a lista</a>
</div>

<section class="card">
    <div class="profile-header">
        <span class="avatar avatar-lg"><?= e(iniciais($cliente['nome'])) ?></span>

        <div class="profile-info">
            <h2><?= e($cliente['nome']) ?></h2>
            <p><?= e($cliente['empresa'] ?: 'Empresa não informada') ?></p>
            <?= badge_status($cliente['status']) ?>
        </div>

        <div class="profile-actions">
            <a href="cliente_editar.php?id=<?= (int) $cliente['id'] ?>" class="btn btn-primary">
                <?= icone('edit') ?> Editar
            </a>
            <form action="cliente_excluir.php" method="post" data-confirm data-nome="<?= e($cliente['nome']) ?>">
                <?= csrf_campo() ?>
                <input type="hidden" name="id" value="<?= (int) $cliente['id'] ?>">
                <button type="submit" class="btn btn-danger-outline"><?= icone('trash') ?> Excluir</button>
            </form>
        </div>
    </div>

    <div class="details-grid">
        <div class="detail-item">
            <span class="detail-label">E-mail</span>
            <a class="detail-value" href="mailto:<?= e($cliente['email']) ?>"><?= e($cliente['email']) ?></a>
        </div>

        <div class="detail-item">
            <span class="detail-label">Telefone</span>
            <?php if ($telefoneDigitos !== ''): ?>
                <a class="detail-value" href="tel:+55<?= e($telefoneDigitos) ?>"><?= e($cliente['telefone']) ?></a>
            <?php else: ?>
                <span class="detail-value text-muted">Não informado</span>
            <?php endif; ?>
        </div>

        <div class="detail-item">
            <span class="detail-label">Empresa</span>
            <span class="detail-value"><?= e($cliente['empresa'] ?: 'Não informada') ?></span>
        </div>

        <div class="detail-item">
            <span class="detail-label">Código</span>
            <span class="detail-value">#<?= str_pad((string) $cliente['id'], 5, '0', STR_PAD_LEFT) ?></span>
        </div>

        <div class="detail-item">
            <span class="detail-label">Cadastrado em</span>
            <span class="detail-value"><?= formatar_data($cliente['created_at'], true) ?></span>
        </div>

        <div class="detail-item">
            <span class="detail-label">Última atualização</span>
            <span class="detail-value"><?= formatar_data($cliente['updated_at'], true) ?></span>
        </div>

        <div class="detail-item detail-full">
            <span class="detail-label">Observações</span>
            <?php if (!empty($cliente['observacoes'])): ?>
                <div class="notes"><?= nl2br(e($cliente['observacoes'])) ?></div>
            <?php else: ?>
                <span class="detail-value text-muted">Nenhuma observação registrada.</span>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
