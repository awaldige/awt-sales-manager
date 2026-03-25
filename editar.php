<?php 
include 'config.php'; 

// 1. Buscar os dados atuais do produto para preencher o formulário
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $res = $conn->query("SELECT * FROM Produtos WHERE idProduto = $id");
    $produto = $res->fetch_assoc();
}

// 2. Buscar todos os fornecedores cadastrados para preencher o <select>
$fornecedores_res = $conn->query("SELECT idFornecedor, RazaoSocial FROM Fornecedor ORDER BY RazaoSocial ASC");

// 3. Processar a atualização quando clicar em "Salvar Alterações"
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['idProduto'];
    $categoria = $_POST['categoria'];
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];
    $idFornecedor = $_POST['idFornecedor']; // Novo campo capturado

    // Atualizamos também o idFornecedor na tabela Produtos
    $sql = "UPDATE Produtos SET 
            Categoria='$categoria', 
            Descricao='$descricao', 
            Valor='$valor', 
            idFornecedor='$idFornecedor' 
            WHERE idProduto=$id";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: index.php?status=updated");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Produto | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white p-8 rounded-3xl shadow-xl w-full max-w-md border border-gray-100">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800 text-center">Editar Produto</h1>
            <span class="bg-gray-100 text-gray-500 px-3 py-1 rounded-lg text-xs font-mono">#<?php echo $produto['idProduto']; ?></span>
        </div>

        <form method="POST" class="space-y-4">
            <input type="hidden" name="idProduto" value="<?php echo $produto['idProduto']; ?>">
            
            <!-- Campo Categoria -->
            <div>
                <label class="block text-sm font-semibold text-gray-700">Categoria</label>
                <select name="categoria" class="w-full mt-1 p-3 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="Eletrônicos" <?php if($produto['Categoria'] == 'Eletrônicos') echo 'selected'; ?>>Eletrônicos</option>
                    <option value="Móveis" <?php if($produto['Categoria'] == 'Móveis') echo 'selected'; ?>>Móveis</option>
                    <option value="Eletrodomésticos" <?php if($produto['Categoria'] == 'Eletrodomésticos') echo 'selected'; ?>>Eletrodomésticos</option>
                    <option value="Informática" <?php if($produto['Categoria'] == 'Informática') echo 'selected'; ?>>Informática</option>
                </select>
            </div>

            <!-- NOVO: Campo Fornecedor (Dinamico) -->
            <div>
                <label class="block text-sm font-semibold text-gray-700">Fornecedor Responsável</label>
                <select name="idFornecedor" required class="w-full mt-1 p-3 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 border-indigo-100">
                    <option value="">Selecione um fornecedor</option>
                    <?php while($f = $fornecedores_res->fetch_assoc()): ?>
                        <option value="<?php echo $f['idFornecedor']; ?>" <?php if($produto['idFornecedor'] == $f['idFornecedor']) echo 'selected'; ?>>
                            <?php echo $f['RazaoSocial']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Campo Descrição -->
            <div>
                <label class="block text-sm font-semibold text-gray-700">Descrição do Produto</label>
                <input type="text" name="descricao" value="<?php echo $produto['Descricao']; ?>" required 
                       class="w-full mt-1 p-3 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Campo Valor -->
            <div>
                <label class="block text-sm font-semibold text-gray-700">Valor de Venda (R$)</label>
                <input type="number" step="0.01" name="valor" value="<?php echo $produto['Valor']; ?>" required 
                       class="w-full mt-1 p-3 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded-xl hover:bg-blue-700 transition-all shadow-lg shadow-blue-100">
                Salvar Alterações
            </button>
            
            <a href="index.php" class="block text-center text-sm text-gray-400 mt-4 hover:text-gray-600">Cancelar e Voltar</a>
        </form>
    </div>
</body>
</html>