<?php
/**
 * Cadastro de novo cliente.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/cliente_funcoes.php';

exigir_login();

$dados = cliente_vazio();
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$dados, $erros] = validar_cliente($_POST);

    if (!csrf_validar()) {
        $erros['geral'] = 'Sessão expirada ou formulário inválido. Envie novamente.';
    }

    if (!$erros) {
        try {
            $stmt = db()->prepare(
                'INSERT INTO clientes (usuario_id, nome, email, telefone, empresa, status, observacoes)
                 VALUES (:usuario_id, :nome, :email, :telefone, :empresa, :status, :observacoes)'
            );
            $stmt->execute([
                ':usuario_id'  => usuario_id_logado(),
                ':nome'        => $dados['nome'],
                ':email'       => $dados['email'],
                ':telefone'    => nulo_se_vazio($dados['telefone']),
                ':empresa'     => nulo_se_vazio($dados['empresa']),
                ':status'      => $dados['status'],
                ':observacoes' => nulo_se_vazio($dados['observacoes']),
            ]);

            definir_flash('sucesso', 'Cliente "' . $dados['nome'] . '" cadastrado com sucesso!');
            redirecionar('clientes.php');
        } catch (PDOException $e) {
            if ($e->getCode() !== '23000') { // 23000 = e-mail duplicado
                throw $e;
            }
            $erros['email'] = 'Já existe um cliente cadastrado com este e-mail.';
        }
    }
}

$tituloPagina = 'Novo cliente';
$paginaAtiva  = 'novo';
$textoBotao   = 'Cadastrar cliente';
require __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div>
        <h2>Cadastrar novo cliente</h2>
        <p>Preencha os dados abaixo. Campos com <span class="required">*</span> são obrigatórios.</p>
    </div>
    <a href="clientes.php" class="btn btn-secondary"><?= icone('arrow-left') ?> Voltar</a>
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
