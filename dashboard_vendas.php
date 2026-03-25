<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'config.php'; 

// --- CONSULTA 1: CARDS DE RESUMO (Baseado na tabela 'pedido') ---
$resumo_query = $conn->query("SELECT 
    SUM(CASE WHEN StatusPedido = 'Confirmado' THEN ValorTotal ELSE 0 END) as total_recebido,
    SUM(CASE WHEN StatusPedido = 'Pendente' THEN ValorTotal ELSE 0 END) as total_pendente,
    COUNT(DISTINCT codigoVenda) as total_vendas
    FROM pedido");
$resumo = $resumo_query ? $resumo_query->fetch_assoc() : [];

// --- CONSULTA 2: FATURAMENTO POR MÊS ---
$vendas_mes = $conn->query("SELECT 
    DATE_FORMAT(dataPedido, '%m/%Y') as mes, 
    SUM(ValorTotal) as total 
    FROM pedido 
    WHERE StatusPedido != 'Cancelado'
    GROUP BY mes 
    ORDER BY dataPedido ASC 
    LIMIT 6");

$labels_mes = []; $valores_mes = [];
if($vendas_mes){
    while($row = $vendas_mes->fetch_assoc()){
        $labels_mes[] = $row['mes'];
        $valores_mes[] = (float)$row['total'];
    }
}

// --- CONSULTA 3: TOP CLIENTES (Quem mais compra) ---
$clientes_rank = $conn->query("SELECT 
    c.Pnome as nome, 
    SUM(p.ValorTotal) as total 
    FROM pedido p
    JOIN cliente c ON p.idCliente = c.idCliente
    WHERE p.StatusPedido != 'Cancelado'
    GROUP BY c.idCliente
    ORDER BY total DESC 
    LIMIT 5");

$labels_cli = []; $valores_cli = [];
if($clientes_rank){
    while($row = $clientes_rank->fetch_assoc()){
        $labels_cli[] = $row['nome'];
        $valores_cli[] = (float)$row['total'];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Analytics de Vendas | AWTecnology</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .chart-container { position: relative; height: 300px; width: 100%; }
    </style>
</head>
<body class="bg-slate-50 p-6">

    <div class="max-w-7xl mx-auto">
        <header class="flex justify-between items-center mb-10">
            <div>
                <h1 class="text-4xl font-black text-slate-900 uppercase italic">Analytics <span class="text-blue-600">Vendas</span></h1>
            </div>
            <a href="pedidos.php" class="bg-slate-900 text-white p-4 px-8 rounded-2xl font-black text-[10px] uppercase">
                <i class="fas fa-arrow-left mr-2"></i> Histórico de Vendas
            </a>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-white p-8 rounded-[2rem] shadow-sm border-l-8 border-blue-500">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Faturamento Confirmado</p>
                <h3 class="text-3xl font-black text-blue-600">R$ <?= number_format($resumo['total_recebido'] ?? 0, 2, ',', '.') ?></h3>
            </div>
            <div class="bg-white p-8 rounded-[2rem] shadow-sm border-l-8 border-purple-500">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Aguardando Pagamento</p>
                <h3 class="text-3xl font-black text-purple-500">R$ <?= number_format($resumo['total_pendente'] ?? 0, 2, ',', '.') ?></h3>
            </div>
            <div class="bg-white p-8 rounded-[2rem] shadow-sm border-l-8 border-slate-800">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total de Vendas (Qtd)</p>
                <h3 class="text-3xl font-black text-slate-800"><?= $resumo['total_vendas'] ?? 0 ?></h3>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white p-10 rounded-[2rem] shadow-sm">
                <h4 class="font-black text-slate-800 uppercase mb-6">Desempenho de Vendas</h4>
                <div class="chart-container">
                    <canvas id="chartMensal"></canvas>
                </div>
            </div>
            
            <div class="bg-white p-10 rounded-[2rem] shadow-sm">
                <h4 class="font-black text-slate-800 uppercase mb-6">Top Clientes</h4>
                <div class="chart-container">
                    <canvas id="chartCliente"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Configuração do Gráfico Mensal (Linha Azul)
        new Chart(document.getElementById('chartMensal'), {
            type: 'line',
            data: {
                labels: <?= json_encode($labels_mes) ?>,
                datasets: [{
                    label: 'Faturamento R$',
                    data: <?= json_encode($valores_mes) ?>,
                    borderColor: '#2563eb', // Blue-600
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointBackgroundColor: '#2563eb'
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });

        // Configuração do Gráfico de Clientes (Rosca Colorida)
        new Chart(document.getElementById('chartCliente'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($labels_cli) ?>,
                datasets: [{
                    data: <?= json_encode($valores_cli) ?>,
                    backgroundColor: ['#2563eb', '#8b5cf6', '#ec4899', '#f59e0b', '#64748b'],
                    borderWidth: 0,
                    hoverOffset: 20
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
                }
            }
        });
    </script>
</body>
</html>