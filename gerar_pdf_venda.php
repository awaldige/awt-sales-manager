<?php
include 'config.php';

// --- SISTEMA DE CARREGAMENTO SEGURO (O MESMO QUE FUNCIONOU NAS COMPRAS) ---
$caminho_dompdf = __DIR__ . '/dompdf/autoload.inc.php';

if (file_exists($caminho_dompdf)) {
    require_once $caminho_dompdf;
} elseif (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
} else {
    die("Erro: Biblioteca Dompdf não encontrada em: $caminho_dompdf");
}

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Helvetica');
$dompdf = new Dompdf($options);

// 2. Filtro de Data (Mês Atual)
$mes_atual = date('m');
$ano_atual = date('Y');

// SQL que traz a Venda, o Cliente e o Vendedor (Usuário)
$sql = "SELECT v.*, c.nome as NomeCliente, u.nome as NomeVendedor 
        FROM vendas v
        JOIN cliente c ON v.idCliente = c.idCliente
        JOIN usuarios u ON v.idUsuario = u.idUsuario
        WHERE MONTH(v.dataVenda) = '$mes_atual' AND YEAR(v.dataVenda) = '$ano_atual'
        ORDER BY v.dataVenda DESC";

$res = $conn->query($sql);
$faturamentoTotal = 0;
$totalVendas = 0;

// 3. Estrutura HTML do Relatório de Vendas
$html = '
<style>
    @page { margin: 1cm; }
    body { font-family: "Helvetica", sans-serif; color: #1e293b; font-size: 12px; }
    .header-table { width: 100%; border-bottom: 2px solid #10b981; padding-bottom: 10px; margin-bottom: 20px; }
    .title { font-size: 18px; font-weight: bold; color: #059669; text-transform: uppercase; }
    .subtitle { font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: 1px; }
    
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th { background-color: #f0fdf4; color: #166534; text-transform: uppercase; font-size: 9px; padding: 10px; text-align: left; border: 1px solid #dcfce7; }
    td { padding: 8px 10px; border: 1px solid #f1f5f9; font-size: 10px; }
    
    .footer { margin-top: 30px; padding: 15px; background: #f0fdf4; border-radius: 8px; text-align: right; border: 1px solid #dcfce7; }
    .total-label { font-size: 10px; color: #166534; text-transform: uppercase; font-weight: bold; }
    .total-valor { font-size: 18px; font-weight: bold; color: #059669; }
    .badge { font-size: 8px; padding: 2px 5px; background: #f1f5f9; border-radius: 4px; color: #475569; }
</style>

<table class="header-table">
    <tr>
        <td>
            <div class="title">ERP PRO | Relatório de Vendas</div>
            <div class="subtitle">Análise de Faturamento Mensal</div>
        </td>
        <td style="text-align: right; font-size: 9px; color: #94a3b8;">
            Gerado em: '.date('d/m/Y H:i').'<br>
            Competência: '.$mes_atual.'/'.$ano_atual.'
        </td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th>Data</th>
            <th>ID</th>
            <th>Cliente</th>
            <th>Vendedor</th>
            <th style="text-align:right">Valor Total</th>
        </tr>
    </thead>
    <tbody>';

if($res && $res->num_rows > 0) {
    while($row = $res->fetch_assoc()) {
        $faturamentoTotal += $row['valorTotal'];
        $totalVendas++;
        $dataVenda = date('d/m/Y', strtotime($row['dataVenda']));
        $valorVenda = number_format($row['valorTotal'], 2, ',', '.');
        
        $html .= "
            <tr>
                <td>$dataVenda</td>
                <td><span class='badge'>#{$row['idVendas']}</span></td>
                <td><b>{$row['NomeCliente']}</b></td>
                <td>{$row['NomeVendedor']}</td>
                <td style='text-align:right; font-weight:bold;'>R$ $valorVenda</td>
            </tr>";
    }
} else {
    $html .= '<tr><td colspan="5" style="text-align:center; padding: 30px; color: #94a3b8; italic">Nenhuma venda concretizada neste período.</td></tr>';
}

$html .= '
    </tbody>
</table>

<div class="footer">
    <table style="width: 100%; border: none; margin-top: 0;">
        <tr>
            <td style="border: none; text-align: left;">
                <span class="total-label">Volume de Vendas:</span><br>
                <span style="font-size: 14px; font-weight: bold; color: #475569;">'.$totalVendas.' pedidos</span>
            </td>
            <td style="border: none; text-align: right;">
                <span class="total-label">Faturamento Total:</span><br>
                <span class="total-valor">R$ '.number_format($faturamentoTotal, 2, ',', '.').'</span>
            </td>
        </tr>
    </table>
</div>

<div style="position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8px; color: #cbd5e1; font-style: italic;">
    Relatório gerado pelo módulo comercial ERP PRO. Documento para fins de controle interno.
</div>';

// 4. Finalização e Saída
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Relatorio_Vendas_".date('m_Y').".pdf", ["Attachment" => false]);