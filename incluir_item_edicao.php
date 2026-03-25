<?php
include 'config.php';
$cod = $_POST['codigoVenda'];
$idProd = $_POST['idProduto'];
$qtd = $_POST['quantidade'];
$idCliente = $_POST['idCliente'];
$status = $_POST['status'];

$res = $conn->query("SELECT Descricao, Valor FROM produtos WHERE idProduto = $idProd");
$p = $res->fetch_assoc();
$total = $p['Valor'] * $qtd;
$desc = $p['Descricao'];

$conn->query("INSERT INTO pedido (idCliente, Descricao, ValorTotal, quantidade, codigoVenda, StatusPedido, dataPedido) 
              VALUES ('$idCliente', '$desc', '$total', '$qtd', '$cod', '$status', NOW())");

$conn->query("UPDATE produtos SET estoque = estoque - $qtd WHERE idProduto = $idProd");

header("Location: editar_pedido.php?cod=$cod");