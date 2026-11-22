<?php
include 'auth.php';
include 'config.php';

function voltarCompra(string $parametro): void
{
    header("Location: compra.php?" . $parametro);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    voltarCompra('erro=solicitacao');
}

$cod = trim((string) ($_POST['cod'] ?? ''));
$token = (string) ($_POST['csrf_token'] ?? '');
$tokenSessao = (string) ($_SESSION['csrf_recebimento_compra'] ?? '');

if ($cod === '' || strlen($cod) > 50) {
    voltarCompra('erro=solicitacao');
}

if ($tokenSessao === '' || !hash_equals($tokenSessao, $token)) {
    voltarCompra('erro=csrf');
}

try {
    $conn->begin_transaction();

    // Bloqueia os itens do lote durante o processamento.
    $stmt = $conn->prepare(
        "SELECT idCompra, idProduto, quantidade, StatusCompra, ValorTotal
         FROM compra
         WHERE codigo_controle = ?
         FOR UPDATE"
    );

    $stmt->bind_param("s", $cod);
    $stmt->execute();

    $resultado = $stmt->get_result();
    $itens = [];

    while ($row = $resultado->fetch_assoc()) {
        $itens[] = $row;
    }

    $stmt->close();

    if (count($itens) === 0) {
        throw new RuntimeException('Compra não encontrada.');
    }

    $pendentes = [];

    foreach ($itens as $item) {
        if ($item['StatusCompra'] === 'Cancelado') {
            throw new RuntimeException('Compra cancelada não pode ser recebida.');
        }

        if ($item['StatusCompra'] === 'Pendente') {
            $idProduto = filter_var(
                $item['idProduto'],
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            $quantidade = filter_var(
                $item['quantidade'],
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            $valorTotal = $item['ValorTotal'];

            if (
                $idProduto === false ||
                $quantidade === false ||
                $valorTotal === null ||
                !is_numeric($valorTotal) ||
                !is_finite((float) $valorTotal) ||
                (float) $valorTotal < 0
            ) {
                throw new RuntimeException('Dados inválidos em um dos itens da compra.');
            }

            $pendentes[] = [
                'idProduto' => $idProduto,
                'quantidade' => $quantidade,
                'valorUnitario' => (float) $valorTotal / $quantidade
            ];
        }
    }

    // Evita atualizar o estoque novamente em um lote já recebido.
    if (count($pendentes) === 0) {
        $conn->rollback();
        voltarCompra('info=ja_recebido');
    }

    $updEstoque = $conn->prepare(
        "UPDATE produtos
         SET estoque = estoque + ?, preco_custo_atual = ?
         WHERE idProduto = ?"
    );

    foreach ($pendentes as $item) {
        $idProduto = $item['idProduto'];
        $quantidade = $item['quantidade'];
        $valorUnitario = $item['valorUnitario'];

        $updEstoque->bind_param(
            "idi",
            $quantidade,
            $valorUnitario,
            $idProduto
        );

        $updEstoque->execute();

        if ($updEstoque->affected_rows !== 1) {
            throw new RuntimeException('Não foi possível atualizar um produto do estoque.');
        }
    }

    $updEstoque->close();

    // Altera somente os itens que ainda estavam pendentes.
    $updStatus = $conn->prepare(
        "UPDATE compra
         SET StatusCompra = 'Recebido'
         WHERE codigo_controle = ?
         AND StatusCompra = 'Pendente'"
    );

    $updStatus->bind_param("s", $cod);
    $updStatus->execute();

    if ($updStatus->affected_rows !== count($pendentes)) {
        throw new RuntimeException('Não foi possível confirmar todos os itens recebidos.');
    }

    $updStatus->close();

    $conn->commit();

    voltarCompra('sucesso=1');

} catch (Throwable $e) {
    $conn->rollback();

    error_log(
        'Erro ao receber compra ' . $cod . ': ' . $e->getMessage()
    );

    voltarCompra('erro=recebimento');
}
