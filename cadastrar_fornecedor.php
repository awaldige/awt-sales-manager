<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Novo Fornecedor | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-6">
    <div class="max-w-xl mx-auto">
        <a href="fornecedores.php" class="text-indigo-600 font-bold mb-6 block">← Voltar</a>
        <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100">
            <h1 class="text-2xl font-bold mb-6 text-gray-800">Cadastrar Fornecedor</h1>
            
            <form action="salvar_fornecedor.php" method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Razão Social</label>
                    <input type="text" name="razao" required class="w-full mt-1 p-3 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700">CNPJ</label>
                    <input type="text" name="cnpj" id="cnpj" required placeholder="00.000.000/0000-00" class="w-full mt-1 p-3 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Telefone</label>
                        <input type="text" name="telefone" placeholder="(00) 00000-0000" class="w-full mt-1 p-3 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">E-mail</label>
                        <input type="email" name="email" required class="w-full mt-1 p-3 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-4 rounded-2xl shadow-lg shadow-indigo-100 mt-4 hover:bg-indigo-700 transition-all">
                    Salvar Parceiro
                </button>
            </form>
        </div>
    </div>

    <script>
        // Máscara simples para CNPJ
        document.getElementById('cnpj').addEventListener('input', function (e) {
            let x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,3})(\d{0,3})(\d{0,4})(\d{0,2})/);
            e.target.value = !x[2] ? x[1] : x[1] + '.' + x[2] + '.' + x[3] + '/' + x[4] + (x[5] ? '-' + x[5] : '');
        });
    </script>
</body>
</html>