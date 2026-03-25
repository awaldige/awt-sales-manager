<?php
include 'auth.php';
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // 1. Pegamos os arrays vindos do formulário
    $produtosArray = isset($_POST['produtos']) ? $_POST['produtos'] : [];
    $quantidadesArray = isset($_POST['quantidades']) ? $_POST['quantidades'] : [];
    $idCliente = isset($_POST['idCliente']) ? intval($_POST['idCliente']) : 1; 
    
    // Geramos UM código de venda para agrupar todos os itens no mesmo pedido
    $codigoVenda = "VENDA-" . time() . "-" . rand(100, 999);

    try {
        if (empty($produtosArray)) {
            throw new Exception("Nenhum produto foi selecionado!");
        }

        $conn->begin_transaction();

        // 2. Iniciamos o loop para processar CADA produto da lista
        foreach ($produtosArray as $index => $idProduto) {
            $idProduto = intval($idProduto);
            $quantidade = intval($quantidadesArray[$index]);

            if ($idProduto > 0 && $quantidade > 0) {
                
                // Buscar dados do produto (Descricao, Valor, estoque)
                $stmt = $conn->prepare("SELECT Descricao, Valor, estoque FROM produtos WHERE idProduto = ?");
                $stmt->bind_param("i", $idProduto);
                $stmt->execute();
                $prod = $stmt->get_result()->fetch_assoc();

                if (!$prod) throw new Exception("Produto ID $idProduto não encontrado.");
                if ($prod['estoque'] < $quantidade) throw new Exception("Estoque insuficiente para: " . $prod['Descricao']);

                $valorTotalItem = $prod['Valor'] * $quantidade;
                $descricao = $prod['Descricao'];

                // 3. Gravar na tabela 'pedido' (Um registro para cada item, mas com o mesmo codigoVenda)
                $sqlPedido = $conn->prepare("INSERT INTO pedido (idCliente, Descricao, ValorTotal, quantidade, codigoVenda, StatusPedido, dataPedido) VALUES (?, ?, ?, ?, ?, 'Confirmado', NOW())");
                $sqlPedido->bind_param("isdis", $idCliente, $descricao, $valorTotalItem, $quantidade, $codigoVenda);
                
                if (!$sqlPedido->execute()) throw new Exception("Erro ao inserir item do pedido.");

                // 4. Baixar o estoque individualmente
                $sqlEstoque = $conn->prepare("UPDATE produtos SET estoque = estoque - ? WHERE idProduto = ?");
                $sqlEstoque->bind_param("ii", $quantidade, $idProduto);
                
                if (!$sqlEstoque->execute()) throw new Exception("Erro ao atualizar estoque.");
            }
        }

        $conn->commit();

        echo "<script>
                alert('Pedido com " . count($produtosArray) . " itens registrado com sucesso!');
                window.location.href = 'pedidos.php'; 
              </script>";

    } catch (Exception $e) {
        if(isset($conn)) $conn->rollback();
        echo "<script>
                alert('ERRO: " . $e->getMessage() . "');
                window.history.back();
              </script>";
    }

} else {
    header("Location: pedidos.php");
    exit;
}