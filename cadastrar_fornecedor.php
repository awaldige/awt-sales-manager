<!DOCTYPE html>
<html lang="pt-br">
<head>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Fornecedor | AWT Sales Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body class="min-h-screen bg-slate-50 font-['Inter'] text-slate-800 antialiased">

    <main class="mx-auto flex min-h-screen w-full max-w-5xl flex-col justify-center px-4 py-8 sm:px-6">

        <div class="mb-6">
            <a href="fornecedores.php"
               class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-slate-500 transition hover:bg-white hover:text-blue-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Voltar para fornecedores
            </a>
        </div>

        <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-100 bg-slate-900 px-6 py-7 sm:px-8">
                <div class="mb-3 flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-blue-300">
                    <span class="h-2 w-2 rounded-full bg-blue-400"></span>
                    AWT Sales Manager
                </div>

                <h1 class="text-2xl font-extrabold tracking-tight text-white sm:text-3xl">
                    Cadastrar Fornecedor
                </h1>

                <p class="mt-2 text-sm text-slate-300">
                    Preencha os dados para adicionar um novo parceiro ao sistema.
                </p>
            </div>

            <div class="p-6 sm:p-8">
                <form action="salvar_fornecedor.php" method="POST" class="space-y-6">

                    <div>
                        <label for="razao" class="mb-2 block text-sm font-semibold text-slate-700">
                            Razão Social <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="razao"
                            id="razao"
                            required
                            autocomplete="organization"
                            placeholder="Nome da empresa ou fornecedor"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10"
                        >
                    </div>

                    <div>
                        <label for="cnpj" class="mb-2 block text-sm font-semibold text-slate-700">
                            CNPJ <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="cnpj"
                            id="cnpj"
                            required
                            placeholder="00.000.000/0000-00"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10"
                        >
                        <p class="mt-2 text-xs text-slate-400">Digite os números do CNPJ. A formatação será aplicada automaticamente.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        <div>
                            <label for="telefone" class="mb-2 block text-sm font-semibold text-slate-700">
                                Telefone
                            </label>
                            <input
                                type="text"
                                name="telefone"
                                id="telefone"
                                placeholder="(00) 00000-0000"
                                autocomplete="tel"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10"
                            >
                        </div>

                        <div>
                            <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">
                                E-mail <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="email"
                                name="email"
                                id="email"
                                required
                                autocomplete="email"
                                placeholder="contato@empresa.com"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10"
                            >
                        </div>
                    </div>

                    <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-6 sm:flex-row sm:justify-end">
                        <a href="fornecedores.php"
                           class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-6 py-3 text-sm font-bold text-slate-600 transition hover:bg-slate-50">
                            Cancelar
                        </a>

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-7 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Salvar Fornecedor
                        </button>
                    </div>

                </form>
            </div>
        </section>

        <footer class="mt-6 text-center text-xs text-slate-400">
            AWT Sales Manager · AW TECHNOLOGY
        </footer>

    </main>

    <script>
        // Máscara simples para CNPJ
        document.getElementById('cnpj').addEventListener('input', function (e) {
            let x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,3})(\d{0,3})(\d{0,4})(\d{0,2})/);
            e.target.value = !x[2] ? x[1] : x[1] + '.' + x[2] + '.' + x[3] + '/' + x[4] + (x[5] ? '-' + x[5] : '');
        });
    </script>

</body>
</html>