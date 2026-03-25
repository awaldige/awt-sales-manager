<?php
include 'config.php';

// --- TENTATIVA DE CARREGAMENTO DA BIBLIOTECA ---
// Se usar Composer: 'vendor/autoload.php'
// Se baixar manual: 'dompdf/autoload.inc.php' (ajuste o nome da pasta se necessário)
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
} elseif (file_exists('dompdf/autoload.inc.php')) {
    require_once 'dompdf/autoload.inc.php';
} else {
    die("<div style='font-family:sans-serif; padding:20px;'>
            <h2 style='color:red;'>Erro: Biblioteca Dompdf não encontrada!</h2>
            <p>Certifique-se de que a pasta <b>vendor</b> ou <b>dompdf</b> existe na raiz do projeto.</p>
         </div>");
}

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'Helvetica');
$dompdf = new Dompdf($options);

// 2. Buscar Dados das Compras (Mês Atual)
$mes_atual = date('m');
$ano_atual = date('Y');

$sql = "SELECT cp.*, f.RazaoSocial, p.Descricao as NomeProduto 
        FROM compra cp
        JOIN fornecedor f ON cp.idFornecedor = f.idFornecedor
        JOIN produtos p ON cp.idProduto = p.idProduto
        WHERE MONTH(cp.DataCompra) = '$mes_atual' AND YEAR(cp.DataCompra) = '$ano_atual'
        AND cp.StatusCompra != 'Cancelado'
        ORDER BY cp.DataCompra DESC";

$res = $conn->query($sql);
$totalGeral = 0;

// 3. Montar o HTML com estilo "ERP PRO"
$html = '
<style>
    @page { margin: 1cm; }
    body { font-family: "Helvetica", sans-serif; color: #1e293b; font-size: 12px; }
    .header-table { width: 100%; border-bottom: 2px solid #6366f1; padding-bottom: 10px; margin-bottom: 20px; }
    .title { font-size: 18px; font-weight: bold; color: #6366f1; text-transform: uppercase; }
    .subtitle { font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: 1px; }
    
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th { background-color: #f1f5f9; color: #475569; text-transform: uppercase; font-size: 9px; padding: 10px; text-align: left; border: 1px solid #e2e8f0; }
    td { padding: 8px 10px; border: 1px solid #e2e8f0; font-size: 10px; }
    
    .status-recebido { color: #059669; font-weight: bold; }
    .status-pendente { color: #d97706; font-weight: bold; }
    
    .footer { margin-top: 30px; padding: 15px; background: #f8fafc; border-radius: 8px; text-align: right; }
    .total-label { font-size: 10px; color: #64748b; text-transform: uppercase; }
    .total-valor { font-size: 16px; font-weight: bold; color: #1e293b; }
</style>

<table class="header-table">
    <tr>
        <td>
            <div class="title">ERP PRO | Inteligência de Compras</div>
            <div class="subtitle">Relatório de Movimentação Mensal</div>
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
            <th>Fornecedor</th>
            <th>Produto / Insumo</th>
            <th style="text-align:center">Qtd</th>
            <th>Status</th>
            <th style="text-align:right">Valor Total</th>
        </tr>
    </thead>
    <tbody>';

if($res->num_rows > 0) {
    while($row = $res->fetch_assoc()) {
        $totalGeral += $row['ValorTotal'];
        $data = date('d/m/Y', strtotime($row['DataCompra']));
        $valor = number_format($row['ValorTotal'], 2, ',', '.');
        $statusClass = ($row['StatusCompra'] == 'Recebido') ? 'status-recebido' : 'status-pendente';
        
        $html .= "
            <tr>
                <td>$data</td>
                <td><b>{$row['RazaoSocial']}</b></td>
                <td>{$row['NomeProduto']}</td>
                <td style='text-align:center'>{$row['quantidade']}</td>
                <td class='$statusClass'>{$row['StatusCompra']}</td>
                <td style='text-align:right; font-weight:bold;'>R$ $valor</td>
            </tr>";
    }
} else {
    $html .= '<tr><td colspan="6" style="text-align:center; padding: 20px; color: #94a3b8;">Nenhuma compra registrada neste mês.</td></tr>';
}

$html .= '
    </tbody>
</table>

<div class="footer">
    <span class="total-label">Investimento Total do Período:</span><br>
    <span class="total-valor">R$ '.number_format($totalGeral, 2, ',', '.').'</span>
</div>

<div style="position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8px; color: #cbd5e1;">
    ERP PRO - Sistema de Gestão Inteligente | Página 1 de 1
</div>';

// 4. Finalização
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Relatorio_Compras_".date('m_Y').".pdf", ["Attachment" => false]); // false abre no navegador antes de baixar