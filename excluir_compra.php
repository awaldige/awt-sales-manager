<?php
include 'auth.php'; 
include 'config.php';

if (isset($_GET['cod']) && !empty($_GET['cod'])) {
    $cod = $_GET['cod'];

    // 1. Verificar se é um código novo (COMPRA-...) ou um ID antigo (AVULSO-X)
    // Se o código começar com 'AVULSO-', extraímos apenas o ID numérico
    if (strpos($cod, 'AVULSO-') !== false) {
        $idAntigo = str_replace('AVULSO-', '', $cod);
        $sqlBusca = "SELECT idProduto, quantidade, StatusCompra FROM compra WHERE idCompra = ?";
        $params = [$idAntigo, "i"];
        $sqlDelete = "DELETE FROM compra WHERE idCompra = ?";
    } else {
        // É um lote novo agrupado pelo código
        $sqlBusca = "SELECT idProduto, quantidade, StatusCompra FROM compra WHERE codigo_controle = ?";
        $params = [$cod, "s"];
        $sqlDelete = "DELETE FROM compra WHERE codigo_controle = ?";
    }

    $stmt = $conn->prepare($sqlBusca);
    $stmt->bind_param($params[1], $params[0]);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $conn->begin_transaction();
        try {
            while ($compra = $resultado->fetch_assoc()) {
                // Estorno de estoque se já foi recebido
                if ($compra['StatusCompra'] == 'Recebido') {
                    $upd = $conn->prepare("UPDATE produtos SET estoque = estoque - ? WHERE idProduto = ?");
                    $upd->bind_param("ii", $compra['quantidade'], $compra['idProduto']);
                    $upd->execute();
                }
            }

            // Deletar o registro ou o lote
            $del = $conn->prepare($sqlDelete);
            $del->bind_param($params[1], $params[0]);
            $del->execute();

            $conn->commit();
            echo "<script>alert('Excluído com sucesso!'); window.location.href = 'compra.php';</script>";
        } catch (Exception $e) {
            $conn->rollback();
            echo "Erro: " . $e->getMessage();
        }
    } else {
        echo "<script>alert('Erro: Registro não localizado no banco (Cod: $cod)'); window.location.href='compra.php';</script>";
    }
} else {
    header("Location: compra.php");
}
exit;