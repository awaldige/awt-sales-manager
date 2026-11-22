<?php
require_once 'auth.php';
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: novo_pedido.php');
    exit;
}

$transacaoAtiva = false;

try {
    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        empty($_SESSION['csrf_nova_venda']) ||
        !hash_equals($_SESSION['csrf_nova_venda'], $token)
    ) {
        throw new InvalidArgumentException('Sessão expirada ou solicitação inválida. Atualize a página.');
    }

    $idCliente = filter_var($_POST['idCliente'] ?? null, FILTER_VALIDATE_INT);
    $idFormaPagamento = filter_var($_POST['idFormaPagamento'] ?? null, FILTER_VALIDATE_INT);

    if (!$idCliente || $idCliente < 1 || !$idFormaPagamento || $idFormaPagamento < 1) {
        throw new InvalidArgumentException('Selecione um cliente e uma forma de pagamento válidos.');
    }

    $produtosIds = $_POST['produtos'] ?? null;
    $quantidades = $_POST['quantidades'] ?? null;

    if (!is_array($produtosIds) || !is_array($quantidades)) {
        throw new InvalidArgumentException('A lista de produtos está inválida.');
    }

    if (
        count($produtosIds) < 1 ||
        count($produtosIds) > 100 ||
        count($produtosIds) !== count($quantidades) ||
        array_keys($produtosIds) !== array_keys($quantidades)
    ) {
        throw new InvalidArgumentException('Confira os produtos e as quantidades informadas.');
    }

    $itensSolicitados = [];

    foreach ($produtosIds as $indice => $produtoInformado) {
        $idProduto = filter_var($produtoInformado, FILTER_VALIDATE_INT);
        $quantidadeTexto = $quantidades[$indice];

        if (!$idProduto || $idProduto < 1) {
            throw new InvalidArgumentException('Selecione um produto válido em todos os itens.');
        }

        if (
            !is_scalar($quantidadeTexto) ||
            !preg_match('/^[0-9]{1,7}$/', (string) $quantidadeTexto)
        ) {
            throw new InvalidArgumentException('Informe quantidades inteiras válidas.');
        }

        $quantidade = (int) $quantidadeTexto;

        if ($quantidade < 1) {
            throw new InvalidArgumentException('A quantidade deve ser maior que zero.');
        }

        if (!isset($itensSolicitados[$idProduto])) {
            $itensSolicitados[$idProduto] = 0;
        }

        $itensSolicitados[$idProduto] += $quantidade;

        if ($itensSolicitados[$idProduto] > 9999999) {
            throw new InvalidArgumentException('Quantidade total de um produto excede o limite permitido.');
        }
    }

    $conn->begin_transaction();
    $transacaoAtiva = true;

    $stmtCliente = $conn->prepare(
        "SELECT idCliente FROM cliente WHERE idCliente = ? FOR UPDATE"
    );
    $stmtCliente->bind_param('i', $idCliente);
    $stmtCliente->execute();
    $stmtCliente->store_result();

    if ($stmtCliente->num_rows !== 1) {
        throw new InvalidArgumentException('O cliente selecionado não foi encontrado.');
    }
    $stmtCliente->close();

    $stmtForma = $conn->prepare(
        "SELECT idFormaPagamento FROM formapagamento WHERE idFormaPagamento = ? FOR UPDATE"
    );
    $stmtForma->bind_param('i', $idFormaPagamento);
    $stmtForma->execute();
    $stmtForma->store_result();

    if ($stmtForma->num_rows !== 1) {
        throw new InvalidArgumentException('A forma de pagamento selecionada não foi encontrada.');
    }
    $stmtForma->close();

    $stmtProduto = $conn->prepare(
        "SELECT idProduto, Descricao, precoVenda, estoque
         FROM produtos
         WHERE idProduto = ?
         FOR UPDATE"
    );

    $stmtInsert = $conn->prepare(
        "INSERT INTO pedido
            (idCliente, idProduto, idFormaPagamento, Descricao, ValorTotal,
             quantidade, codigoVenda, StatusPedido, dataPedido)
         VALUES (?, ?, ?, ?, ?, ?, ?, 'Confirmado', NOW())"
    );

    $codigoVenda = 'VENDA-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));

    foreach ($itensSolicitados as $idProduto => $quantidade) {
        $stmtProduto->bind_param('i', $idProduto);
        $stmtProduto->execute();

        $resultadoProduto = $stmtProduto->get_result();
        $produto = $resultadoProduto->fetch_assoc();

        if (!$produto) {
            throw new InvalidArgumentException('Um dos produtos selecionados não foi encontrado.');
        }

        $estoqueAtual = (int) $produto['estoque'];

        if ($estoqueAtual < $quantidade) {
            throw new InvalidArgumentException(
                'Estoque insuficiente para o produto: ' . $produto['Descricao']
            );
        }

        if (!is_numeric($produto['precoVenda']) || !is_finite((float) $produto['precoVenda'])) {
            throw new RuntimeException('Preço de venda inválido no cadastro de um produto.');
        }

        $precoUnitario = (float) $produto['precoVenda'];

        if ($precoUnitario < 0) {
            throw new RuntimeException('Preço de venda não pode ser negativo.');
        }

        $valorTotalItem = round($precoUnitario * $quantidade, 2);
        $descricao = $produto['Descricao'];

        $stmtInsert->bind_param(
            'iiisdis',
            $idCliente,
            $idProduto,
            $idFormaPagamento,
            $descricao,
            $valorTotalItem,
            $quantidade,
            $codigoVenda
        );

        if (!$stmtInsert->execute()) {
            throw new RuntimeException('Não foi possível registrar um item da venda.');
        }

        $stmtEstoque = $conn->prepare(
            "UPDATE produtos SET estoque = estoque - ? WHERE idProduto = ? AND estoque >= ?"
        );
        $stmtEstoque->bind_param('iii', $quantidade, $idProduto, $quantidade);
        $stmtEstoque->execute();

        if ($stmtEstoque->affected_rows !== 1) {
            $stmtEstoque->close();
            throw new InvalidArgumentException('O estoque foi alterado durante a operação. Confira os produtos e tente novamente.');
        }

        $stmtEstoque->close();
    }

    $stmtProduto->close();
    $stmtInsert->close();

    $conn->commit();
    $transacaoAtiva = false;

    unset($_SESSION['csrf_nova_venda']);
    header('Location: pedidos.php?sucesso=venda');
    exit;

} catch (InvalidArgumentException $e) {
    if ($transacaoAtiva) {
        $conn->rollback();
    }

    $_SESSION['erro_venda'] = $e->getMessage();
    header('Location: novo_pedido.php');
    exit;

} catch (Throwable $e) {
    if ($transacaoAtiva) {
        $conn->rollback();
    }

    error_log('Erro ao processar venda: ' . $e->getMessage());
    $_SESSION['erro_venda'] = 'Não foi possível registrar a venda. Verifique os dados e tente novamente.';
    header('Location: novo_pedido.php');
    exit;
}