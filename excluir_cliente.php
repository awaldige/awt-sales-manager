<?php
include 'config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $stmt = $conn->prepare("DELETE FROM Cliente WHERE idCliente = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: clientes.php?status=deleted");
    } else {
        echo "Erro ao excluir: " . $conn->error;
    }
    $stmt->close();
}
$conn->close();
?>