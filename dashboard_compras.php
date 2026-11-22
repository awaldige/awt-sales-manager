<?php
require_once 'auth.php';
require_once 'config.php';

restringirAdmin();

// --- CONSULTA 1: CARDS DE RESUMO ---
// Verifique se os nomes StatusCompra e ValorTotal estÃ£o corretos no seu banco
$resumo_query = $conn->query("SELECT 
    SUM(CASE WHEN StatusCompra = 'Recebido' THEN ValorTotal ELSE 0 END) as total_pago,
    SUM(CASE WHEN StatusCompra = 'Pendente' THEN ValorTotal ELSE 0 END) as total_pendente,
    COUNT(*) as total_pedidos
    FROM compra");
$resumo = $resumo_query ? $resumo_query->fetch_assoc() : [];

// --- CONSULTA 2: GASTOS POR MÃŠS ---
$gastos_mes = $conn->query("SELECT 
    DATE_FORMAT(DataCompra, '%m/%Y') as mes, 
    SUM(ValorTotal) as total 
    FROM compra 
    WHERE StatusCompra = 'Recebido'
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
// IMPORTANTE: Verifique se na sua tabela fornecedor a coluna Ã© 'RazaoSocial' ou 'nome'
$fornecedores_rank = $conn->query("SELECT 
    f.RazaoSocial, 
    SUM(cp.ValorTotal) as total 
    FROM compra cp
    JOIN fornecedor f ON cp.idFornecedor = f.idFornecedor
    WHERE cp.StatusCompra = 'Recebido'
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
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Compras | AWT Sales Manager</title>
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
    <main class="mx-auto max-w-7xl p-4 sm:p-6 lg:p-8">

        <header class="mb-8 flex flex-col gap-5 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-7">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-blue-600 text-xl font-black text-white shadow-lg shadow-blue-600/20">
                    AW
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-blue-600">AWT Sales Manager</p>
                    <h1 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Dashboard de Compras</h1>
                    <p class="mt-1 text-sm text-slate-500">Acompanhe os gastos e o desempenho dos fornecedores.</p>
                </div>
            </div>
            <a href="index.php" class="inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-700">
                <i class="fas fa-arrow-left"></i>
                Voltar ao painel
            </a>
        </header>

        <section class="mb-4">
            <h2 class="text-lg font-extrabold text-slate-900">Resumo das compras</h2>
            <p class="mt-1 text-sm text-slate-500">Indicadores financeiros e volume de pedidos registrados.</p>
        </section>

        <section class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <article class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="absolute inset-y-0 left-0 w-1.5 bg-emerald-500"></div>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total recebido</p>
                        <h3 class="mt-3 text-2xl font-extrabold tracking-tight text-emerald-600 sm:text-3xl">
                            R$ <?= number_format($resumo['total_pago'] ?? 0, 2, ',', '.') ?>
                        </h3>
                        <p class="mt-2 text-xs text-slate-400">Compras com status recebido</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <i class="fas fa-circle-check text-lg"></i>
                    </div>
                </div>
            </article>

            <article class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="absolute inset-y-0 left-0 w-1.5 bg-amber-500"></div>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total pendente</p>
                        <h3 class="mt-3 text-2xl font-extrabold tracking-tight text-amber-600 sm:text-3xl">
                            R$ <?= number_format($resumo['total_pendente'] ?? 0, 2, ',', '.') ?>
                        </h3>
                        <p class="mt-2 text-xs text-slate-400">Compras aguardando recebimento</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <i class="fas fa-clock text-lg"></i>
                    </div>
                </div>
            </article>

            <article class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="absolute inset-y-0 left-0 w-1.5 bg-blue-500"></div>
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total de pedidos</p>
                        <h3 class="mt-3 text-2xl font-extrabold tracking-tight text-blue-600 sm:text-3xl">
                            <?= $resumo['total_pedidos'] ?? 0 ?>
                        </h3>
                        <p class="mt-2 text-xs text-slate-400">Quantidade de compras registradas</p>
                    </div>
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <i class="fas fa-shopping-cart text-lg"></i>
                    </div>
                </div>
            </article>
        </section>

        <section class="mb-4">
            <h2 class="text-lg font-extrabold text-slate-900">Análise de desempenho</h2>
            <p class="mt-1 text-sm text-slate-500">Visualização dos gastos mensais e dos principais fornecedores.</p>
        </section>

        <section class="grid grid-cols-1 gap-5 lg:grid-cols-2">
            <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-6 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900">Investimento mensal</h3>
                        <p class="text-xs text-slate-500">Evolução dos valores recebidos</p>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="chartMensal"></canvas>
                </div>
            </article>

            <article class="min-w-0 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-6 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900">Principais fornecedores</h3>
                        <p class="text-xs text-slate-500">Distribuição dos valores recebidos</p>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="chartFornecedor"></canvas>
                </div>
            </article>
        </section>

        <footer class="mt-8 border-t border-slate-200 py-5 text-center text-xs text-slate-400">
            AWT Sales Manager · Painel de indicadores de compras
        </footer>
    </main>
<script>
        // ConfiguraÃ§Ã£o do GrÃ¡fico Mensal
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

        // ConfiguraÃ§Ã£o do GrÃ¡fico de Fornecedores
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