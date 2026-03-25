<?php
include 'config.php';
$id = $_GET['id'];
$cod = $_GET['cod'];
$qtd = $_GET['qtd'];
$desc = $_GET['desc'];

// 1. Devolve ao estoque
$conn->query("UPDATE produtos SET estoque = estoque + $qtd WHERE Descricao = '$desc'");
// 2. Remove o item do pedido
$conn->query("DELETE FROM pedido WHERE idPedido = $id");

header("Location: editar_pedido.php?cod=$cod");