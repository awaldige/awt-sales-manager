<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Cliente | AWT Sales Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body class="min-h-screen bg-slate-50 font-['Inter'] text-slate-800 antialiased">

<div class="mx-auto max-w-3xl px-4 py-6 sm:px-6 sm:py-10">

    <a href="clientes.php" class="mb-6 inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-blue-600">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Voltar para clientes
    </a>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-100 bg-white px-6 py-7 sm:px-8">
            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 9v6m3-3h-6m-3-4a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0"></path>
                </svg>
            </div>

            <p class="mb-1 text-xs font-bold uppercase tracking-widest text-blue-600">AWT Sales Manager</p>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Cadastrar novo cliente</h1>
            <p class="mt-2 text-sm leading-relaxed text-slate-500">
                Preencha os dados abaixo para adicionar um cliente à sua base.
            </p>
        </div>

        <form action="salvar_cliente.php" method="POST" class="space-y-6 px-6 py-7 sm:px-8 sm:py-8">

            <div>
                <label for="nome" class="mb-2 block text-sm font-semibold text-slate-700">
                    Nome completo / Razão social
                </label>
                <input
                    type="text"
                    name="nome"
                    id="nome"
                    required
                    placeholder="Ex: João Silva ou Tech LTDA"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10"
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
                        onchange="ajustarMascara()"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10"
                    >
                        <option value="F">Pessoa Física (CPF)</option>
                        <option value="J">Pessoa Jurídica (CNPJ)</option>
                    </select>
                </div>

                <div>
                    <label for="identificacao" id="labelDoc" class="mb-2 block text-sm font-semibold text-slate-700">
                        CPF
                    </label>
                    <input
                        type="text"
                        name="identificacao"
                        id="identificacao"
                        required
                        placeholder="000.000.000-00"
                        maxlength="14"
                        inputmode="numeric"
                        autocomplete="off"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3.5 font-mono text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-500/10"
                    >
                    <p class="mt-2 text-xs text-slate-400">Digite somente os números ou utilize a máscara automática.</p>
                </div>

            </div>

            <div class="border-t border-slate-100 pt-6">
                <button
                    type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-4 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-500/20"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Finalizar cadastro
                </button>

                <p class="mt-4 text-center text-xs text-slate-400">
                    Confira os dados antes de finalizar o cadastro.
                </p>
            </div>

        </form>
    </div>

    <footer class="mt-6 text-center text-xs text-slate-400">
        AW TECHNOLOGY · AWT Sales Manager
    </footer>

</div>

<script>
    function ajustarMascara() {
        const tipo = document.getElementById('tipo').value;
        const input = document.getElementById('identificacao');
        const label = document.getElementById('labelDoc');

        if (tipo === 'F') {
            label.innerText = 'CPF';
            input.placeholder = '000.000.000-00';
            input.maxLength = 14;
        } else {
            label.innerText = 'CNPJ';
            input.placeholder = '00.000.000/0000-00';
            input.maxLength = 18;
        }

        input.value = '';
    }

    document.getElementById('identificacao').addEventListener('input', function (e) {
        let value = e.target.value.replace(/\D/g, '');
        const tipo = document.getElementById('tipo').value;

        if (tipo === 'F') {
            value = value.substring(0, 11);
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d)/, '$1.$2');
            value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
        } else {
            value = value.substring(0, 14);
            value = value.replace(/^(\d{2})(\d)/, '$1.$2');
            value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
            value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
            value = value.replace(/(\d{4})(\d)/, '$1-$2');
        }

        e.target.value = value;
    });
</script>

</body>
</html>