<?php
/**
 * Funções auxiliares do sistema:
 * sessão, segurança (XSS/CSRF), mensagens flash, formatação e ícones.
 */

date_default_timezone_set('America/Sao_Paulo');

// Inicia a sessão com cookies mais seguros.
// O nome próprio evita misturar o login com outros projetos do localhost.
if (session_status() === PHP_SESSION_NONE) {
    session_name('GESTOR_CLIENTES_V2');
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true,
    ]);
}

/* ==========================================================
   Segurança e entrada de dados
   ========================================================== */

/**
 * Escapa um valor para exibição segura no HTML (evita XSS).
 */
function e(?string $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

/**
 * Lê um campo de texto de $_POST ou $_GET.
 * Sempre retorna string (ignora arrays enviados de forma maliciosa).
 */
function campo(array $origem, string $chave): string
{
    $valor = $origem[$chave] ?? '';
    return is_string($valor) ? trim($valor) : '';
}

/**
 * Lê um ID inteiro positivo. Retorna null se for inválido.
 */
function campo_id(array $origem, string $chave = 'id'): ?int
{
    $id = filter_var($origem[$chave] ?? null, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);

    return $id === false ? null : $id;
}

/**
 * Remove tags HTML e espaços extras de um texto (sanitização).
 */
function limpar_texto(string $texto): string
{
    return trim(strip_tags($texto));
}

/**
 * Converte string vazia em null (para campos opcionais no banco).
 */
function nulo_se_vazio(string $valor): ?string
{
    return $valor === '' ? null : $valor;
}

/**
 * Redireciona para outra página e encerra a execução.
 */
function redirecionar(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/* ==========================================================
   Proteção CSRF
   ========================================================== */

/**
 * Gera (ou reaproveita) o token CSRF da sessão.
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Campo oculto com o token, para colocar dentro dos formulários.
 */
function csrf_campo(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Confere se o token enviado no POST é igual ao da sessão.
 */
function csrf_validar(): bool
{
    $token = $_POST['csrf_token'] ?? '';

    return is_string($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

/* ==========================================================
   Mensagens flash (exibidas uma única vez)
   ========================================================== */

/**
 * Guarda uma mensagem para a próxima página. Tipos: sucesso, erro, info.
 */
function definir_flash(string $tipo, string $mensagem): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensagem' => $mensagem];
}

/**
 * Retorna e remove a mensagem flash da sessão.
 */
function obter_flash(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

/* ==========================================================
   Formulários
   ========================================================== */

/**
 * Retorna a classe CSS de erro quando o campo é inválido.
 */
function classe_erro(array $erros, string $campo): string
{
    return isset($erros[$campo]) ? ' is-invalid' : '';
}

/**
 * Retorna a mensagem de erro de validação de um campo.
 */
function mensagem_erro(array $erros, string $campo): string
{
    return isset($erros[$campo])
        ? '<div class="invalid-feedback">' . e($erros[$campo]) . '</div>'
        : '';
}

/* ==========================================================
   Formatação
   ========================================================== */

/**
 * Converte data do banco (Y-m-d H:i:s) para o padrão brasileiro.
 */
function formatar_data(?string $data, bool $comHora = false): string
{
    if (empty($data)) {
        return '—';
    }

    $timestamp = strtotime($data);
    if ($timestamp === false) {
        return '—';
    }

    return $comHora
        ? date('d/m/Y', $timestamp) . ' às ' . date('H:i', $timestamp)
        : date('d/m/Y', $timestamp);
}

/**
 * Formata somente dígitos como telefone: (11) 98765-4321 ou (11) 3456-7890.
 */
function formatar_telefone(string $digitos): string
{
    if (strlen($digitos) === 11) {
        return sprintf('(%s) %s-%s', substr($digitos, 0, 2), substr($digitos, 2, 5), substr($digitos, 7));
    }

    if (strlen($digitos) === 10) {
        return sprintf('(%s) %s-%s', substr($digitos, 0, 2), substr($digitos, 2, 4), substr($digitos, 6));
    }

    return $digitos;
}

/**
 * Retorna as iniciais de um nome (ex.: "Maria Oliveira" => "MO").
 */
function iniciais(string $nome): string
{
    $partes = preg_split('/\s+/u', trim($nome), -1, PREG_SPLIT_NO_EMPTY);

    if (!$partes) {
        return '?';
    }

    $letras = mb_substr($partes[0], 0, 1);
    if (count($partes) > 1) {
        $letras .= mb_substr($partes[count($partes) - 1], 0, 1);
    }

    return mb_strtoupper($letras);
}

/**
 * Retorna um ícone SVG inline (sem dependências externas).
 */
function icone(string $nome): string
{
    $icones = [
        'briefcase'  => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
        'grid'       => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>',
        'users'      => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'user-plus'  => '<path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/>',
        'user-check' => '<path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/>',
        'user-x'     => '<path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="18" y1="8" x2="23" y2="13"/><line x1="23" y1="8" x2="18" y2="13"/>',
        'clock'      => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'log-out'    => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
        'menu'       => '<line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>',
        'search'     => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
        'plus'       => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
        'eye'        => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
        'edit'       => '<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>',
        'trash'      => '<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
        'arrow-left' => '<line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>',
        'inbox'      => '<polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>',
        'sun'        => '<circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>',
        'moon'       => '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>',
    ];

    return '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" '
        . 'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
        . ($icones[$nome] ?? '')
        . '</svg>';
}

/**
 * Botão que alterna entre tema claro e escuro (lua no claro, sol no escuro).
 */
function botao_tema(string $classeExtra = ''): string
{
    $classes = trim('theme-toggle ' . $classeExtra);

    return '<button type="button" class="' . e($classes) . '" data-theme-toggle'
        . ' aria-label="Alternar tema claro/escuro" title="Alternar tema claro/escuro">'
        . '<span class="theme-icon-dark">' . icone('moon') . '</span>'
        . '<span class="theme-icon-light">' . icone('sun') . '</span>'
        . '</button>';
}
