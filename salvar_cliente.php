<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Pegamos o nome completo do formulário e tentamos dividir
    $nomeCompleto = trim($_POST['nome']);
    $partes = explode(" ", $nomeCompleto);
    
    $pnome = $partes[0]; // Primeiro nome
    $sobrenome = (count($partes) > 1) ? end($partes) : ""; // Último nome
    
    // O nome do meio serão todas as partes entre o primeiro e o último
    $nomeMeio = "";
    if (count($partes) > 2) {
        for ($i = 1; $i < count($partes) - 1; $i++) {
            $nomeMeio .= $partes[$i] . " ";
        }
    }
    $nomeMeio = trim($nomeMeio);

    // Ajusta o Tipo para o padrão do seu ENUM ('PF' ou 'PJ')
    $tipoForm = $_POST['tipo']; 
    $tipoEnum = ($tipoForm == "F") ? "PF" : "PJ";

    // Limpa o documento (mantém só números)
    $documento = preg_replace('/\D/', '', $_POST['identificacao']);

    // Prepara a SQL com os nomes exatos das suas colunas
    $sql = "INSERT INTO Cliente (Pnome, NomeMeio, Sobrenome, Documento, Tipo) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    // "sssss" significa que estamos enviando 5 strings
    $stmt->bind_param("sssss", $pnome, $nomeMeio, $sobrenome, $documento, $tipoEnum);

    if ($stmt->execute()) {
        header("Location: clientes.php?status=success");
    } else {
        echo "Erro ao cadastrar: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>