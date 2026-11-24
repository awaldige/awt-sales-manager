<?php 
require_once 'auth.php';
require_once 'config.php';

// --- DADOS DO USUÃƒÆ’Ã‚ÂRIO ---
$usuario_nome   = $_SESSION['usuario_nome'] ?? 'Visitante';
$usuario_nivel  = $_SESSION['usuario_nivel'] ?? 'colaborador'; 

// --- QUERIES DOS ANALYTICS ---
$count_prod     = $conn->query("SELECT COUNT(*) as total FROM produtos")->fetch_assoc()['total'] ?? 0;
$count_cli      = $conn->query("SELECT COUNT(*) as total FROM cliente")->fetch_assoc()['total'] ?? 0;
$count_forn     = $conn->query("SELECT COUNT(*) as total FROM fornecedor")->fetch_assoc()['total'] ?? 0;
$count_cat      = $conn->query("SELECT COUNT(*) as total FROM categoria")->fetch_assoc()['total'] ?? 0;
$count_users    = $conn->query("SELECT COUNT(*) as total FROM usuarios")->fetch_assoc()['total'] ?? 0;
$count_estoque  = $conn->query("SELECT SUM(estoque) as total FROM produtos")->fetch_assoc()['total'] ?? 0;
$count_compras  = $conn->query("SELECT COUNT(*) as total FROM compra")->fetch_assoc()['total'] ?? 0;

// Ajuste para contar vendas ÃƒÆ’Ã‚Âºnicas baseadas no StatusPedido (onde gravamos o cÃƒÆ’Ã‚Â³digo VND-...)
$vendas_res = $conn->query("
    SELECT
        COUNT(DISTINCT codigoVenda) AS total,
        SUM(CASE WHEN StatusPedido != 'Cancelado' THEN ValorTotal ELSE 0 END) AS faturamento
    FROM pedido
")->fetch_assoc();
$count_vendas = $vendas_res['total'] ?? 0;
$faturamento  = $vendas_res['faturamento'] ?? 0;

$investimento = $conn->query("SELECT SUM(ValorTotal) as total FROM compra WHERE StatusCompra = 'Recebido'")->fetch_assoc()['total'] ?? 0;

// QUERY CORRIGIDA: Busca a categoria real do produto para o histÃƒÆ’Ã‚Â³rico
$res_financeiro = $conn->query("
    SELECT 
        COALESCE(c.NomeCategoria, 'Sem Categoria') as Tipo, 
        SUM(p.ValorTotal) as total 
    FROM pedido p
    INNER JOIN produtos prod ON p.idProduto = prod.idProduto
    LEFT JOIN categoria c ON prod.idCategoria = c.idCategoria
    WHERE p.StatusPedido IN ('Confirmado', 'Em processamento')
    GROUP BY c.NomeCategoria
");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | AWT Sales Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');

    body {
        font-family: 'Inter', sans-serif;
    }

    .card-soft {
        border-left-width: 3px;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.035);
    }

    .card-soft:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 24px -10px rgba(15, 23, 42, 0.18);
    }

    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: #0f172a;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #1e293b;
        border-radius: 10px;
    }

    @media (max-width: 640px) {
        main {
            padding: 1rem;
        }
    }
