<?php
include 'config.php';

$tabela = $_GET['tabela'];
$id = $_GET['id'];

// Detecta o nome da primeira coluna da tabela (o ID)
$res = $conn->query("SHOW COLUMNS FROM $tabela");
$col_id = $res->fetch_assoc()['Field'];

$sql = "DELETE FROM $tabela WHERE $col_id = $id";

if($conn->query($sql)) {
    echo "<script>alert('Excluído com sucesso!'); window.location.href='index.php';</script>";
} else {
    echo "Erro ao excluir: " . $conn->error;
}
?>