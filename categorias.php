<?php 
include 'auth.php'; 
include 'config.php'; 

// Lógica para Deletar
if(isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM categoria WHERE idCategoria = $id");
    header("Location: categorias.php");
}

// Lógica para Salvar Nova
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nomeCategoria'];
    $conn->query("INSERT INTO categoria (nomeCategoria) VALUES ('$nome')");
    header("Location: categorias.php");
}

$categorias = $conn->query("SELECT * FROM categoria ORDER BY idCategoria DESC");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Categorias | ERP PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-slate-50 flex">

    <!-- BARRA LATERAL (SIDEBAR) -->
    <aside class="w-64 bg-slate-900 min-h-screen p-6 text-white hidden lg:flex flex-col sticky top-0 h-screen">
        <div class="text-2xl font-black mb-10 italic"><i class="fas fa-rocket text-blue-500"></i> ERP <span class="text-blue-500">PRO</span></div>
        <nav class="space-y-2 flex-1">
            <a href="index.php" class="flex items-center gap-3 p-3 hover:bg-slate-800 rounded-xl transition text-slate-400 font-medium"><i class="fas fa-chart-pie w-5"></i> Dashboard</a>
            <a href="categorias.php" class="flex items-center gap-3 p-3 bg-rose-600 rounded-xl font-bold shadow-lg shadow-rose-900/20"><i class="fas fa-tags w-5"></i> Categorias</a>
            <a href="produtos.php" class="flex items-center gap-3 p-3 hover:bg-slate-800 rounded-xl transition text-slate-400 font-medium"><i class="fas fa-box w-5"></i> Produtos</a>
        </nav>
    </aside>

    <main class="flex-1 p-8">
        <header class="mb-10 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-black text-slate-800 uppercase italic">Categorias</h1>
                <p class="text-slate-400 text-sm">Organize seus produtos por grupos.</p>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- FORMULÁRIO DE CADASTRO -->
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 h-fit">
                <h2 class="text-lg font-black text-slate-800 mb-6 uppercase italic">Nova Categoria</h2>
                <form action="" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-2 ml-2">Nome da Categoria</label>
                        <input type="text" name="nomeCategoria" required placeholder="Ex: Bebidas" class="w-full p-4 bg-slate-50 border-2 border-slate-100 rounded-2xl focus:border-rose-500 outline-none font-bold text-slate-700">
                    </div>
                    <button type="submit" class="w-full bg-slate-900 hover:bg-black text-white font-black py-4 rounded-2xl transition-all uppercase italic tracking-widest text-sm">
                        Cadastrar
                    </button>
                </form>
            </div>

            <!-- LISTAGEM DE CATEGORIAS -->
            <div class="lg:col-span-2 bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-[10px] text-slate-400 uppercase font-black tracking-widest border-b border-slate-50">
                            <th class="py-4">ID</th>
                            <th class="py-4">Nome da Categoria</th>
                            <th class="py-4 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php while($cat = $categorias->fetch_assoc()): ?>
                        <tr class="group hover:bg-slate-50 transition">
                            <td class="py-4 font-black text-slate-400 text-xs italic">#<?= $cat['idCategoria'] ?></td>
                            <td class="py-4 font-bold text-slate-700 uppercase italic"><?= $cat['nomeCategoria'] ?></td>
                            <td class="py-4 text-right">
                                <a href="?delete=<?= $cat['idCategoria'] ?>" onclick="return confirm('Excluir esta categoria?')" class="text-rose-100 group-hover:text-rose-500 transition-colors p-2">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>
</body>
</html>