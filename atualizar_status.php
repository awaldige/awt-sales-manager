<?php
require_once 'auth.php';
require_once 'config.php';

// Aceita somente requisições POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: pedidos.php');
    exit();
}

// Valida os dados recebidos.
$codigo = $_POST['codigoVenda'] ?? '';
$status = $_POST['novoStatus'] ?? '';
$token = $_POST['csrf_token'] ?? '';

if (!is_string($codigo) || trim($codigo) === '' || strlen($codigo) > 100) {
    http_response_code(400);
    exit('Código do pedido inválido.');
}

if (!is_string($status) || !in_array($status, [
    'Confirmado',
    'Em processamento',
    'Cancelado'
], true)) {
    http_response_code(400);
    exit('Status do pedido inválido.');
}

// Valida proteção CSRF.
if (
    !is_string($token) ||
    empty($_SESSION['csrf_status_pedido']) ||
    !hash_equals($_SESSION['csrf_status_pedido'], $token)
) {
    http_response_code(403);
    exit('Solicitação inválida. Atualize a página e tente novamente.');
}

try {
    // Confirma que o pedido existe.
    $stmt = $conn->prepare(
        "SELECT idPedido FROM pedido WHERE codigoVenda = ? LIMIT 1"
    );

    if (!$stmt) {
        throw new RuntimeException('Falha ao preparar consulta.');
    }

    $stmt->bind_param("s", $codigo);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $pedidoExiste = $resultado->num_rows > 0;
    $stmt->close();

    if (!$pedidoExiste) {
        http_response_code(404);
        exit('Pedido não encontrado.');
    }

    // Atualiza o status de todos os itens do pedido.
    $stmt = $conn->prepare(
        "UPDATE pedido SET StatusPedido = ? WHERE codigoVenda = ?"
    );

    if (!$stmt) {
        throw new RuntimeException('Falha ao preparar atualização.');
    }

    $stmt->bind_param("ss", $status, $codigo);

    if (!$stmt->execute()) {
        throw new RuntimeException('Falha ao atualizar pedido.');
    }

    $stmt->close();

    echo "<script>
        alert('Status do Pedido atualizado com sucesso!');
        window.location.href = 'pedidos.php';
    </script>";
    exit();

} catch (Throwable $e) {
    error_log('Erro ao atualizar status do pedido: ' . $e->getMessage());
    http_response_code(500);
    exit('Não foi possível atualizar o status do pedido.');
}
?>