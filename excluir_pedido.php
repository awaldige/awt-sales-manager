<?php

require_once 'auth.php';
require_once 'config.php';

restringirAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: pedidos.php');
    exit();
}

// Validação CSRF.
$token = $_POST['csrf_token'] ?? '';

if (
    !is_string($token) ||
    empty($_SESSION['csrf_exclusao_pedido']) ||
    !hash_equals($_SESSION['csrf_exclusao_pedido'], $token)
) {
    http_response_code(403);
    exit('Solicitação inválida. Atualize a página e tente novamente.');
}

// Validação do código da venda.
$codigo = $_POST['cod'] ?? '';

if (!is_string($codigo) || trim($codigo) === '') {
    header('Location: pedidos.php?erro=codigo_invalido');
    exit();
}

$codigo = trim($codigo);

try {
    $conn->begin_transaction();

    // Bloqueia os itens da venda durante a operação.
    $stmtItens = $conn->prepare(
        "SELECT idProduto, quantidade, StatusPedido
         FROM pedido
         WHERE codigoVenda = ?
         FOR UPDATE"
    );

    $stmtItens->bind_param("s", $codigo);
    $stmtItens->execute();

    $itens = $stmtItens->get_result()->fetch_all(MYSQLI_ASSOC);

    if (empty($itens)) {
        throw new RuntimeException('pedido_nao_encontrado');
    }

    // Confere todos os itens antes de devolver qualquer estoque.
    foreach ($itens as $item) {
        $idProduto = (int) ($item['idProduto'] ?? 0);
        $quantidade = (int) ($item['quantidade'] ?? 0);
        $status = $item['StatusPedido'] ?? '';

        if ($idProduto <= 0 || $quantidade <= 0) {
            throw new RuntimeException('produto_sem_identificacao');
        }

        if ($status === 'Cancelado') {
            throw new RuntimeException('pedido_cancelado');
        }

        // Confirma que o produto ainda existe e bloqueia sua linha.
        $stmtProduto = $conn->prepare(
            "SELECT idProduto
             FROM produtos
             WHERE idProduto = ?
             FOR UPDATE"
        );

        $stmtProduto->bind_param("i", $idProduto);
        $stmtProduto->execute();

        if (!$stmtProduto->get_result()->fetch_assoc()) {
            throw new RuntimeException('produto_nao_encontrado');
        }

        $stmtProduto->close();
    }

    // Devolve o estoque pelo identificador correto do produto.
    $stmtEstoque = $conn->prepare(
        "UPDATE produtos
         SET estoque = COALESCE(estoque, 0) + ?
         WHERE idProduto = ?"
    );

    foreach ($itens as $item) {
        $idProduto = (int) $item['idProduto'];
        $quantidade = (int) $item['quantidade'];

        $stmtEstoque->bind_param("ii", $quantidade, $idProduto);
        $stmtEstoque->execute();

        if ($stmtEstoque->affected_rows !== 1) {
            throw new RuntimeException('erro_ao_devolver_estoque');
        }
    }

    $stmtEstoque->close();

    // Exclui todos os itens da venda.
    $stmtExcluir = $conn->prepare(
        "DELETE FROM pedido WHERE codigoVenda = ?"
    );

    $stmtExcluir->bind_param("s", $codigo);
    $stmtExcluir->execute();

    if ($stmtExcluir->affected_rows !== count($itens)) {
        throw new RuntimeException('erro_ao_excluir_pedido');
    }

    $stmtExcluir->close();

    $conn->commit();

    header('Location: pedidos.php?status=excluido');
    exit();

} catch (Throwable $e) {
    $conn->rollback();

    error_log('Erro ao excluir pedido: ' . $e->getMessage());

    $mensagem = $e->getMessage();

    $errosPermitidos = [
        'pedido_nao_encontrado',
        'produto_sem_identificacao',
        'pedido_cancelado',
        'produto_nao_encontrado',
        'erro_ao_devolver_estoque',
        'erro_ao_excluir_pedido'
    ];

    if (!in_array($mensagem, $errosPermitidos, true)) {
        $mensagem = 'erro_ao_excluir_pedido';
    }

    header('Location: pedidos.php?erro=' . urlencode($mensagem));
    exit();
}

?>