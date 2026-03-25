<?php
include 'config.php';

// Pega o ID da URL
$id = $_GET['id'] ?? null;

if ($id) {
    // Usamos Prepared Statement para segurança
    $sql = "DELETE FROM Fornecedor WHERE idFornecedor = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Redireciona de volta com status de deletado
        header("Location: fornecedores.php?status=deleted");
        exit();
    } else {
        echo "Erro ao excluir: " . $conn->error;
    }
} else {
    header("Location: fornecedores.php");
}
?>