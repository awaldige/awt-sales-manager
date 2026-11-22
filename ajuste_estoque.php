<?php

require_once 'auth.php';
require_once 'config.php';

function voltarEstoque(string $resultado): void
{
    header('Location: estoque.php?resultado=' . urlencode($resultado));
    exit();
}

// Aceita somente POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    voltarEstoque('solicitacao_invalida');
}

// Validação CSRF.
$token = $_POST['csrf_token'] ?? '';

if (
    !is_string($token) ||
    empty($_SESSION['csrf_estoque']) ||
    !hash_equals($_SESSION['csrf_estoque'], $token)
) {
    voltarEstoque('csrf');
}

// Validação do produto.
$id = filter_var(
    $_POST['id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

$acao = $_POST['acao'] ?? '';

if (!$id || !in_array($acao, ['add', 'sub'], true)) {
    voltarEstoque('dados_invalidos');
}

try {

    if ($acao === 'add') {

        $stmt = $conn->prepare(
            "UPDATE produtos
             SET estoque = COALESCE(estoque, 0) + 1
             WHERE idProduto = ?"
        );

    } else {

        $stmt = $conn->prepare(
            "UPDATE produtos
             SET estoque = estoque - 1
             WHERE idProduto = ?
             AND estoque > 0"
        );
    }

    $stmt->bind_param('i', $id);
    $stmt->execute();

    $alteracoes = $stmt->affected_rows;
    $stmt->close();

    if ($alteracoes === 1) {
        voltarEstoque('estoque_atualizado');
    }

    // Diferencia produto inexistente de estoque zerado.
    $stmt = $conn->prepare(
        "SELECT idProduto
         FROM produtos
         WHERE idProduto = ?
         LIMIT 1"
    );

    $stmt->bind_param('i', $id);
    $stmt->execute();

    $produtoExiste = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    if (!$produtoExiste) {
        voltarEstoque('produto_nao_encontrado');
    }

    voltarEstoque('estoque_zerado');

} catch (Throwable $e) {

    error_log('Erro no ajuste de estoque: ' . $e->getMessage());

    voltarEstoque('erro_estoque');
}