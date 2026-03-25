<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $razao    = $_POST['razao'] ?? null;
    $cnpj     = $_POST['cnpj'] ?? null;
    $telefone = $_POST['telefone'] ?? null;
    $email    = $_POST['email'] ?? null; // Novo campo capturado do formulário

    if (!$razao || !$cnpj) {
        die("Razão Social e CNPJ são obrigatórios.");
    }

    $cnpjLimpo = preg_replace('/[^0-9]/', '', $cnpj);

    // Adicionamos 'Email' e mais um '?' na consulta
    $sql = "INSERT INTO fornecedor (RazaoSocial, CNPJ, Telefone, Email) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Agora são 4 strings: "ssss"
        $stmt->bind_param("ssss", $razao, $cnpjLimpo, $telefone, $email);

        if ($stmt->execute()) {
            header("Location: fornecedores.php?status=success");
            exit();
        } else {
            echo "Erro ao salvar: " . $conn->error;
        }
    }
}
?>