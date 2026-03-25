<?php
// Se o arquivo de auth.php não existir ou der erro, use session_start() diretamente para testar
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include 'config.php'; 

// --- LÓGICA DE DEMONSTRAÇÃO ---
$nivel_real_sessao = $_SESSION['usuario_nivel'] ?? 'colaborador';
$usuario_nivel = 'admin'; 

// --- CONSULTA 1: CARDS DE RESUMO ---
// Verifique se os nomes StatusCompra e ValorTotal estão corretos no seu banco
$resumo_query = $conn->query("SELECT 
    SUM(CASE WHEN StatusCompra = 'Recebido' THEN ValorTotal ELSE 0 END) as total_pago,
    SUM(CASE WHEN StatusCompra = 'Pendente' THEN ValorTotal ELSE 0 END) as total_pendente,
    COUNT(*) as total_pedidos
    FROM compra");
$resumo = $resumo_query ? $resumo_query->fetch_assoc() : [];

// --- CONSULTA 2: GASTOS POR MÊS ---
$gastos_mes = $conn->query("SELECT 
    DATE_FORMAT(DataCompra, '%m/%Y') as mes, 
    SUM(ValorTotal) as total 
    FROM compra 
    WHERE StatusCompra != 'Cancelado'
    GROUP BY mes 
    ORDER BY DataCompra ASC 
    LIMIT 6");

$labels_mes = []; $valores_mes = [];
if($gastos_mes){
    while($row = $gastos_mes->fetch_assoc()){
        $labels_mes[] = $row['mes'];
        $valores_mes[] = (float)$row['total'];
    }
}

// --- CONSULTA 3: TOP FORNECEDORES ---
// IMPORTANTE: Verifique se na sua tabela fornecedor a coluna é 'RazaoSocial' ou 'nome'
$fornecedores_rank = $conn->query("SELECT 
    f.RazaoSocial, 
    SUM(cp.ValorTotal) as total 
    FROM compra cp
    JOIN fornecedor f ON cp.idFornecedor = f.idFornecedor
    WHERE cp.StatusCompra != 'Cancelado'
    GROUP BY f.idFornecedor
    ORDER BY total DESC 
    LIMIT 5");

$labels_forn = []; $valores_forn = [];
if($fornecedores_rank){
    while($row = $fornecedores_rank->fetch_assoc()){
        $labels_forn[] = $row['RazaoSocial'];
        $valores_forn[] = (float)$row['total'];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Analytics de Compras | ERP PRO</title>
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
                <h1 class="text-4xl font-black text-slate-900 uppercase italic">Analytics <span class="text-indigo-600">Compras</span></h1>
            </div>
            <a href="index.php" class="bg-slate-900 text-white p-4 px-8 rounded-2xl font-black text-[10px] uppercase">
                <i class="fas fa-arrow-left mr-2"></i> Voltar
            </a>
        </header>

        <!-- CARDS -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
            <div class="bg-white p-8 rounded-[2rem] shadow-sm border-l-8 border-emerald-500">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Investimento Pago</p>
                <h3 class="text-3xl font-black text-emerald-600">R$ <?= number_format($resumo['total_pago'] ?? 0, 2, ',', '.') ?></h3>
            </div>
            <div class="bg-white p-8 rounded-[2rem] shadow-sm border-l-8 border-amber-500">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Pendente</p>
                <h3 class="text-3xl font-black text-amber-500">R$ <?= number_format($resumo['total_pendente'] ?? 0, 2, ',', '.') ?></h3>
            </div>
            <div class="bg-white p-8 rounded-[2rem] shadow-sm border-l-8 border-indigo-500">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Pedidos</p>
                <h3 class="text-3xl font-black text-indigo-600"><?= $resumo['total_pedidos'] ?? 0 ?></h3>
            </div>
        </div>

        <!-- GRÁFICOS -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white p-10 rounded-[2rem] shadow-sm">
                <h4 class="font-black text-slate-800 uppercase mb-6">Investimento Mensal</h4>
                <div class="chart-container">
                    <canvas id="chartMensal"></canvas>
                </div>
            </div>
            <div class="bg-white p-10 rounded-[2rem] shadow-sm">
                <h4 class="font-black text-slate-800 uppercase mb-6">Top Fornecedores</h4>
                <div class="chart-container">
                    <canvas id="chartFornecedor"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Configuração do Gráfico Mensal
        new Chart(document.getElementById('chartMensal'), {
            type: 'line',
            data: {
                labels: <?= json_encode($labels_mes) ?>,
                datasets: [{
                    label: 'Total R$',
                    data: <?= json_encode($valores_mes) ?>,
                    borderColor: '#6366f1',
                    tension: 0.4,
                    fill: true,
                    backgroundColor: 'rgba(99, 102, 241, 0.1)'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // Configuração do Gráfico de Fornecedores
        new Chart(document.getElementById('chartFornecedor'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($labels_forn) ?>,
                datasets: [{
                    data: <?= json_encode($valores_forn) ?>,
                    backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#f43f5e', '#64748b']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    </script>
</body>
</html>