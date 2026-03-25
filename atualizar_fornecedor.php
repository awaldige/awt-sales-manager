<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id       = $_POST['id'];
    $razao    = $_POST['razao'];
    $cnpj     = preg_replace('/[^0-9]/', '', $_POST['cnpj']);
    $telefone = $_POST['telefone'];
    $email    = $_POST['email'];

    $sql = "UPDATE Fornecedor SET RazaoSocial=?, CNPJ=?, Telefone=?, Email=? WHERE idFornecedor=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $razao, $cnpj, $telefone, $email, $id);

    if ($stmt->execute()) {
        header("Location: fornecedores.php?status=success");
    } else {
        echo "Erro ao atualizar: " . $conn->error;
    }
}
?>