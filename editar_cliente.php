<?php
include 'config.php';

$id = (int)($_GET['id'] ?? 0);
$res = $conn->query("SELECT * FROM Cliente WHERE idCliente = $id");
$cliente = $res->fetch_assoc();

$nomeCompleto = trim($cliente['Pnome'] . " " . $cliente['NomeMeio'] . " " . $cliente['Sobrenome']);
$nomeCompleto = str_replace("  ", " ", $nomeCompleto);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente | AWT Sales Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>

<body class="min-h-screen bg-slate-50 px-4 py-8 sm:px-6 sm:py-12">

    <main class="mx-auto max-w-2xl">

        <div class="mb-6">
            <a href="clientes.php" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-blue-600">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Voltar para clientes
            </a>
        </div>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

            <header class="border-b border-slate-100 px-6 py-7 sm:px-8">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16.862 4.487l1.651-1.651a2.121 2.121 0 113 3l-9.75 9.75a4 4 0 01-1.69 1.006L7 17l.408-3.073a4 4 0 011.006-1.69l8.448-8.448zM19 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h4"/>
                    </svg>
                </div>

                <p class="mb-1 text-xs font-bold uppercase tracking-widest text-blue-600">AWT Sales Manager</p>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Editar cliente</h1>
                <p class="mt-2 text-sm text-slate-500">
                    Atualize as informações cadastrais do cliente.
                </p>
                <p class="mt-3 inline-flex rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-600">
                    Cliente #<?php echo (int)$id; ?>
                </p>
            </header>

            <form action="atualizar_cliente.php" method="POST" class="space-y-6 p-6 sm:p-8">

                <input type="hidden" name="id" value="<?php echo (int)$id; ?>">

                <div>
                    <label for="nome" class="mb-2 block text-sm font-semibold text-slate-700">
                        Nome completo / Razão social
                    </label>
                    <input
                        type="text"
                        name="nome"
                        id="nome"
                        value="<?php echo htmlspecialchars($nomeCompleto, ENT_QUOTES, 'UTF-8'); ?>"
                        required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10"
                    >
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                    <div>
                        <label for="tipo" class="mb-2 block text-sm font-semibold text-slate-700">
                            Tipo de pessoa
                        </label>
                        <select
                            name="tipo"
                            id="tipo"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10"
                        >
                            <option value="PF" <?php echo ($cliente['Tipo'] == 'PF') ? 'selected' : ''; ?>>Pessoa Física</option>
                            <option value="PJ" <?php echo ($cliente['Tipo'] == 'PJ') ? 'selected' : ''; ?>>Pessoa Jurídica</option>
                        </select>
                    </div>

                    <div>
                        <label for="documento" class="mb-2 block text-sm font-semibold text-slate-700">
                            Documento
                        </label>
                        <input
                            type="text"
                            name="documento"
                            id="documento"
                            value="<?php echo htmlspecialchars($cliente['Documento'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                            required
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10"
                        >
                    </div>

                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">

                    <a
                        href="clientes.php"
                        class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-6 py-3.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                    >
                        Cancelar
                    </a>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-6 py-3.5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Salvar alterações
                    </button>

                </div>

            </form>
        </section>

        <p class="mt-5 text-center text-xs text-slate-400">
            AW TECHNOLOGY · AWT Sales Manager
        </p>

    </main>

</body>
</html>