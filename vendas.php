<?php 
include 'auth.php'; 
include 'config.php'; 

// 1. Busca produtos que TEM estoque para vender
$produtos = $conn->query("SELECT idProduto, Descricao, Valor, estoque FROM produtos WHERE estoque > 0");

// 2. Busca as formas de pagamento cadastradas no banco
$formas_pgto = $conn->query("SELECT idFormaPagamento, Tipo FROM formapagamento ORDER BY Tipo ASC");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Nova Venda | ERP PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 flex">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-slate-900 min-h-screen p-6 text-white hidden lg:flex flex-col sticky top-0 h-screen">
        <div class="text-2xl font-black mb-10 flex items-center gap-2 italic">
            <i class="fas fa-rocket text-blue-500"></i> ERP <span class="text-blue-500">PRO</span>
        </div>
        <nav class="space-y-2 flex-1 overflow-y-auto">
            <a href="index.php" class="flex items-center gap-3 p-3 hover:bg-slate-800 rounded-xl transition text-slate-400 font-medium">
                <i class="fas fa-chart-pie w-5"></i> Dashboard
            </a>
            <a href="vendas.php" class="flex items-center gap-3 p-3 bg-blue-600 rounded-xl font-bold shadow-lg shadow-blue-900/20">
                <i class="fas fa-shopping-cart w-5"></i> Nova Venda
            </a>
            <a href="produtos.php" class="flex items-center gap-3 p-3 hover:bg-slate-800 rounded-xl transition text-slate-400 font-medium">
                <i class="fas fa-box w-5 text-blue-400"></i> Produtos
            </a>
        </nav>
        <div class="mt-auto pt-6 border-t border-slate-800">
            <a href="logout.php" class="flex items-center gap-3 p-3 text-red-400 hover:bg-red-500/10 rounded-xl transition font-bold text-xs uppercase">
                <i class="fas fa-sign-out-alt w-4"></i> Sair
            </a>
        </div>
    </aside>
    
    <main class="flex-1 p-8">
        <div class="max-w-4xl mx-auto">
            
            <header class="flex justify-between items-center mb-10">
                <div>
                    <h1 class="text-3xl font-black text-slate-800 uppercase italic">Registrar Venda</h1>
                    <p class="text-slate-400 font-medium italic">Operador: <span class="text-blue-600 font-bold"><?= $_SESSION['usuario_nome'] ?></span></p>
                </div>
                
                <a href="index.php" class="flex items-center gap-2 p-3 px-6 bg-white border border-slate-200 rounded-2xl font-bold text-slate-500 hover:bg-slate-100 transition text-xs uppercase shadow-sm">
                    <i class="fas fa-arrow-left"></i> Voltar ao Painel
                </a>
            </header>

            <div class="bg-white rounded-[2.5rem] p-10 shadow-xl border border-slate-100">
                <form action="processar_venda.php" method="POST" class="space-y-6">
                    
                    <!-- Seleção de Produto -->
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-2 tracking-widest">Produto em Estoque</label>
                        <select name="idProduto" required class="w-full p-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-500 outline-none font-bold text-slate-700 appearance-none cursor-pointer">
                            <option value="">Selecione o produto...</option>
                            <?php while($prod = $produtos->fetch_assoc()): ?>
                                <option value="<?= $prod['idProduto'] ?>">
                                    <?= $prod['Descricao'] ?> — R$ <?= number_format($prod['Valor'], 2, ',', '.') ?> (Estoque: <?= $prod['estoque'] ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <!-- Quantidade -->
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-2 tracking-widest">Quantidade</label>
                            <input type="number" name="quantidade" min="1" value="1" required class="w-full p-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-500 outline-none font-black text-xl text-blue-600">
                        </div>

                        <!-- Forma de Pagamento (Nova seção) -->
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-2 tracking-widest">Forma de Pagamento</label>
                            <select name="idFormaPagamento" required class="w-full p-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-blue-500 outline-none font-bold text-slate-700 appearance-none cursor-pointer">
                                <option value="">Escolha...</option>
                                <?php while($f = $formas_pgto->fetch_assoc()): ?>
                                    <option value="<?= $f['idFormaPagamento'] ?>"><?= $f['Tipo'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Rodapé do formulário -->
                    <div class="pt-4">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-5 rounded-3xl shadow-lg shadow-blue-200 transition-all uppercase italic tracking-widest flex items-center justify-center gap-3 group">
                            <i class="fas fa-check-circle group-hover:scale-110 transition"></i> 
                            Confirmar Pagamento e Venda
                        </button>
                    </div>
                </form>
            </div>

            <p class="mt-8 text-center text-slate-400 text-xs font-medium uppercase tracking-tighter">
                <i class="fas fa-shield-alt mr-1"></i> Transação Segura • Registro Financeiro Automático
            </p>
        </div>
    </main>
</body>
</html>