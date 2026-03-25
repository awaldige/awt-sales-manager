<?php
include 'auth.php';
include 'config.php';

// 1. Processar Cadastro de Nova Forma
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['novo_tipo'])) {
    $tipo = trim(mysqli_real_escape_string($conn, $_POST['novo_tipo']));
    
    if (!empty($tipo)) {
        // Busca se já existe para evitar duplicados
        $check = $conn->query("SELECT idFormaPagamento FROM formapagamento WHERE Tipo = '$tipo'");
        
        if ($check->num_rows == 0) {
            $stmt = $conn->prepare("INSERT INTO formapagamento (Tipo) VALUES (?)");
            $stmt->bind_param("s", $tipo);
            if($stmt->execute()){
                header("Location: formas_pagamento.php?sucesso=1");
            } else {
                die("Erro ao inserir: " . $conn->error);
            }
        } else {
            header("Location: formas_pagamento.php?erro=duplicado");
        }
    }
    exit();
}

// 2. Busca as formas cadastradas
$res = $conn->query("SELECT * FROM formapagamento ORDER BY idFormaPagamento DESC");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamentos | ERP PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
        .card-shadow { box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.04); }
    </style>
</head>
<body class="flex min-h-screen">

    <aside class="w-72 bg-slate-900 text-white hidden lg:flex flex-col sticky top-0 h-screen shadow-2xl">
        <div class="p-8 text-2xl font-black italic tracking-tighter">
            <i class="fas fa-bolt text-blue-500 mr-2"></i>ERP <span class="text-blue-500">PRO</span>
        </div>
        <nav class="mt-4 px-4 space-y-2 flex-1 font-semibold text-slate-400">
            <a href="index.php" class="flex items-center gap-4 p-4 hover:bg-slate-800 rounded-2xl transition">
                <i class="fas fa-chart-pie w-5"></i> Dashboard
            </a>
            <a href="formas_pagamento.php" class="flex items-center gap-4 p-4 bg-blue-600/10 text-blue-400 border-r-4 border-blue-600 transition">
                <i class="fas fa-credit-card w-5"></i> Pagamentos
            </a>
            <a href="compra.php" class="flex items-center gap-4 p-4 hover:bg-slate-800 rounded-2xl transition">
                <i class="fas fa-shopping-cart w-5"></i> Compras
            </a>
        </nav>
    </aside>

    <main class="flex-1 p-8 lg:p-12">
        <header class="mb-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-4xl font-extrabold text-slate-800 tracking-tight">Financeiro</h1>
                <p class="text-slate-500 font-medium italic mt-1 text-sm tracking-wide">Gerencie os métodos de recebimento do PDV.</p>
            </div>
            <?php if(isset($_GET['sucesso'])): ?>
                <div class="bg-emerald-500 text-white px-6 py-3 rounded-2xl font-bold text-sm shadow-lg shadow-emerald-200 animate-bounce">
                    <i class="fas fa-check mr-2"></i> Atualizado!
                </div>
            <?php endif; ?>
        </header>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-10">
            
            <div class="xl:col-span-4">
                <div class="bg-white rounded-[2.5rem] p-8 border border-slate-200 card-shadow sticky top-8">
                    <h2 class="text-xl font-bold text-slate-800 mb-8 flex items-center gap-3">
                        <div class="w-2 h-6 bg-blue-600 rounded-full"></div>
                        Novo Método
                    </h2>
                    <form action="" method="POST" class="space-y-6">
                        <div>
                            <label class="block text-[10px] font-black uppercase text-slate-400 mb-3 tracking-[0.2em] ml-1">Nome do Pagamento</label>
                            <input type="text" name="novo_tipo" required placeholder="Ex: Dinheiro, Pix, Vale..." 
                                class="w-full p-5 bg-slate-50 border border-slate-100 rounded-3xl font-bold text-slate-700 outline-none focus:ring-4 focus:ring-blue-100 focus:bg-white focus:border-blue-300 transition-all">
                        </div>
                        <button type="submit" class="w-full bg-slate-900 text-white font-black py-5 rounded-3xl hover:bg-blue-600 transition-all shadow-xl shadow-slate-200 uppercase text-xs tracking-[0.2em]">
                            Cadastrar Método
                        </button>
                    </form>
                </div>
            </div>

            <div class="xl:col-span-8">
                <div class="bg-white rounded-[2.5rem] border border-slate-200 card-shadow overflow-hidden">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/50 border-b border-slate-100 text-[10px] font-black uppercase text-slate-400">
                                <th class="px-8 py-6">ID</th>
                                <th class="px-8 py-6">Denominação</th>
                                <th class="px-8 py-6 text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <?php while($f = $res->fetch_assoc()): 
                                $nome = $f['Tipo'];
                                
                                // Cores em Hexadecimal para evitar transparência do Tailwind dinâmico
                                $cor = "#64748b"; $bg = "#f1f5f9"; $icon = "fa-wallet";

                                if (stripos($nome, 'Pix') !== false) { $cor = "#0891b2"; $bg = "#ecfeff"; $icon = "fa-bolt-lightning"; }
                                elseif (stripos($nome, 'Dinheiro') !== false) { $cor = "#16a34a"; $bg = "#f0fdf4"; $icon = "fa-money-bill-1-wave"; }
                                elseif (stripos($nome, 'Cartao') !== false) { $cor = "#4f46e5"; $bg = "#eef2ff"; $icon = "fa-credit-card"; }
                                elseif (stripos($nome, 'Boleto') !== false) { $cor = "#d97706"; $bg = "#fffbeb"; $icon = "fa-barcode"; }
                            ?>
                            <tr class="hover:bg-slate-50/80 transition group">
                                <td class="px-8 py-6">
                                    <span class="text-xs font-black text-slate-300 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-100 group-hover:text-blue-500 transition-colors">
                                        #<?= $f['idFormaPagamento'] ?>
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-5">
                                        <div class="w-14 h-14 rounded-[1.2rem] flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform duration-300" 
                                             style="background-color: <?= $bg ?>; color: <?= $cor ?>;">
                                            <i class="fas <?= $icon ?> text-xl"></i>
                                        </div>
                                        <div>
                                            <p class="font-extrabold text-slate-700 uppercase italic text-sm tracking-tight leading-none mb-1"><?= htmlspecialchars($nome) ?></p>
                                            <div class="flex items-center gap-1.5">
                                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                                                <span class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">Ativo no PDV</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <a href="deletar_forma.php?id=<?= $f['idFormaPagamento'] ?>" 
                                       onclick="return confirm('Excluir método: <?= $nome ?>?')"
                                       class="w-12 h-12 inline-flex items-center justify-center rounded-2xl bg-slate-50 text-slate-300 hover:bg-red-500 hover:text-white transition-all duration-300 shadow-sm">
                                        <i class="fas fa-trash-can text-sm"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>