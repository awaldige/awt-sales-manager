<?php
include 'config.php';

// Verifica se os parâmetros necessários existem
if (isset($_GET['id']) && isset($_GET['acao'])) {
    $id = intval($_GET['id']);
    $acao = $_GET['acao'];

    // Define se soma 1 ou subtrai 1
    if ($acao == 'add') {
        $sql = "UPDATE produtos SET estoque = estoque + 1 WHERE idProduto = $id";
    } elseif ($acao == 'sub') {
        // Garante que o estoque não fique negativo (opcional, mas recomendado)
        $sql = "UPDATE produtos SET estoque = GREATEST(0, estoque - 1) WHERE idProduto = $id";
    }

    // Executa a atualização
    if ($conn->query($sql)) {
        // Sucesso: Volta para a página de estoque
        header("Location: estoque.php?sucesso=1");
        exit();
    } else {
        echo "Erro ao atualizar estoque: " . $conn->error;
    }
} else {
    // Se alguém acessar o arquivo direto sem passar ID, volta para o estoque
    header("Location: estoque.php");
    exit();
}
?>