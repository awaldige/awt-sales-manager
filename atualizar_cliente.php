<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $nomeCompleto = trim($_POST['nome']);
    $partes = explode(" ", $nomeCompleto);
    
    $pnome = $partes[0];
    $sobrenome = (count($partes) > 1) ? end($partes) : "";
    $nomeMeio = "";
    if (count($partes) > 2) {
        for ($i = 1; $i < count($partes) - 1; $i++) { $nomeMeio .= $partes[$i] . " "; }
    }
    $nomeMeio = trim($nomeMeio);
    $tipo = $_POST['tipo'];
    $documento = preg_replace('/\D/', '', $_POST['documento']);

    $sql = "UPDATE Cliente SET Pnome=?, NomeMeio=?, Sobrenome=?, Documento=?, Tipo=? WHERE idCliente=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $pnome, $nomeMeio, $sobrenome, $documento, $tipo, $id);

    if ($stmt->execute()) {
        header("Location: clientes.php?status=updated");
    } else {
        echo "Erro: " . $stmt->error;
    }
}
?>