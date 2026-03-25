<?php 
include 'config.php'; 

// 1. Lógica para EXCLUIR
if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    $conn->query("DELETE FROM produtos WHERE idProduto = $id");
    header("Location: produtos.php");
    exit();
}

// 2. BUSCA ATUALIZADA (Incluindo o novo campo preco_custo_atual)
$sql = "SELECT p.*, c.nomeCategoria 
        FROM produtos p 
        LEFT JOIN categoria c ON p.idCategoria = c.idCategoria 
        ORDER BY p.idProduto DESC";

$lista = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Estoque Inteligente | ERP PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 p-4 md:p-8">
    <div class="max-w-7xl mx-auto">
        <header class="flex justify-between items-center mb-10">
            <div>
                <h1 class="text-3xl font-black text-slate-800 italic uppercase italic tracking-tighter">Estoque & Margem</h1>
                <p class="text-[10px] font-black text-blue-500 uppercase tracking-widest">Controle de Custo e Venda</p>
            </div>
            <div class="flex gap-3">
                <a href="index.php" class="bg-white border px-6 py-3 rounded-2xl font-bold text-slate-600 hover:bg-slate-100 transition text-xs uppercase">Início</a>
                <a href="cadastro_produto.php" class="bg-blue-600 px-6 py-3 rounded-2xl font-black text-white hover:bg-blue-700 transition shadow-lg text-xs uppercase tracking-widest">Novo Produto</a>
            </div>
        </header>

        <div class="bg-white rounded-[2.5rem] p-6 md:p-8 shadow-sm border border-slate-100 overflow-x-auto">
            <table class="w-full text-left border-separate border-spacing-y-3">
                <thead>
                    <tr class="text-[10px] text-slate-400 uppercase font-black tracking-widest">
                        <th class="px-6 pb-2">Produto</th>
                        <th class="px-6 pb-2 text-center">Estoque</th>
                        <th class="px-6 pb-2">Custo (Última)</th>
                        <th class="px-6 pb-2">Venda</th>
                        <th class="px-6 pb-2 text-center">Margem</th>
                        <th class="px-6 pb-2 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($p = $lista->fetch_assoc()): 
                        $custo = $p['preco_custo_atual'] ?? 0;
                        $venda = $p['precoVenda'] ?? 0;
                        
                        // Cálculo de Margem Bruta
                        $margem = ($venda > 0 && $custo > 0) ? (($venda - $custo) / $venda) * 100 : 0;
                        $corMargem = ($margem <= 10) ? 'text-red-500' : (($margem <= 30) ? 'text-amber-500' : 'text-emerald-500');
                    ?>
                    <tr class="group hover:bg-slate-50 transition-all">
                        <!-- DESCRIÇÃO -->
                        <td class="px-6 py-5 bg-slate-50 rounded-l-3xl">
                            <div class="font-black text-slate-800 text-lg italic uppercase leading-tight"><?= $p['Descricao'] ?></div>
                            <span class="text-[10px] text-slate-400 font-bold uppercase">CÓD: #<?= $p['idProduto'] ?></span>
                        </td>

                        <!-- ESTOQUE -->
                        <td class="px-6 py-5 border-y border-slate-50 text-center">
                            <span class="text-lg font-black <?= $p['estoque'] <= 5 ? 'text-red-500' : 'text-slate-700' ?>">
                                <?= $p['estoque'] ?>
                            </span>
                        </td>

                        <!-- PREÇO DE CUSTO -->
                        <td class="px-6 py-5 border-y border-slate-50 font-bold text-slate-500 text-sm italic">
                            R$ <?= number_format($custo, 2, ',', '.') ?>
                        </td>

                        <!-- PREÇO DE VENDA -->
                        <td class="px-6 py-5 border-y border-slate-50 font-black text-slate-900 text-lg italic">
                            R$ <?= number_format($venda, 2, ',', '.') ?>
                        </td>

                        <!-- MARGEM DE LUCRO -->
                        <td class="px-6 py-5 border-y border-slate-50 text-center">
                            <div class="flex flex-col items-center">
                                <span class="<?= $corMargem ?> font-black text-sm">
                                    <?= number_format($margem, 1, ',', '.') ?>%
                                </span>
                                <div class="w-12 h-1 bg-slate-200 rounded-full mt-1">
                                    <div class="h-full <?= str_replace('text', 'bg', $corMargem) ?> rounded-full" style="width: <?= min($margem, 100) ?>%"></div>
                                </div>
                            </div>
                        </td>

                        <!-- AÇÕES -->
                        <td class="px-6 py-5 bg-slate-50 rounded-r-3xl text-right">
                            <div class="flex justify-end gap-2">
                                <a href="editar_produto.php?id=<?= $p['idProduto'] ?>" class="w-10 h-10 flex items-center justify-center bg-white text-amber-500 rounded-xl shadow-sm hover:bg-amber-500 hover:text-white transition">
                                    <i class="fas fa-pen text-xs"></i>
                                </a>
                                <a href="produtos.php?excluir=<?= $p['idProduto'] ?>" onclick="return confirm('Excluir este produto?')" class="w-10 h-10 flex items-center justify-center bg-white text-red-500 rounded-xl shadow-sm hover:bg-red-500 hover:text-white transition">
                                    <i class="fas fa-trash text-xs"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>