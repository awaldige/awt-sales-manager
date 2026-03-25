<?php 
include 'config.php'; 

// SQL CORRIGIDO: Usando RazaoSocial para fornecedor e nomeCategoria para categoria
$fornecedores = $conn->query("SELECT idFornecedor, RazaoSocial FROM fornecedor ORDER BY RazaoSocial ASC");
$categorias_db = $conn->query("SELECT idCategoria, nomeCategoria FROM categoria ORDER BY nomeCategoria ASC");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recebendo dados do formulário
    $descricao    = $_POST['descricao'];
    $categoriaStr = $_POST['categoria_texto']; 
    $valor        = str_replace(',', '.', $_POST['valor_custo']);
    $precoVenda   = str_replace(',', '.', $_POST['preco_venda']);
    $idFornecedor = $_POST['idFornecedor'];
    $idCategoria  = $_POST['idCategoria']; 
    $estoque      = intval($_POST['estoque_inicial']);

    // SQL DE INSERÇÃO: Ajustado para sua estrutura real
    $sql = "INSERT INTO produtos (Categoria, Descricao, Valor, idFornecedor, idCategoria, precoVenda, estoque) 
            VALUES ('$categoriaStr', '$descricao', '$valor', '$idFornecedor', '$idCategoria', '$precoVenda', '$estoque')";

    if ($conn->query($sql)) {
        header("Location: produtos.php?sucesso=1");
        exit();
    } else {
        echo "Erro ao cadastrar: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Novo Produto | ERP PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-slate-50 p-6 md:p-12 font-['Inter']">

    <div class="max-w-3xl mx-auto bg-white p-10 rounded-[2.5rem] shadow-sm border border-slate-100">
        <header class="mb-10 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-black text-slate-900 uppercase italic tracking-tighter">Novo Produto</h1>
                <p class="text-slate-400 font-bold text-xs uppercase italic">Cadastro técnico de inventário</p>
            </div>
            <a href="produtos.php" class="text-slate-400 hover:text-slate-600 transition"><i class="fas fa-times fa-lg"></i></a>
        </header>

        <form method="POST" class="space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <!-- Nome/Descrição -->
                <div class="md:col-span-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-4 mb-2 block">Descrição do Produto</label>
                    <input type="text" name="descricao" required placeholder="Ex: Cadeira Gamer Black Edition" 
                           class="w-full p-5 bg-slate-50 border-2 border-transparent focus:border-blue-500 focus:bg-white rounded-3xl font-bold text-slate-700 transition outline-none">
                </div>

                <!-- Categoria Texto -->
                <div>
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-4 mb-2 block">Categoria (Nome)</label>
                    <input type="text" name="categoria_texto" placeholder="Ex: Móveis" 
                           class="w-full p-5 bg-slate-50 border-none rounded-3xl font-bold text-slate-700 outline-none">
                </div>

                <!-- Categoria ID (Relacional) -->
                <div>
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-4 mb-2 block">Vincular Categoria</label>
                    <select name="idCategoria" class="w-full p-5 bg-slate-50 border-none rounded-3xl font-bold text-slate-700 outline-none appearance-none">
                        <option value="">Selecione...</option>
                        <?php while($c = $categorias_db->fetch_assoc()): ?>
                            <!-- CORRIGIDO: nomeCategoria -->
                            <option value="<?= $c['idCategoria'] ?>"><?= $c['nomeCategoria'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Custos -->
                <div>
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-4 mb-2 block">Custo Unitário (R$)</label>
                    <input type="text" name="valor_custo" placeholder="0,00" 
                           class="w-full p-5 bg-slate-50 border-none rounded-3xl font-black text-slate-800 outline-none text-xl">
                </div>

                <div>
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-4 mb-2 block">Preço de Venda (R$)</label>
                    <input type="text" name="preco_venda" placeholder="0,00" 
                           class="w-full p-5 bg-blue-50 border-none rounded-3xl font-black text-blue-600 outline-none text-xl">
                </div>

                <!-- Fornecedor -->
                <div>
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-4 mb-2 block">Fornecedor Principal</label>
                    <select name="idFornecedor" class="w-full p-5 bg-slate-50 border-none rounded-3xl font-bold text-slate-700 outline-none appearance-none">
                        <?php while($f = $fornecedores->fetch_assoc()): ?>
                            <option value="<?= $f['idFornecedor'] ?>"><?= $f['RazaoSocial'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Estoque -->
                <div>
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-4 mb-2 block">Estoque Inicial</label>
                    <input type="number" name="estoque_inicial" value="0" 
                           class="w-full p-5 bg-slate-50 border-none rounded-3xl font-black text-slate-700 text-center outline-none">
                </div>

            </div>

            <div class="pt-6">
                <button type="submit" class="w-full p-6 bg-slate-900 text-white rounded-[2rem] font-black uppercase tracking-[0.2em] hover:bg-blue-600 transition shadow-2xl shadow-blue-100">
                    Finalizar Cadastro
                </button>
            </div>
        </form>
    </div>

</body>
</html>