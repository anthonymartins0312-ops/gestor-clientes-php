<?php
/**
 * Regras de negócio dos clientes: status, consulta e validação.
 */

const STATUS_CLIENTE = [
    'ativo'   => 'Ativo',
    'inativo' => 'Inativo',
];

/**
 * Estrutura vazia usada no formulário de novo cliente.
 */
function cliente_vazio(): array
{
    return [
        'nome'        => '',
        'email'       => '',
        'telefone'    => '',
        'empresa'     => '',
        'status'      => 'ativo',
        'observacoes' => '',
    ];
}

/**
 * Busca um cliente do usuário logado pelo ID.
 * Retorna null se não existir ou se pertencer a outro usuário.
 */
function buscar_cliente(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM clientes WHERE id = :id AND usuario_id = :usuario_id LIMIT 1');
    $stmt->execute([
        ':id'         => $id,
        ':usuario_id' => usuario_id_logado(),
    ]);
    $cliente = $stmt->fetch();

    return $cliente ?: null;
}

/**
 * Verifica se o e-mail já pertence a outro cliente do mesmo usuário.
 */
function email_cliente_em_uso(string $email, ?int $ignorarId = null): bool
{
    $sql    = 'SELECT COUNT(*) FROM clientes WHERE usuario_id = :usuario_id AND email = :email';
    $params = [
        ':usuario_id' => usuario_id_logado(),
        ':email'      => $email,
    ];

    if ($ignorarId !== null) {
        $sql .= ' AND id <> :id';
        $params[':id'] = $ignorarId;
    }

    $stmt = db()->prepare($sql);
    $stmt->execute($params);

    return (int) $stmt->fetchColumn() > 0;
}

/**
 * Sanitiza e valida os dados do formulário de cliente.
 * Retorna [dados_limpos, erros].
 */
function validar_cliente(array $entrada, ?int $ignorarId = null): array
{
    $dados = [
        'nome'        => limpar_texto(campo($entrada, 'nome')),
        'email'       => mb_strtolower(campo($entrada, 'email')),
        'telefone'    => campo($entrada, 'telefone'),
        'empresa'     => limpar_texto(campo($entrada, 'empresa')),
        'status'      => campo($entrada, 'status'),
        'observacoes' => limpar_texto(str_replace("\r\n", "\n", campo($entrada, 'observacoes'))),
    ];

    $erros = [];

    // Nome (obrigatório)
    $tamanhoNome = mb_strlen($dados['nome']);
    if ($tamanhoNome === 0) {
        $erros['nome'] = 'Informe o nome do cliente.';
    } elseif ($tamanhoNome < 3 || $tamanhoNome > 150) {
        $erros['nome'] = 'O nome deve ter entre 3 e 150 caracteres.';
    }

    // E-mail (obrigatório e único)
    if ($dados['email'] === '') {
        $erros['email'] = 'Informe o e-mail do cliente.';
    } elseif (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($dados['email']) > 150) {
        $erros['email'] = 'Informe um e-mail válido.';
    } elseif (email_cliente_em_uso($dados['email'], $ignorarId)) {
        $erros['email'] = 'Já existe um cliente cadastrado com este e-mail.';
    }

    // Telefone (opcional) — aceita 10 ou 11 dígitos com DDD
    if ($dados['telefone'] !== '') {
        $digitos = preg_replace('/\D/', '', $dados['telefone']);

        if (strlen($digitos) < 10 || strlen($digitos) > 11) {
            $erros['telefone'] = 'Informe DDD + número (10 ou 11 dígitos).';
        } else {
            $dados['telefone'] = formatar_telefone($digitos);
        }
    }

    // Empresa (opcional)
    if (mb_strlen($dados['empresa']) > 150) {
        $erros['empresa'] = 'O nome da empresa deve ter no máximo 150 caracteres.';
    }

    // Status (somente valores permitidos)
    if (!array_key_exists($dados['status'], STATUS_CLIENTE)) {
        $erros['status'] = 'Selecione um status válido.';
        $dados['status'] = 'ativo';
    }

    // Observações (opcional)
    if (mb_strlen($dados['observacoes']) > 1000) {
        $erros['observacoes'] = 'As observações devem ter no máximo 1000 caracteres.';
    }

    return [$dados, $erros];
}

/**
 * Badge colorido de status para tabelas e detalhes.
 */
function badge_status(string $status): string
{
    $rotulo = STATUS_CLIENTE[$status] ?? ucfirst($status);

    return '<span class="badge badge-' . e($status) . '">' . e($rotulo) . '</span>';
}
