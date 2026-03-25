<?php 
// 1. Conexão com o banco de dados
include 'config.php'; 

$mensagem = "";

// 2. Processamento do Formulário
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $categoria = $_POST['categoria'];
    $descricao = $_POST['descricao'];
    $valor = $_POST['valor'];
    $idFornecedor = $_POST['idFornecedor']; 

    // Usando Prepared Statements para maior segurança
    $stmt = $conn->prepare("INSERT INTO Produtos (Categoria, Descricao, Valor, idFornecedor) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssdi", $categoria, $descricao, $valor, $idFornecedor);
    
    if ($stmt->execute()) {
        // Redireciona para o index com status de sucesso para exibir o alerta azul/verde que você já tem lá
        header("Location: index.php?status=success");
        exit;
    } else {
        $mensagem = "<div class='mb-4 p-4 bg-red-100 text-red-700 rounded-xl border border-red-200'>❌ Erro ao salvar: " . $conn->error . "</div>";
    }
}

// 3. Buscar fornecedores para preencher o Select
$res_fornecedores = $conn->query("SELECT idFornecedor, RazaoSocial FROM Fornecedor ORDER BY RazaoSocial ASC");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Produtos | Ecommerce Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans antialiased text-gray-900">

    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="bg-white w-full max-w-md p-8 rounded-3xl shadow-2xl shadow-blue-100/50 border border-gray-100">
            
            <div class="text-center mb-8">
                <div class="inline-block p-3 bg-blue-50 rounded-2xl mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Novo Produto</h1>
                <p class="text-gray-500">Preencha os dados abaixo</p>
            </div>

            <?php echo $mensagem; ?>

            <form action="cadastro.php" method="POST" class="space-y-5">
                
                <!-- Seleção de Fornecedor -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1 ml-1">Fornecedor Responsável</label>
                    <select name="idFornecedor" required class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all cursor-pointer">
                        <option value="">Selecione um parceiro...</option>
                        <?php while($f = $res_fornecedores->fetch_assoc()): ?>
                            <option value="<?php echo $f['idFornecedor']; ?>">
                                <?php echo $f['RazaoSocial']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Seleção de Categoria -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1 ml-1">Categoria</label>
                    <select name="categoria" required class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all cursor-pointer">
                        <option value="Eletrônicos">Eletrônicos</option>
                        <option value="Móveis">Móveis</option>
                        <option value="Eletrodomésticos">Eletrodomésticos</option>
                        <option value="Informática">Informática</option>
                    </select>
                </div>

                <!-- Descrição -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1 ml-1">Descrição</label>
                    <input type="text" name="descricao" placeholder="Ex: Monitor Gamer 24''" required 
                        class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all">
                </div>

                <!-- Preço -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1 ml-1">Preço de Venda (R$)</label>
                    <input type="number" step="0.01" name="valor" placeholder="0.00" required 
                        class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:bg-white outline-none transition-all">
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-200 transition-all active:scale-[0.98]">
                    Cadastrar Produto
                </button>

                <div class="text-center mt-6">
                    <a href="index.php" class="text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors">
                        ← Voltar para a Lista de Produtos
                    </a>
                </div>
            </form>

        </div>
    </div>

</body>
</html>