<?php
include 'auth.php';
include 'config.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header("Location: categorias.php");
    exit;
}

$stmt = $conn->prepare("SELECT idCategoria, nomeCategoria FROM categoria WHERE idCategoria = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();
$cat = $resultado->fetch_assoc();
$stmt->close();

if (!$cat) {
    header("Location: categorias.php");
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $novoNome = trim($_POST['nomeCategoria'] ?? '');

    if ($novoNome === '') {
        $erro = "Informe o nome da categoria.";
    } else {
        $stmt = $conn->prepare("UPDATE categoria SET nomeCategoria = ? WHERE idCategoria = ?");
        $stmt->bind_param("si", $novoNome, $id);

        if ($stmt->execute()) {
            $stmt->close();
            header("Location: categorias.php");
            exit;
        } else {
            $erro = "Não foi possível atualizar a categoria. Tente novamente.";
            $stmt->close();
        }

        $cat['nomeCategoria'] = $novoNome;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Categoria | AWT Sales Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="min-h-screen bg-slate-100 font-['Inter'] text-slate-800">

    <main class="flex min-h-screen items-center justify-center px-4 py-10 sm:px-6">
        <section class="w-full max-w-lg overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl shadow-slate-200/60">

            <header class="bg-slate-900 px-6 py-7 text-white sm:px-8">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
                <a href="categorias.php" class="mb-6 inline-flex items-center gap-2 text-sm font-medium text-slate-300 transition hover:text-white">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Voltar para categorias
                </a>

                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-500/15 text-blue-300">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.862 4.487l1.687-1.688a2.121 2.121 0 113 3l-9.5 9.5a4 4 0 01-1.686 1.03l-3.5 1a.5.5 0 01-.62-.62l1-3.5a4 4 0 011.03-1.686l8.589-8.536z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h4"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-widest text-blue-300">AWT Sales Manager</p>
                        <h1 class="mt-1 text-2xl font-extrabold">Editar categoria</h1>
                        <p class="mt-1 text-sm text-slate-400">Registro #<?php echo (int)$id; ?></p>
                    </div>
                </div>
            </header>

            <div class="p-6 sm:p-8">
                <div class="mb-6">
                    <h2 class="text-lg font-bold text-slate-900">Informações da categoria</h2>
                    <p class="mt-1 text-sm text-slate-500">Altere o nome e salve para atualizar o cadastro.</p>
                </div>

                <?php if ($erro !== ''): ?>
                    <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                        <?php echo htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="editar_categoria.php?id=<?php echo (int)$id; ?>" class="space-y-6">
                    <div>
                        <label for="nomeCategoria" class="mb-2 block text-sm font-semibold text-slate-700">
                            Nome da categoria
                        </label>
                        <input
                            type="text"
                            name="nomeCategoria"
                            id="nomeCategoria"
                            value="<?php echo htmlspecialchars($cat['nomeCategoria'], ENT_QUOTES, 'UTF-8'); ?>"
                            maxlength="255"
                            required
                            autofocus
                            placeholder="Ex: Bebidas"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm font-medium text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10"
                        >
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                        <a
                            href="categorias.php"
                            class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                        >
                            Cancelar
                        </a>
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Salvar alterações
                        </button>
                    </div>
                </form>
            </div>

            <footer class="border-t border-slate-100 bg-slate-50 px-6 py-4 text-center sm:px-8">
                <p class="text-xs font-medium text-slate-400">AW TECHNOLOGY · AWT Sales Manager</p>
            </footer>
        </section>
    </main>

</body>
</html>