</style>
</head>
<body class="bg-slate-50 flex min-h-screen">

    <aside class="w-64 bg-slate-900 text-white hidden lg:flex flex-col sticky top-0 h-screen p-6 shadow-2xl">
        <div class="text-2xl font-black mb-8 flex items-center gap-3 italic tracking-tighter">
            <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/40 not-italic">
                <span class="text-white text-sm font-black tracking-tight">AW</span>
            </div>
            <span class="not-italic"><span class="text-white">AWT</span><span class="block text-[10px] text-blue-400 font-bold uppercase tracking-[0.22em] mt-1">Sales Manager</span></span>
        </div>

        <nav class="space-y-1 flex-1 overflow-y-auto pr-2 custom-scrollbar">
            <a href="index.php" class="flex items-center gap-3 p-3 bg-blue-600 rounded-xl font-bold shadow-lg mb-6 text-sm">
                <i class="fas fa-chart-pie w-5 text-center"></i> Dashboard
            </a>
            
            <div class="pt-2 pb-2 text-[10px] font-black uppercase text-slate-500 tracking-[0.2em] ml-3">Comercial</div>
            <a href="pedidos.php" class="group flex items-center gap-3 p-2.5 hover:bg-slate-800 rounded-xl transition text-slate-400 hover:text-white font-semibold text-sm">
                <i class="fas fa-file-invoice-dollar w-5 text-amber-500"></i> Gestão de Vendas & PDV
            </a>

            <div class="pt-6 pb-2 text-[10px] font-black uppercase text-slate-500 tracking-[0.2em] ml-3">Estrat&#233;gico</div>
            <a href="dashboard_vendas.php" class="group flex items-center gap-3 p-2.5 hover:bg-slate-800 rounded-xl transition text-slate-400 hover:text-white font-semibold text-sm">
                <i class="fas fa-chart-line w-5 text-emerald-400"></i> Analytics Vendas
            </a>
            <a href="dashboard_compras.php" class="group flex items-center gap-3 p-2.5 hover:bg-slate-800 rounded-xl transition text-slate-400 hover:text-white font-semibold text-sm">
                <i class="fas fa-chart-bar w-5 text-rose-400"></i> Analytics Compras
            </a>

            <div class="pt-6 pb-2 text-[10px] font-black uppercase text-slate-500 tracking-[0.2em] ml-3">Suprimentos</div>
            <a href="compra.php" class="group flex items-center gap-3 p-2.5 hover:bg-slate-800 rounded-xl transition text-slate-400 hover:text-white font-semibold text-sm">
                <i class="fas fa-shopping-basket w-5 text-indigo-400"></i> Compras
            </a>
            <a href="fornecedores.php" class="flex items-center gap-3 p-2.5 hover:bg-slate-800 rounded-xl transition text-slate-400 hover:text-white font-semibold text-sm">
                <i class="fas fa-truck w-5 text-purple-500"></i> Fornecedores
            </a>
            <a href="estoque.php" class="flex items-center gap-3 p-2.5 hover:bg-slate-800 rounded-xl transition text-slate-400 hover:text-white font-semibold text-sm">
              <i class="fas fa-boxes w-5 text-orange-400"></i> Gest&#227;o de Estoque</a>

            <div class="pt-6 pb-2 text-[10px] font-black uppercase text-slate-500 tracking-[0.2em] ml-3">Cadastros</div>
            <a href="produtos.php" class="flex items-center gap-3 p-2.5 hover:bg-slate-800 rounded-xl transition text-slate-400 hover:text-white font-semibold text-sm">
                <i class="fas fa-box w-5 text-blue-400"></i> Produtos
            </a>
            <a href="clientes.php" class="flex items-center gap-3 p-2.5 hover:bg-slate-800 rounded-xl transition text-slate-400 hover:text-white font-semibold text-sm">
                <i class="fas fa-users w-5 text-emerald-500"></i> Clientes
            </a>
            <a href="categorias.php" class="flex items-center gap-3 p-2.5 hover:bg-slate-800 rounded-xl transition text-slate-400 hover:text-white font-semibold text-sm">
                <i class="fas fa-tags w-5 text-rose-500"></i> Categorias
            </a>
            <a href="formas_pagamento.php" class="flex items-center gap-3 p-2.5 hover:bg-slate-800 rounded-xl transition text-slate-400 hover:text-white font-semibold text-sm">
                <i class="fas fa-credit-card w-5 text-cyan-400"></i> Formas de Pagamento
            </a>

            <div class="pt-6 pb-2 text-[10px] font-black uppercase text-slate-500 tracking-[0.2em] ml-3">Sistema</div>
            <a href="usuarios.php" class="flex items-center gap-3 p-2.5 hover:bg-slate-800 rounded-xl transition text-slate-400 hover:text-white font-semibold text-sm">
                <i class="fas fa-user-shield w-5 text-slate-400"></i> Usu&#225;rios
            </a>
        </nav>

        <div class="mt-auto pt-6 border-t border-slate-800">
            <?php if($usuario_nivel == 'colaborador'): ?>
                <div class="mb-4 p-3 bg-gradient-to-r from-amber-500/10 to-orange-500/10 border border-amber-500/20 rounded-xl">
                    <p class="text-[9px] font-black uppercase tracking-widest text-amber-500 mb-1 leading-tight">Acesso Demonstra&#231;&#227;o</p>
                    <p class="text-[10px] text-slate-400 font-medium leading-tight text-pretty">Funcionalidades liberadas para aprecia&#231;&#227;o do portf&#243;lio.</p>
                </div>
            <?php endif; ?>

            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-gradient-to-tr from-blue-600 to-indigo-600 rounded-full flex items-center justify-center font-black text-white italic">
                    <?= strtoupper(substr($usuario_nome, 0, 1)) ?>
                </div>
                <div class="truncate text-xs">
                    <p class="font-bold text-white"><?= $usuario_nome ?></p>
                    <p class="text-blue-400 uppercase font-black tracking-widest mt-0.5"><?= $usuario_nivel ?></p>
                </div>
            </div>
            <a href="logout.php" class="flex items-center gap-3 p-3 text-slate-500 hover:text-red-400 transition-all font-bold text-xs uppercase tracking-widest"><i class="fas fa-sign-out-alt"></i> Sair</a>
        </div>
    </aside>

    <main class="flex-1 min-w-0 p-4 md:p-6 xl:p-8 overflow-y-auto">
        <details id="menu-mobile-dashboard" class="lg:hidden mb-6 bg-slate-900 text-white rounded-2xl shadow-lg">
            <summary class="flex items-center justify-between p-4 cursor-pointer list-none">
                <span class="flex items-center gap-3 font-bold text-sm">
                    <span class="w-9 h-9 bg-blue-600 rounded-xl flex items-center justify-center">
                        <span class="font-black">AW</span>
                    </span>
                    AWT Sales Manager
                </span>
                <i class="fas fa-bars text-blue-400"></i>
            </summary>

            <nav class="px-4 pb-4 space-y-1 border-t border-slate-800 pt-3 text-sm">
                <a href="index.php" class="block p-3 bg-blue-600 rounded-xl font-bold">
                    <i class="fas fa-chart-pie w-5 mr-2"></i> Dashboard
                </a>
                <a href="pedidos.php" class="block p-3 hover:bg-slate-800 rounded-xl">
                    <i class="fas fa-file-invoice-dollar w-5 mr-2 text-amber-500"></i> Gestão de Vendas & PDV
                </a>
                <a href="dashboard_vendas.php" class="block p-3 hover:bg-slate-800 rounded-xl">
                    <i class="fas fa-chart-line w-5 mr-2 text-emerald-400"></i> Analytics Vendas
                </a>
                <a href="dashboard_compras.php" class="block p-3 hover:bg-slate-800 rounded-xl">
                    <i class="fas fa-chart-bar w-5 mr-2 text-rose-400"></i> Analytics Compras
                </a>
                <a href="compra.php" class="block p-3 hover:bg-slate-800 rounded-xl">
                    <i class="fas fa-shopping-basket w-5 mr-2 text-indigo-400"></i> Compras
                </a>
                <a href="fornecedores.php" class="block p-3 hover:bg-slate-800 rounded-xl">
                    <i class="fas fa-truck w-5 mr-2 text-purple-500"></i> Fornecedores
                </a>
                <a href="estoque.php" class="block p-3 hover:bg-slate-800 rounded-xl">
                    <i class="fas fa-boxes w-5 mr-2 text-orange-400"></i> Gestão de Estoque
                </a>
                <a href="produtos.php" class="block p-3 hover:bg-slate-800 rounded-xl">
                    <i class="fas fa-box w-5 mr-2 text-blue-400"></i> Produtos
                </a>
                <a href="clientes.php" class="block p-3 hover:bg-slate-800 rounded-xl">
                    <i class="fas fa-users w-5 mr-2 text-emerald-500"></i> Clientes
                </a>
                <a href="categorias.php" class="block p-3 hover:bg-slate-800 rounded-xl">
                    <i class="fas fa-tags w-5 mr-2 text-rose-500"></i> Categorias
                </a>
                <a href="formas_pagamento.php" class="block p-3 hover:bg-slate-800 rounded-xl">
                    <i class="fas fa-credit-card w-5 mr-2 text-cyan-400"></i> Formas de Pagamento
                </a>
                <a href="usuarios.php" class="block p-3 hover:bg-slate-800 rounded-xl">
                    <i class="fas fa-user-shield w-5 mr-2 text-slate-400"></i> Usuários
                </a>

                <div class="border-t border-slate-800 pt-3 mt-3">
                    <p class="text-xs font-bold text-white"><?= htmlspecialchars($usuario_nome, ENT_QUOTES, 'UTF-8') ?></p>
                    <p class="text-[10px] text-blue-400 uppercase font-bold mt-1"><?= htmlspecialchars($usuario_nivel, ENT_QUOTES, 'UTF-8') ?></p>
                    <a href="logout.php" class="block p-3 mt-2 text-red-400 hover:bg-slate-800 rounded-xl font-bold">
                        <i class="fas fa-sign-out-alt w-5 mr-2"></i> Sair
                    </a>
                </div>
            </nav>
        </details>
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-5">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
            <div>
                <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 tracking-tight uppercase leading-tight">Dashboard</h1>
                <p class="text-slate-500 text-xs md:text-sm font-medium mt-2 tracking-wide">Bem-vindo ao centro de comando</p>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="flex flex-wrap gap-5 md:gap-6 bg-white p-4 md:px-6 rounded-2xl border border-slate-200 shadow-sm">
                    <div class="text-right">
                        <p class="text-[9px] md:text-[10px] font-bold text-slate-500 uppercase mb-1 tracking-wider">Compras Recebidas</p>
                        <p class="text-lg md:text-xl font-extrabold text-rose-600">R$ <?= number_format($investimento, 2, ',', '.') ?></p>
                    </div>
                    <div class="w-[1px] bg-slate-100 h-8 self-center mx-2"></div>
                    <div class="text-right">
                        <p class="text-[9px] md:text-[10px] font-bold text-slate-500 uppercase mb-1 tracking-wider">Valor dos Pedidos</p>
                        <p class="text-lg md:text-xl font-extrabold text-emerald-600">R$ <?= number_format($faturamento, 2, ',', '.') ?></p>
                    </div>
                </div>
            </div>
        </header>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4 mb-10">
            <a href="pedidos.php" class="bg-white p-4 md:p-5 rounded-2xl border border-slate-100 card-soft border-l-amber-400 text-center block">
                <h3 class="text-slate-500 font-bold text-[9px] md:text-[10px] uppercase tracking-wide mb-2">Vendas</h3>
                <p class="text-2xl font-extrabold text-slate-800"><?= $count_vendas ?></p>
            </a>
            <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-100 card-soft border-l-indigo-400 text-center">
                <h3 class="text-slate-500 font-bold text-[9px] md:text-[10px] uppercase tracking-wide mb-2">Compras</h3>
                <p class="text-2xl font-extrabold text-slate-800"><?= $count_compras ?></p>
            </div>
            <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-100 card-soft border-l-blue-400 text-center">
                <h3 class="text-slate-500 font-bold text-[9px] md:text-[10px] uppercase tracking-wide mb-2">Produtos</h3>
                <p class="text-2xl font-extrabold text-slate-800"><?= $count_prod ?></p>
            </div>
            <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-100 card-soft border-l-emerald-400 text-center">
                <h3 class="text-slate-500 font-bold text-[9px] md:text-[10px] uppercase tracking-wide mb-2">Clientes</h3>
                <p class="text-2xl font-extrabold text-slate-800"><?= $count_cli ?></p>
            </div>
            <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-100 card-soft border-l-purple-400 text-center">
                <h3 class="text-slate-500 font-bold text-[9px] md:text-[10px] uppercase tracking-wide mb-2">Forn.</h3>
                <p class="text-2xl font-extrabold text-slate-800"><?= $count_forn ?></p>
            </div>
            <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-100 card-soft border-l-cyan-400 text-center">
                <h3 class="text-slate-500 font-bold text-[9px] md:text-[10px] uppercase tracking-wide mb-2">Estoque</h3>
                <p class="text-2xl font-extrabold text-slate-800"><?= number_format($count_estoque, 0, '', '.') ?></p>
            </div>
            <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-100 card-soft border-l-rose-400 text-center">
                <h3 class="text-slate-500 font-bold text-[9px] md:text-[10px] uppercase tracking-wide mb-2">Cat.</h3>
                <p class="text-2xl font-extrabold text-slate-800"><?= $count_cat ?></p>
            </div>
            <div class="bg-white p-4 md:p-5 rounded-2xl border border-slate-100 card-soft border-l-slate-800 text-center">
                <h3 class="text-slate-500 font-bold text-[9px] md:text-[10px] uppercase tracking-wide mb-2">Usu&#225;rios</h3>
                <p class="text-2xl font-extrabold text-slate-800"><?= $count_users ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-3xl p-6 md:p-8 border border-slate-200 shadow-sm">
                <h2 class="text-lg font-extrabold text-slate-800 mb-7 flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-wallet"></i>
                    </div>
                    Pedidos por Categoria
                </h2>

                <div class="space-y-3">
                    <?php if($res_financeiro->num_rows > 0): ?>
                        <?php while($fin = $res_financeiro->fetch_assoc()): ?>
                            <div class="flex justify-between items-center gap-4 px-4 py-4 bg-slate-50 rounded-xl border border-slate-100">
                                <span class="font-bold text-slate-500 uppercase text-xs tracking-wide"><?= $fin['Tipo'] ?></span>
                                <span class="font-extrabold text-slate-800">R$ <?= number_format($fin['total'] ?? 0, 2, ',', '.') ?></span>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-slate-400 text-sm">Nenhuma venda registrada.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-slate-900 rounded-3xl p-7 md:p-9 shadow-xl text-white flex flex-col justify-center">
                <h2 class="text-sm font-bold uppercase tracking-widest text-blue-400 mb-6">
                    Comparativo de Valores
                </h2>

                <p class="text-4xl md:text-5xl font-extrabold tracking-tight">
                    R$ <?= number_format(($faturamento - $investimento), 2, ',', '.') ?>
                </p>

                <p class="text-slate-400 text-xs leading-relaxed mt-4">
                    Diferença entre o valor dos pedidos e as compras recebidas. Não representa lucro contábil.
                </p>

                <div class="mt-8 pt-6 border-t border-white/10">
                    <p class="text-slate-500 text-[10px] font-bold uppercase tracking-widest">
                        ERP Dashboard System v4.0
                    </p>
                </div>
            </div>
        </div>
    </main>
</body>
</html>

