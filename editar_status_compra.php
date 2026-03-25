<?php
include 'auth.php';
include 'config.php';

// Pegamos o código do lote via URL
$cod = $_GET['cod'] ?? '';

if (empty($cod)) {
    die("Código de compra não informado.");
}

$conn->begin_transaction();

try {
    // 1. BUSCAR TODOS OS ITENS DESSA COMPRA QUE AINDA ESTÃO PENDENTES
    $stmt = $conn->prepare("SELECT idProduto, quantidade, StatusCompra, ValorTotal FROM compra WHERE codigo_controle = ?");
    $stmt->bind_param("s", $cod);
    $stmt->execute();
    $itens = $stmt->get_result();

    while ($row = $itens->fetch_assoc()) {
        // Só processamos se o status atual for 'Pendente'
        if ($row['StatusCompra'] == 'Pendente') {
            $idProd = $row['idProduto'];
            $qtd = $row['quantidade'];
            // Calculamos o valor unitário para atualizar o preço de custo no cadastro do produto
            $valorUnitario = $row['ValorTotal'] / $qtd;

            // 2. ATUALIZAR ESTOQUE E PREÇO DE CUSTO DE CADA PRODUTO
            $updEstoque = $conn->prepare("UPDATE produtos SET estoque = estoque + ?, preco_custo_atual = ? WHERE idProduto = ?");
            $updEstoque->bind_param("idi", $qtd, $valorUnitario, $idProd);
            $updEstoque->execute();
        }
    }

    // 3. MUDAR O STATUS DE TODOS OS ITENS PARA 'RECEBIDO'
    $updStatus = $conn->prepare("UPDATE compra SET StatusCompra = 'Recebido' WHERE codigo_controle = ?");
    $updStatus->bind_param("s", $cod);
    $updStatus->execute();

    $conn->commit();
    
    echo "<script>
            alert('Baixa efetuada com sucesso! O estoque foi atualizado.');
            window.location.href = 'compra.php?sucesso=1';
          </script>";

} catch (Exception $e) {
    $conn->rollback();
    echo "Erro ao dar baixa no pedido: " . $e->getMessage();
}