<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Fornecedores | AWT Sales Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body class="min-h-screen bg-slate-50 font-['Inter'] text-slate-800 antialiased">

    <main class="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">

        <?php if(isset($_GET['status'])): ?>
            <?php if($_GET['status'] == 'success'): ?>
                <div class="mb-6 flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-emerald-800">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="text-sm font-semibold">Fornecedor cadastrado com sucesso!</span>
                </div>
            <?php elseif($_GET['status'] == 'deleted'): ?>
                <div class="mb-6 flex items-center gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-4 text-red-700">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    <span class="text-sm font-semibold">Fornecedor removido.</span>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <header class="mb-8 flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
            <div>
                <div class="mb-2 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-blue-600">
                    <span class="h-2 w-2 rounded-full bg-blue-600"></span>
                    AWT Sales Manager
                </div>
                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">
                    Gestão de Fornecedores
                </h1>
                <p class="mt-2 text-sm text-slate-500 sm:text-base">
                    Gerencie seus parceiros e distribuidores.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <a href="index.php"
                   class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-600 shadow-sm transition hover:bg-slate-100">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1"></path>
                    </svg>
                    Dashboard
                </a>

                <a href="cadastrar_fornecedor.php"
                   class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Novo Fornecedor
                </a>
            </div>
        </header>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-1 border-b border-slate-100 px-5 py-5 sm:px-6">
                <h2 class="text-lg font-bold text-slate-800">Fornecedores cadastrados</h2>
                <p class="text-sm text-slate-500">Consulte e gerencie os dados dos seus fornecedores.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] border-collapse text-left">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50">
                            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">ID</th>
                            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Razão Social</th>
                            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">CNPJ</th>
                            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Contato</th>
                            <th class="px-5 py-4 text-center text-xs font-bold uppercase tracking-wider text-slate-500">Ações</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        <?php
                        $sql = "SELECT * FROM Fornecedor ORDER BY idFornecedor DESC";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0):
                            while($row = $result->fetch_assoc()):
                                $idFornecedor = (int) $row["idFornecedor"];
                                $razaoSocial = htmlspecialchars($row["RazaoSocial"] ?? '', ENT_QUOTES, 'UTF-8');
                                $cnpj = htmlspecialchars($row["CNPJ"] ?? '', ENT_QUOTES, 'UTF-8');
                                $email = htmlspecialchars($row["Email"] ?? '', ENT_QUOTES, 'UTF-8');
                                $telefone = htmlspecialchars($row["Telefone"] ?? '', ENT_QUOTES, 'UTF-8');
                        ?>
                            <tr class="transition-colors hover:bg-slate-50">
                                <td class="whitespace-nowrap px-5 py-5 font-mono text-sm text-slate-400">
                                    #<?= $idFornecedor ?>
                                </td>

                                <td class="px-5 py-5">
                                    <span class="font-semibold text-slate-800"><?= $razaoSocial ?></span>
                                </td>

                                <td class="whitespace-nowrap px-5 py-5 font-mono text-sm text-slate-600">
                                    <?= $cnpj ?>
                                </td>

                                <td class="px-5 py-5">
                                    <div class="text-sm font-medium text-slate-700"><?= $email ?></div>
                                    <div class="mt-1 text-xs text-slate-500"><?= $telefone ?></div>
                                </td>

                                <td class="px-5 py-5">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="editar_fornecedor.php?id=<?= $idFornecedor ?>"
                                           title="Editar fornecedor"
                                           aria-label="Editar fornecedor"
                                           class="rounded-lg p-2 text-slate-400 transition hover:bg-blue-50 hover:text-blue-600">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                            </svg>
                                        </a>

                                        <a href="excluir_fornecedor.php?id=<?= $idFornecedor ?>"
                                           onclick="return confirm('Excluir fornecedor?')"
                                           title="Excluir fornecedor"
                                           aria-label="Excluir fornecedor"
                                           class="rounded-lg p-2 text-slate-400 transition hover:bg-red-50 hover:text-red-600">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                            <tr>
                                <td colspan="5" class="px-5 py-16 text-center">
                                    <div class="mx-auto flex max-w-sm flex-col items-center">
                                        <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M17 20h5v-2a3 3 0 00-5.196-2.04M17 20H7m10 0v-2c0-.72-.154-1.404-.43-2.02M7 20H2v-2a3 3 0 015.196-2.04M7 20v-2c0-.72.154-1.404.43-2.02m0 0a5.002 5.002 0 019.14 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                            </svg>
                                        </div>
                                        <p class="font-bold text-slate-700">Nenhum fornecedor cadastrado</p>
                                        <p class="mt-1 text-sm text-slate-500">Cadastre seu primeiro fornecedor para começar.</p>
                                        <a href="cadastrar_fornecedor.php" class="mt-5 text-sm font-bold text-blue-600 hover:text-blue-700">
                                            Cadastrar fornecedor
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <footer class="mt-6 text-center text-xs text-slate-400">
            AWT Sales Manager · AW TECHNOLOGY
        </footer>

    </main>
</body>
</html>