<?php
include 'config.php';
include 'auth.php';

if (isset($_GET['cod'])) {
    $codigo = $_GET['cod'];

    // 1. Busca os itens do pedido para saber quanto devolver ao estoque
    $stmtItens = $conn->prepare("SELECT Descricao, quantidade FROM pedido WHERE codigoVenda = ?");
    $stmtItens->bind_param("s", $codigo);
    $stmtItens->execute();
    $itens = $stmtItens->get_result();

    $conn->begin_transaction();

    try {
        while ($item = $itens->fetch_assoc()) {
            $desc = $item['Descricao'];
            $qtd = $item['quantidade'];
            
            // 2. Devolve a quantidade para a tabela produtos (usando a Descricao)
            $updateEstoque = $conn->prepare("UPDATE produtos SET estoque = estoque + ? WHERE Descricao = ?");
            $updateEstoque->bind_param("is", $qtd, $desc);
            $updateEstoque->execute();
        }

        // 3. Exclui o pedido
        $deletePedido = $conn->prepare("DELETE FROM pedido WHERE codigoVenda = ?");
        $deletePedido->bind_param("s", $codigo);
        $deletePedido->execute();

        $conn->commit();
        header("Location: pedidos.php?status=excluido");
    } catch (Exception $e) {
        $conn->rollback();
        die("Erro ao excluir: " . $e->getMessage());
    }
} else {
    header("Location: pedidos.php");
}
?>