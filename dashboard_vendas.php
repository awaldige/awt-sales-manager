<?php
require_once 'auth.php';
require_once 'config.php';

restringirAdmin();
// --- CONSULTA 1: CARDS DE RESUMO (Baseado na tabela 'pedido') ---
$resumo_query = $conn->query("SELECT 
    SUM(CASE WHEN StatusPedido = 'Confirmado' THEN ValorTotal ELSE 0 END) as total_confirmado,
    SUM(CASE WHEN StatusPedido = 'Em processamento' THEN ValorTotal ELSE 0 END) as total_processamento,
    COUNT(DISTINCT codigoVenda) as total_vendas
    FROM pedido");
$resumo = $resumo_query ? $resumo_query->fetch_assoc() : [];

// --- CONSULTA 2: FATURAMENTO POR MÃŠS ---
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
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Vendas | AWT Sales Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .chart-container { position: relative; height: 300px; width: 100%; }
    </style>
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
    <main class="mx-auto w-full max-w-7xl p-4 sm:p-6 lg:p-8">
        <header class="mb-8 flex flex-col gap-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-6">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
            <div>
                <div class="mb-2 flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-lg font-black text-white">AW</span>
                    <span class="text-sm font-extrabold tracking-wide text-slate-700">AWT Sales Manager</span>
                </div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-600">Indicadores e desempenho</p>
                <h1 class="mt-1 text-2xl font-black text-slate-900 sm:text-3xl">Dashboard de Vendas</h1>
                <p class="mt-1 text-sm text-slate-500">Acompanhe os resultados e o desempenho comercial.</p>
            </div>
            <a href="pedidos.php" class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-blue-700">
                <i class="fas fa-arrow-left"></i>
                Histórico de vendas
            </a>
        </header>

        <section class="mb-8">
            <div class="mb-4">
                <h2 class="text-lg font-extrabold text-slate-900">Resumo comercial</h2>
                <p class="text-sm text-slate-500">Visão geral dos pedidos registrados.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <article class="rounded-2xl border border-slate-200 border-l-4 border-l-blue-600 bg-white p-5 shadow-sm sm:p-6">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Pedidos confirmados</p>
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600"><i class="fas fa-check-circle"></i></span>
                    </div>
                    <p class="text-2xl font-black text-slate-900 sm:text-3xl">R$ <?= number_format($resumo['total_confirmado'] ?? 0, 2, ',', '.') ?></p>
                    <p class="mt-2 text-xs text-slate-500">Valor total dos pedidos confirmados</p>
                </article>

                <article class="rounded-2xl border border-slate-200 border-l-4 border-l-violet-500 bg-white p-5 shadow-sm sm:p-6">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Em processamento</p>
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600"><i class="fas fa-clock"></i></span>
                    </div>
                    <p class="text-2xl font-black text-slate-900 sm:text-3xl">R$ <?= number_format($resumo['total_processamento'] ?? 0, 2, ',', '.') ?></p>
                    <p class="mt-2 text-xs text-slate-500">Valor total em processamento</p>
                </article>

                <article class="rounded-2xl border border-slate-200 border-l-4 border-l-slate-700 bg-white p-5 shadow-sm sm:col-span-2 sm:p-6 xl:col-span-1">
                    <div class="mb-4 flex items-center justify-between gap-3">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total de vendas</p>
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-700"><i class="fas fa-shopping-cart"></i></span>
                    </div>
                    <p class="text-2xl font-black text-slate-900 sm:text-3xl"><?= $resumo['total_vendas'] ?? 0 ?></p>
                    <p class="mt-2 text-xs text-slate-500">Quantidade de vendas registradas</p>
                </article>
            </div>
        </section>

        <section>
            <div class="mb-4">
                <h2 class="text-lg font-extrabold text-slate-900">Análise de desempenho</h2>
                <p class="text-sm text-slate-500">Evolução do faturamento e principais clientes.</p>
            </div>

            <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="mb-5 flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600"><i class="fas fa-chart-line"></i></span>
                        <div>
                            <h3 class="font-extrabold text-slate-900">Desempenho de vendas</h3>
                            <p class="text-xs text-slate-500">Faturamento mensal</p>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="chartMensal"></canvas>
                    </div>
                </article>

                <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="mb-5 flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600"><i class="fas fa-users"></i></span>
                        <div>
                            <h3 class="font-extrabold text-slate-900">Principais clientes</h3>
                            <p class="text-xs text-slate-500">Ranking por valor de compras</p>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="chartCliente"></canvas>
                    </div>
                </article>
            </div>
        </section>

        <footer class="mt-8 border-t border-slate-200 py-5 text-center text-xs text-slate-400">
            AWT Sales Manager · Painel de indicadores comerciais
        </footer>
    </main>
<script>
        // ConfiguraÃ§Ã£o do GrÃ¡fico Mensal (Linha Azul)
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

        // ConfiguraÃ§Ã£o do GrÃ¡fico de Clientes (Rosca Colorida)
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