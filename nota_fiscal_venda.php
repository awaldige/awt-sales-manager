<?php
include 'config.php';
$caminho_dompdf = __DIR__ . '/dompdf/autoload.inc.php';
require_once $caminho_dompdf;

use Dompdf\Dompdf;

$idVenda = $_GET['id'] ?? 0;

// Busca dados da venda e cliente
$venda = $conn->query("SELECT v.*, c.nome, c.email, c.telefone 
                      FROM vendas v 
                      JOIN cliente c ON v.idCliente = c.idCliente 
                      WHERE v.idVendas = $idVenda")->fetch_assoc();

if (!$venda) die("Venda não encontrada.");

$dompdf = new Dompdf();

$html = '
<style>
    body { font-family: sans-serif; font-size: 12px; color: #333; }
    .header { text-align: center; border-bottom: 2px solid #eee; padding-bottom: 10px; }
    .info-venda { margin: 20px 0; background: #f9f9f9; padding: 15px; border-radius: 5px; }
    .footer { margin-top: 50px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
    table { width: 100%; border-collapse: collapse; }
    th { text-align: left; background: #eee; padding: 5px; }
    td { padding: 5px; border-bottom: 1px solid #eee; }
</style>

<div class="header">
    <h1 style="margin:0; color:#059669;">COMPROVANTE DE VENDA</h1>
    <p>ERP PRO SISTEMAS | CNPJ: 00.000.000/0001-00</p>
</div>

<div class="info-venda">
    <table style="border:none;">
        <tr>
            <td><b>PEDIDO:</b> #'.$venda['idVendas'].'</td>
            <td style="text-align:right"><b>DATA:</b> '.date('d/m/Y H:i', strtotime($venda['dataVenda'])).'</td>
        </tr>
        <tr>
            <td><b>CLIENTE:</b> '.$venda['nome'].'</td>
            <td style="text-align:right"><b>TEL:</b> '.$venda['telefone'].'</td>
        </tr>
    </table>
</div>

<h3>Resumo Financeiro</h3>
<table>
    <thead>
        <tr>
            <th>Descrição</th>
            <th style="text-align:right">Total</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Venda de Produtos/Serviços conforme pedido</td>
            <td style="text-align:right">R$ '.number_format($venda['valorTotal'], 2, ',', '.').'</td>
        </tr>
    </tbody>
</table>

<div style="text-align:right; margin-top:20px;">
    <h2 style="color:#059669;">TOTAL: R$ '.number_format($venda['valorTotal'], 2, ',', '.').'</h2>
</div>

<div class="footer">
    <p>Este documento é um comprovante de venda para controle interno.</p>
    <p>Obrigado pela preferência!</p>
</div>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A5', 'portrait'); // Papel A5 (metade da folha) para parecer cupom
$dompdf->render();
$dompdf->stream("Venda_#$idVenda.pdf", ["Attachment" => false]);