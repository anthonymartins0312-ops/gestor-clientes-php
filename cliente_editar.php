<?php
/**
 * Edição de cliente existente.
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

$dados = $cliente;
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$dados, $erros] = validar_cliente($_POST, $id);

    if (!csrf_validar()) {
        $erros['geral'] = 'Sessão expirada ou formulário inválido. Envie novamente.';
    }

    if (!$erros) {
        try {
            $stmt = db()->prepare(
                'UPDATE clientes
                 SET nome = :nome, email = :email, telefone = :telefone, empresa = :empresa,
                     status = :status, observacoes = :observacoes, updated_at = NOW()
                 WHERE id = :id AND usuario_id = :usuario_id'
            );
            $stmt->execute([
                ':nome'        => $dados['nome'],
                ':email'       => $dados['email'],
                ':telefone'    => nulo_se_vazio($dados['telefone']),
                ':empresa'     => nulo_se_vazio($dados['empresa']),
                ':status'      => $dados['status'],
                ':observacoes' => nulo_se_vazio($dados['observacoes']),
                ':id'          => $id,
                ':usuario_id'  => usuario_id_logado(),
            ]);

            definir_flash('sucesso', 'Dados de "' . $dados['nome'] . '" atualizados com sucesso!');
            redirecionar('cliente_ver.php?id=' . $id);
        } catch (PDOException $e) {
            if ($e->getCode() !== '23000') { // 23000 = e-mail duplicado
                throw $e;
            }
            $erros['email'] = 'Já existe um cliente cadastrado com este e-mail.';
        }
    }
}

$tituloPagina = 'Editar cliente';
$paginaAtiva  = 'clientes';
$textoBotao   = 'Salvar alterações';
require __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h2>Editar: <?= e($cliente['nome']) ?></h2>
        <p>Cadastrado em <?= formatar_data($cliente['created_at'], true) ?>.</p>
    </div>
    <div class="page-actions">
        <a href="cliente_ver.php?id=<?= (int) $cliente['id'] ?>" class="btn btn-secondary"><?= icone('eye') ?> Ver detalhes</a>
        <a href="clientes.php" class="btn btn-secondary"><?= icone('arrow-left') ?> Voltar</a>
    </div>
</div>

<?php if (isset($erros['geral'])): ?>
    <div class="alert alert-erro" role="alert"><?= e($erros['geral']) ?></div>
<?php elseif ($erros): ?>
    <div class="alert alert-erro" role="alert">Corrija os campos destacados e tente novamente.</div>
<?php endif; ?>

<section class="card">
    <div class="card-body">
        <?php require __DIR__ . '/includes/cliente_form.php'; ?>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
