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
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta charset="UTF-8">
    <title>Estoque Inteligente | ERP PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 p-4 sm:p-6 lg:p-8">
    <div class="max-w-7xl mx-auto">
        <header class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-5 mb-8">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-800 uppercase tracking-tight">Estoque & Margem</h1>
                <p class="text-xs font-bold text-blue-600 uppercase tracking-widest mt-1">Controle de Custo e Venda</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="index.php" class="bg-white border border-slate-200 px-5 py-3 rounded-xl font-bold text-slate-600 hover:bg-slate-100 transition text-xs uppercase">Início</a>
                <a href="cadastro_produto.php" class="bg-blue-600 px-5 py-3 rounded-xl font-bold text-white hover:bg-blue-700 transition shadow-md text-xs uppercase tracking-widest">Novo Produto</a>
            </div>
        </header>

        <div class="bg-white rounded-2xl p-4 sm:p-6 lg:p-8 shadow-sm border border-slate-200 overflow-x-auto">
            <table class="w-full min-w-[900px] text-left border-separate border-spacing-y-2">
                <thead>
                    <tr class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">
                        <th class="px-6 pb-2">Produto</th>
                        <th class="px-6 pb-2 text-center">Estoque</th>
                        <th class="px-6 pb-2">Custo (Última)</th>
                        <th class="px-6 pb-2">Custo Cadastro</th>
                        <th class="px-6 pb-2">Venda</th>
                        <th class="px-6 pb-2 text-center">Margem</th>
                        <th class="px-6 pb-2 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($p = $lista->fetch_assoc()): 
                        $custo = $p['preco_custo_atual'] ?? 0;
                        $custoCadastro = $p['Valor'] ?? 0;
                        $venda = $p['precoVenda'] ?? 0;
                        
                        // Cálculo de Margem Bruta
                        $margem = ($venda > 0 && $custoCadastro > 0) ? (($venda - $custoCadastro) / $venda) * 100 : 0;
                        $corMargem = ($margem <= 10) ? 'text-red-500' : (($margem <= 30) ? 'text-amber-500' : 'text-emerald-500');
                    ?>
                    <tr class="group hover:bg-slate-50 transition-colors">
                        <!-- DESCRIÇÃO -->
                        <td class="px-5 py-4 bg-slate-50 rounded-l-2xl">
                            <div class="font-black text-slate-800 text-lg italic uppercase leading-tight"><?= $p['Descricao'] ?></div>
                            <span class="text-[10px] text-slate-400 font-bold uppercase">CÓD: #<?= $p['idProduto'] ?></span>
                        </td>

                        <!-- ESTOQUE -->
                        <td class="px-5 py-4 border-y border-slate-100 text-center">
                            <span class="text-lg font-black <?= $p['estoque'] <= 5 ? 'text-red-500' : 'text-slate-700' ?>">
                                <?= $p['estoque'] ?>
                            </span>
                        </td>

                        <!-- PREÇO DE CUSTO -->
                        <td class="px-5 py-4 border-y border-slate-100 font-bold text-slate-500 text-sm italic">
                            R$ <?= number_format($custo, 2, ',', '.') ?>
                        </td>

                        <!-- PREÇO DE VENDA -->
                                                <td class="px-5 py-4 border-y border-slate-100 font-bold text-slate-500 text-sm italic">
                            R$ <?= number_format($custoCadastro, 2, ',', '.') ?>
                        </td>
<td class="px-5 py-4 border-y border-slate-100 font-black text-slate-900 text-lg italic">
                            R$ <?= number_format($venda, 2, ',', '.') ?>
                        </td>

                        <!-- MARGEM DE LUCRO -->
                        <td class="px-5 py-4 border-y border-slate-100 text-center">
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
                        <td class="px-5 py-4 bg-slate-50 rounded-r-2xl text-right">
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