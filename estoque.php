<?php 
include 'auth.php'; // Proteção de login
include 'config.php'; 

// 1. Busca os produtos - Priorizando o que está acabando
$sql = "SELECT idProduto, Descricao, Categoria, estoque, Valor 
        FROM produtos 
        ORDER BY estoque ASC"; 
$res = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Controle de Estoque | ERP PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 flex">

    <!-- SIDEBAR COMPLETA -->
    <aside class="w-64 bg-slate-900 min-h-screen p-6 text-white hidden lg:flex flex-col sticky top-0 h-screen">
        <div class="text-2xl font-black mb-10 flex items-center gap-2 italic">
            <i class="fas fa-rocket text-blue-500"></i> ERP <span class="text-blue-500">PRO</span>
        </div>
        
        <nav class="space-y-2 flex-1">
            <a href="index.php" class="flex items-center gap-3 p-3 hover:bg-slate-800 rounded-xl transition text-slate-400 hover:text-white font-medium">
                <i class="fas fa-chart-pie w-5"></i> Dashboard
            </a>
            <a href="estoque.php" class="flex items-center gap-3 p-3 bg-blue-600 rounded-xl font-bold shadow-lg shadow-blue-900/20">
                <i class="fas fa-boxes w-5"></i> Estoque Atual
            </a>
            <a href="produtos.php" class="flex items-center gap-3 p-3 hover:bg-slate-800 rounded-xl transition text-slate-400 hover:text-white font-medium">
                <i class="fas fa-box w-5"></i> Cadastrar Produtos
            </a>
            <a href="clientes.php" class="flex items-center gap-3 p-3 hover:bg-slate-800 rounded-xl transition text-slate-400 hover:text-white font-medium">
                <i class="fas fa-users w-5"></i> Clientes
            </a>

            <?php if($_SESSION['usuario_nivel'] == 'admin'): ?>
            <div class="pt-4 pb-2 text-[10px] font-black uppercase text-slate-500 tracking-widest ml-3">Administração</div>
            <a href="usuarios.php" class="flex items-center gap-3 p-3 hover:bg-slate-800 rounded-xl transition text-slate-400 hover:text-white font-medium">
                <i class="fas fa-user-shield w-5"></i> Gestão de Equipe
            </a>
            <?php endif; ?>
        </nav>

        <div class="mt-auto pt-6 border-t border-slate-800">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center font-black text-white shadow-lg italic">
                    <?= strtoupper(substr($_SESSION['usuario_nome'], 0, 1)) ?>
                </div>
                <div class="truncate">
                    <p class="text-sm font-bold leading-none truncate"><?= $_SESSION['usuario_nome'] ?></p>
                    <p class="text-[9px] text-blue-400 uppercase font-black tracking-widest mt-1 italic"><?= $_SESSION['usuario_nivel'] ?></p>
                </div>
            </div>
            <a href="logout.php" class="flex items-center gap-3 p-3 text-red-400 hover:bg-red-500/10 rounded-xl transition font-bold text-xs uppercase tracking-tighter">
                <i class="fas fa-sign-out-alt w-4"></i> Sair
            </a>
        </div>
    </aside>

    <main class="flex-1 p-8">
        <header class="flex justify-between items-center mb-10">
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight italic uppercase leading-none">Inventário</h1>
                <p class="text-slate-400 text-sm font-medium mt-1">Status em tempo real das mercadorias</p>
            </div>
            <button onclick="window.print()" class="p-3 px-6 bg-white border border-slate-200 rounded-2xl font-bold text-slate-500 hover:bg-slate-100 transition text-xs uppercase shadow-sm">
                <i class="fas fa-print mr-2"></i> Imprimir Relatório
            </button>
        </header>

        <!-- Grade de Produtos -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-200 overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-slate-50 text-slate-400 text-[10px] uppercase font-black tracking-widest border-b border-slate-100">
                    <tr>
                        <th class="p-6">Identificação do Produto</th>
                        <th class="p-6">Categoria</th>
                        <th class="p-6 text-center">Quantidade</th>
                        <th class="p-6 text-center">Status</th>
                        <th class="p-6 text-right">Ajuste Rápido</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php while($row = $res->fetch_assoc()): 
                        $qtd = $row['estoque'];
                        
                        // Lógica de Status (Definição de classes manuais para evitar erros no Tailwind)
                        if($qtd <= 0) {
                            $status = "Sem Estoque";
                            $css = "bg-red-50 text-red-600 border-red-100";
                        } elseif ($qtd <= 5) {
                            $status = "Estoque Baixo";
                            $css = "bg-amber-50 text-amber-600 border-amber-100";
                        } else {
                            $status = "Normal";
                            $css = "bg-emerald-50 text-emerald-600 border-emerald-100";
                        }
                    ?>
                    <tr class="hover:bg-slate-50/50 transition group">
                        <td class="p-6">
                            <span class="font-black text-slate-700 block uppercase italic"><?= $row['Descricao'] ?></span>
                            <span class="text-[10px] text-blue-500 font-black uppercase tracking-tighter">SKU: #<?= $row['idProduto'] ?></span>
                        </td>
                        <td class="p-6">
                            <span class="px-3 py-1 bg-slate-100 text-slate-500 text-[10px] font-black uppercase rounded-lg border border-slate-200">
                                <i class="fas fa-tag mr-1 text-[8px] text-slate-400"></i>
                                <?= !empty($row['Categoria']) ? $row['Categoria'] : 'Geral' ?>
                            </span>
                        </td>
                        <td class="p-6 text-center">
                            <span class="text-xl font-black text-slate-800"><?= $qtd ?></span>
                            <span class="text-[9px] text-slate-400 block uppercase font-bold">Unidades</span>
                        </td>
                        <td class="p-6 text-center">
                            <span class="px-4 py-1.5 rounded-full text-[9px] font-black uppercase border <?= $css ?>">
                                <?= $status ?>
                            </span>
                        </td>
                        <td class="p-6 text-right">
                            <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                <a href="ajuste_estoque.php?id=<?= $row['idProduto'] ?>&acao=add" class="w-9 h-9 flex items-center justify-center bg-white border border-slate-200 text-emerald-600 rounded-xl hover:bg-emerald-600 hover:text-white transition shadow-sm" title="Adicionar Entrada">
                                    <i class="fas fa-plus text-xs"></i>
                                </a>
                                <a href="ajuste_estoque.php?id=<?= $row['idProduto'] ?>&acao=sub" class="w-9 h-9 flex items-center justify-center bg-white border border-slate-200 text-rose-500 rounded-xl hover:bg-rose-500 hover:text-white transition shadow-sm" title="Registrar Saída">
                                    <i class="fas fa-minus text-xs"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>