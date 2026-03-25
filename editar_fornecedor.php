<?php 
include 'config.php'; 

// 1. Buscar os dados atuais do fornecedor
$id = $_GET['id'] ?? null;
if (!$id) { header("Location: fornecedores.php"); exit; }

$sql = "SELECT * FROM Fornecedor WHERE idFornecedor = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$fornecedor = $result->fetch_assoc();

if (!$fornecedor) { die("Fornecedor não encontrado."); }
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Fornecedor | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-6">
    <div class="max-w-xl mx-auto">
        <a href="fornecedores.php" class="text-indigo-600 font-bold mb-6 block">← Voltar para Lista</a>
        
        <div class="bg-white p-8 rounded-3xl shadow-xl border border-gray-100">
            <h1 class="text-2xl font-bold mb-2 text-gray-800">Editar Parceiro</h1>
            <p class="text-gray-400 mb-6">Atualizando dados do ID #<?php echo $id; ?></p>
            
            <form action="atualizar_fornecedor.php" method="POST" class="space-y-4">
                <input type="hidden" name="id" value="<?php echo $fornecedor['idFornecedor']; ?>">

                <div>
                    <label class="block text-sm font-semibold text-gray-700">Razão Social</label>
                    <input type="text" name="razao" required 
                           value="<?php echo $fornecedor['RazaoSocial']; ?>"
                           class="w-full mt-1 p-3 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700">CNPJ</label>
                    <input type="text" name="cnpj" id="cnpj" required 
                           value="<?php echo $fornecedor['CNPJ']; ?>"
                           class="w-full mt-1 p-3 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Telefone</label>
                        <input type="text" name="telefone" 
                               value="<?php echo $fornecedor['Telefone']; ?>"
                               class="w-full mt-1 p-3 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">E-mail</label>
                        <input type="email" name="email" 
                               value="<?php echo $fornecedor['Email']; ?>"
                               class="w-full mt-1 p-3 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-4 rounded-2xl shadow-lg shadow-indigo-100 mt-4 hover:bg-indigo-700 transition-all">
                    Salvar Alterações
                </button>
            </form>
        </div>
    </div>

    <script>
        // Mesma máscara de CNPJ para manter a consistência
        document.getElementById('cnpj').addEventListener('input', function (e) {
            let x = e.target.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,3})(\d{0,3})(\d{0,4})(\d{0,2})/);
            e.target.value = !x[2] ? x[1] : x[1] + '.' + x[2] + '.' + x[3] + '/' + x[4] + (x[5] ? '-' + x[5] : '');
        });
    </script>
</body>
</html>