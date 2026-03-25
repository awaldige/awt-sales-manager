<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Cliente | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans text-gray-900">

    <div class="max-w-2xl mx-auto p-6">
        <!-- Voltar -->
        <a href="clientes.php" class="text-blue-600 font-semibold flex items-center gap-2 mb-6 hover:underline">
            ← Voltar para a lista
        </a>

        <div class="bg-white rounded-3xl shadow-xl p-8 border border-gray-100">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Cadastrar Novo Cliente</h1>

            <form action="salvar_cliente.php" method="POST" class="space-y-5">
                <!-- Nome -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Nome Completo / Razão Social</label>
                    <input type="text" name="nome" required placeholder="Ex: João Silva ou Tech LTDA"
                        class="w-full mt-1 p-3 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <!-- Tipo -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Tipo de Pessoa</label>
                        <select name="tipo" id="tipo" onchange="ajustarMascara()" 
                            class="w-full mt-1 p-3 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                            <option value="F">Pessoa Física (CPF)</option>
                            <option value="J">Pessoa Jurídica (CNPJ)</option>
                        </select>
                    </div>

                    <!-- Documento -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700" id="labelDoc">CPF</label>
                        <input type="text" name="identificacao" id="identificacao" required placeholder="000.000.000-00"
                            class="w-full mt-1 p-3 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                    </div>
                </div>

                <!-- Botão Salvar -->
                <button type="submit" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-blue-200 transition-all mt-4">
                    Finalizar Cadastro
                </button>
            </form>
        </div>
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
            input.value = ''; // Limpa ao trocar
        }

        // Simples máscara de entrada (opcional, para visual)
        document.getElementById('identificacao').addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            const tipo = document.getElementById('tipo').value;

            if (tipo === 'F') {
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d)/, '$1.$2');
                value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            } else {
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