<?php
/**
 * Exclusão de cliente (somente via POST com token CSRF).
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/cliente_funcoes.php';

exigir_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar('clientes.php');
}

if (!csrf_validar()) {
    definir_flash('erro', 'Não foi possível validar a solicitação. Tente novamente.');
    redirecionar('clientes.php');
}

$id      = campo_id($_POST);
$cliente = $id ? buscar_cliente($id) : null;

if (!$cliente) {
    definir_flash('erro', 'Cliente não encontrado ou já excluído.');
    redirecionar('clientes.php');
}

$stmt = db()->prepare('DELETE FROM clientes WHERE id = :id AND usuario_id = :usuario_id');
$stmt->execute([
    ':id'         => $id,
    ':usuario_id' => usuario_id_logado(),
]);

definir_flash('sucesso', 'Cliente "' . $cliente['nome'] . '" excluído com sucesso.');
redirecionar('clientes.php');
