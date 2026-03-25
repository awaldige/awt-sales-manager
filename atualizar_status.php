<?php
session_start();
include 'config.php';
include 'auth.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo = $_POST['codigoVenda'];
    $status = $_POST['novoStatus'];

    // Atualiza o status de todos os itens desse pedido
    $stmt = $conn->prepare("UPDATE pedido SET StatusPedido = ? WHERE codigoVenda = ?");
    $stmt->bind_param("ss", $status, $codigo);

    if ($stmt->execute()) {
        // Redireciona de volta com uma mensagem de sucesso
        echo "<script>
                alert('Status do Pedido atualizado com sucesso!');
                window.location.href = 'pedidos.php';
              </script>";
    } else {
        echo "Erro ao atualizar: " . $conn->error;
    }
} else {
    header("Location: pedidos.php");
}
?>