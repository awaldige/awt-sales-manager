<?php
include 'config.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    try {
        $stmt = $conn->prepare("DELETE FROM formapagamento WHERE idFormaPagamento = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // AJUSTE AQUI: O nome deve ser exatamente o do seu ficheiro
            header("Location: formas_pagamento.php?excluido=1"); 
            exit();
        }
    } catch (Exception $e) {
        // Se houver erro de chave estrangeira (já usada em compras)
        echo "<script>
                alert('Erro: Esta forma está em uso em algum registo de compra.');
                window.location.href='formas_pagamento.php';
              </script>";
    }
}