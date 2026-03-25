<?php 
include 'config.php'; 

$id = $_GET['id'];
$res = $conn->query("SELECT * FROM Cliente WHERE idCliente = $id");
$cliente = $res->fetch_assoc();

// Junta o nome para o campo de input
$nomeCompleto = trim($cliente['Pnome'] . " " . $cliente['NomeMeio'] . " " . $cliente['Sobrenome']);
$nomeCompleto = str_replace("  ", " ", $nomeCompleto);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Cliente | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-6">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-3xl shadow-xl p-8 border border-gray-100">
            <h1 class="text-2xl font-bold text-gray-800 mb-6">Editar Cliente #<?php echo $id; ?></h1>

            <form action="atualizar_cliente.php" method="POST" class="space-y-5">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700">Nome Completo</label>
                    <input type="text" name="nome" value="<?php echo $nomeCompleto; ?>" required 
                        class="w-full mt-1 p-3 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="grid grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Tipo</label>
                        <select name="tipo" class="w-full mt-1 p-3 bg-gray-50 border rounded-xl">
                            <option value="PF" <?php echo ($cliente['Tipo'] == 'PF') ? 'selected' : ''; ?>>Pessoa Física</option>
                            <option value="PJ" <?php echo ($cliente['Tipo'] == 'PJ') ? 'selected' : ''; ?>>Pessoa Jurídica</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700">Documento</label>
                        <input type="text" name="documento" value="<?php echo $cliente['Documento']; ?>" required 
                            class="w-full mt-1 p-3 bg-gray-50 border rounded-xl">
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-4 rounded-2xl hover:bg-blue-700 transition-all">
                    Salvar Alterações
                </button>
                <a href="clientes.php" class="block text-center text-gray-400 text-sm hover:underline">Cancelar</a>
            </form>
        </div>
    </div>
</body>
</html>