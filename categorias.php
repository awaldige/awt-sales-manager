<?php
include 'auth.php';
include 'config.php';

// Excluir categoria
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM categoria WHERE idCategoria = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: categorias.php");
    exit;
}

// Cadastrar nova categoria
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nomeCategoria'] ?? '');

    if ($nome !== '') {
        $stmt = $conn->prepare("INSERT INTO categoria (nomeCategoria) VALUES (?)");
        $stmt->bind_param("s", $nome);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: categorias.php");
    exit;
}

$categorias = $conn->query("SELECT * FROM categoria ORDER BY idCategoria DESC");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorias | AWT Sales Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>

<body class="min-h-screen bg-slate-50 text-slate-800">

    <div class="flex min-h-screen">

        <aside class="hidden w-64 shrink-0 flex-col bg-slate-900 px-5 py-7 text-white lg:flex">
            <a href="index.php" class="mb-10 flex items-center gap-3 px-2">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-sm font-black">AW</div>
                <div>
                    <p class="text-sm font-extrabold tracking-wide">AWT Sales</p>
                    <p class="text-xs font-medium text-slate-400">Manager</p>
                </div>
            </a>

            <p class="mb-3 px-3 text-[10px] font-bold uppercase tracking-widest text-slate-500">Menu principal</p>

            <nav class="space-y-2">
                <a href="index.php" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-400 transition hover:bg-slate-800 hover:text-white">
                    <span>Dashboard</span>
                </a>
                <a href="categorias.php" class="flex items-center gap-3 rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-900/20">
                    <span>Categorias</span>
                </a>
                <a href="produtos.php" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-400 transition hover:bg-slate-800 hover:text-white">
                    <span>Produtos</span>
                </a>
            </nav>

            <div class="mt-auto border-t border-slate-800 pt-5">
                <p class="px-3 text-xs text-slate-500">AW TECHNOLOGY</p>
            </div>
        </aside>

        <main class="min-w-0 flex-1">

            <header class="border-b border-slate-200 bg-white px-5 py-5 sm:px-8">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
                <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="mb-1 text-xs font-bold uppercase tracking-widest text-blue-600">AWT Sales Manager</p>
                        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Categorias</h1>
                        <p class="mt-1 text-sm text-slate-500">Organize seus produtos por grupos.</p>
                    </div>

                    <a href="produtos.php" class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Ver produtos
                    </a>
                </div>
            </header>

            <div class="mx-auto max-w-7xl space-y-6 p-5 sm:p-8">

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

                    <section class="h-fit rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-7">
                        <div class="mb-6">
                            <div class="mb-3 flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </div>
                            <h2 class="text-lg font-bold text-slate-900">Nova categoria</h2>
                            <p class="mt-1 text-sm text-slate-500">Adicione um novo grupo de produtos.</p>
                        </div>

                        <form action="categorias.php" method="POST" class="space-y-5">
                            <div>
                                <label for="nomeCategoria" class="mb-2 block text-sm font-semibold text-slate-700">
                                    Nome da categoria
                                </label>
                                <input
                                    type="text"
                                    name="nomeCategoria"
                                    id="nomeCategoria"
                                    required
                                    maxlength="255"
                                    placeholder="Ex: Bebidas"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10"
                                >
                            </div>

                            <button
                                type="submit"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3.5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20"
                            >
                                Cadastrar categoria
                            </button>
                        </form>
                    </section>

                    <section class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-6 py-5">
                            <div>
                                <h2 class="text-lg font-bold text-slate-900">Categorias cadastradas</h2>
                                <p class="mt-1 text-sm text-slate-500">Gerencie os grupos de produtos.</p>
                            </div>
                            <span class="rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700">
                                Listagem
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[480px] text-left">
                                <thead>
                                    <tr class="border-b border-slate-100 bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                                        <th class="px-6 py-4">ID</th>
                                        <th class="px-6 py-4">Nome da categoria</th>
                                        <th class="px-6 py-4 text-right">Ações</th>
                                    </tr>
                                </thead>

                                <tbody class="divide-y divide-slate-100">
                                    <?php if ($categorias && $categorias->num_rows > 0): ?>
                                        <?php while ($cat = $categorias->fetch_assoc()): ?>
                                            <tr class="transition hover:bg-slate-50">
                                                <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-slate-400">
                                                    #<?php echo (int)$cat['idCategoria']; ?>
                                                </td>
                                                <td class="px-6 py-4 text-sm font-semibold text-slate-700">
                                                    <?php echo htmlspecialchars($cat['nomeCategoria'], ENT_QUOTES, 'UTF-8'); ?>
                                                </td>
                                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                                    <a
                                                        href="editar_categoria.php?id=<?php echo (int)$cat['idCategoria']; ?>"
                                                        title="Editar categoria"
                                                        aria-label="Editar categoria"
                                                        class="mr-1 inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 transition hover:bg-blue-50 hover:text-blue-600"
                                                    >
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 20h9M16.5 3.5a2.12 2.12 0 013 3L9 17l-4 1 1-4 10.5-10.5z"/>
                                                        </svg>
                                                    </a>
                                                    <a
                                                        href="?delete=<?php echo (int)$cat['idCategoria']; ?>"
                                                        onclick="return confirm('Deseja realmente excluir esta categoria?')"
                                                        title="Excluir categoria"
                                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-600"
                                                    >
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12m-10 0V5a1 1 0 011-1h6a1 1 0 011 1v2m-9 0l1 13h6l1-13M10 11v5m4-5v5"/>
                                                        </svg>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="px-6 py-12 text-center">
                                                <p class="text-sm font-semibold text-slate-600">Nenhuma categoria cadastrada.</p>
                                                <p class="mt-1 text-xs text-slate-400">Use o formulário ao lado para adicionar a primeira categoria.</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>

                </div>

                <p class="pb-2 text-center text-xs text-slate-400">
                    AW TECHNOLOGY · AWT Sales Manager
                </p>

            </div>
        </main>
    </div>

</body>
</html>