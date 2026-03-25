<?php
include 'config.php';
$cod = $_GET['cod'] ?? '';

// Busca todos os itens do pedido e dados do cliente
$sql = "SELECT p.*, c.Pnome, c.Sobrenome, f.Tipo as Pagamento 
        FROM pedido p 
        LEFT JOIN cliente c ON p.idCliente = c.idCliente 
        LEFT JOIN formapagamento f ON p.idFormaPagamento = f.idFormaPagamento
        WHERE p.codigoVenda = '$cod'";
$res = $conn->query($sql);
$itens = $res->fetch_all(MYSQLI_ASSOC);

if (!$itens) { die("Pedido não encontrado."); }
$totalGeral = 0;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Recibo_<?= $cod ?></title>
    <style>
        /* Estilo para a bobina de papel */
        body { font-family: 'Courier New', Courier, monospace; width: 300px; padding: 20px; color: #000; background: #f4f4f4; margin: 0 auto; }
        .ticket { background: #fff; padding: 10px; box-shadow: 0 0 5px rgba(0,0,0,0.1); }
        
        .header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 10px; margin-bottom: 10px; }
        .item-row { display: flex; justify-content: space-between; margin-bottom: 5px; font-size: 13px; }
        .total-box { border-top: 1px solid #000; margin-top: 15px; padding-top: 10px; text-align: right; font-weight: bold; font-size: 16px; }
        .footer { text-align: center; margin-top: 30px; font-size: 11px; }

        /* Barra de Ações (Voltar e Imprimir) */
        .actions-bar { 
            display: flex; 
            gap: 10px; 
            margin-bottom: 20px; 
            font-family: sans-serif; 
        }
        .btn { 
            text-decoration: none; 
            padding: 12px; 
            border-radius: 8px; 
            font-size: 12px; 
            font-weight: bold; 
            text-transform: uppercase; 
            cursor: pointer; 
            border: none;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-back { background: #e2e8f0; color: #475569; }
        .btn-print-action { background: #2563eb; color: #fff; }

        /* Esconde os botões na hora da impressão */
        @media print { 
            body { background: #fff; padding: 0; }
            .actions-bar { display: none; } 
            .ticket { box-shadow: none; }
        }
    </style>
</head>
<body>

    <div class="actions-bar">
        <a href="pedidos.php" class="btn btn-back">
            <-- Voltar
        </a>
        <button onclick="window.print()" class="btn btn-print-action">
            Imprimir
        </button>
    </div>

    <div class="ticket">
        <div class="header">
            <h2 style="margin: 0; font-size: 18px;">AWTecnology</h2>
            <p style="font-size: 11px; margin: 5px 0; font-weight: bold;">Comprovante de Venda</p>
        </div>

        <div style="font-size: 12px; margin-bottom: 15px; line-height: 1.4;">
            <b>CÓDIGO:</b> <?= $cod ?><br>
            <b>DATA  :</b> <?= date('d/m/Y H:i', strtotime($itens[0]['dataPedido'])) ?><br>
            <b>CLI   :</b> <?= $itens[0]['Pnome'] ?> <?= $itens[0]['Sobrenome'] ?><br>
            <b>PAGTO :</b> <?= $itens[0]['Pagamento'] ?>
        </div>

        <div style="border-bottom: 1px solid #000; margin-bottom: 8px; font-weight: bold; font-size: 11px;">
            DESCRIÇÃO | QTD | TOTAL
        </div>

        <?php foreach($itens as $i): $totalGeral += $i['ValorTotal']; ?>
        <div class="item-row">
            <span><?= substr($i['Descricao'], 0, 16) ?>.</span>
            <span><?= $i['quantidade'] ?>x</span>
            <span><?= number_format($i['ValorTotal'], 2, ',', '.') ?></span>
        </div>
        <?php endforeach; ?>

        <div class="total-box">
            TOTAL: R$ <?= number_format($totalGeral, 2, ',', '.') ?>
        </div>

        <div class="footer">
            *** Obrigado pela Preferência ***<br>
            <span style="font-size: 9px;">Gerado por AWTecnology Dev</span>
        </div>
    </div>

</body>
</html>