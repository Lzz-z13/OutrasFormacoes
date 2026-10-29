<?php
// Front controller: único ponto de entrada HTTP do módulo.
require_once __DIR__ . '/Controller/OutrasFormacoesController.php';
$configApp = require __DIR__ . '/Config/app.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start([
        'use_strict_mode' => true,
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
}
header('Cache-Control: no-store');
header('Content-Type: text/html; charset=UTF-8');

$acao = $_GET['acao'] ?? 'listar';
$rotas = ['listar' => 'GET', 'inserir' => 'POST', 'remover' => 'POST'];
if (!is_string($acao) || !isset($rotas[$acao])) {
    http_response_code(404);
    exit('Ação não encontrada.');
}
$metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($metodo !== $rotas[$acao]) {
    header('Allow: ' . $rotas[$acao]);
    http_response_code(405);
    exit('Método não permitido para esta ação.');
}

if (empty($_SESSION['of_csrf']))
    $_SESSION['of_csrf'] = bin2hex(random_bytes(32));
$csrf = $_SESSION['of_csrf'];
$modoDemonstracao = $configApp['modo_demonstracao'] === true;
$erroContexto = '';
$idusuario = 0;
if ($modoDemonstracao) {
    // Não utiliza o ID enviado pelo navegador nem simula login no portal.
    if (in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
        $idusuario = (int) $configApp['usuario_demonstracao'];
    } else {
        $erroContexto = 'A demonstração está disponível apenas neste computador, pelo endereço localhost.';
    }
} else {
    $valorUsuario = $_SESSION['idusuario'] ?? null;
    $usuarioValido = is_int($valorUsuario) || is_string($valorUsuario)
        ? filter_var($valorUsuario, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2147483647]]) : false;
    $idusuario = $usuarioValido === false ? 0 : $usuarioValido;
}
if ($idusuario < 1 && $erroContexto === '')
    $erroContexto = 'Entre no portal para acessar suas formações.';
$controller = new OutrasFormacoesController();

if ($metodo === 'POST') {
    $tokenEnviado = $_POST['csrf_token'] ?? null;
    if (!is_string($tokenEnviado) || !hash_equals($csrf, $tokenEnviado)) {
        $resultado = ['sucesso' => false, 'mensagem' => 'O formulário expirou. Atualize a página e tente novamente.', 'valores' => []];
    } elseif ($erroContexto !== '') {
        $resultado = ['sucesso' => false, 'mensagem' => $erroContexto, 'valores' => []];
    } elseif ($acao === 'inserir') {
        $resultado = $controller->inserir($_POST, $idusuario);
    } else {
        $resultado = $controller->remover($_POST, $idusuario);
    }
    $_SESSION['of_flash'] = $resultado;
    // Post/Redirect/Get: o refresh seguinte repete somente a listagem.
    header('Location: index.php?acao=listar', true, 303);
    exit;
}

$mensagem = $_SESSION['of_flash'] ?? null;
unset($_SESSION['of_flash']);
$valores = array_merge(['txtInicioOF' => '', 'txtFimOF' => '', 'txtDescEP' => ''], $mensagem['valores'] ?? []);
$lista = $erroContexto === ''
    ? $controller->gerarLista($idusuario)
    : ['sucesso' => false, 'mensagem' => $erroContexto, 'itens' => []];
if ($erroContexto !== '')
    http_response_code($modoDemonstracao ? 403 : 401);
$itens = $lista['itens'];
$esc = static function ($valor): string {
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
};
$formatarData = static function (string $valor): string {
    return implode('/', array_reverse(explode('-', $valor)));
};
define('OUTRAS_FORMACOES_VIEW', true);
require __DIR__ . '/View/principal.php';
