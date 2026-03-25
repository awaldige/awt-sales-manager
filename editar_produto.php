<?php 
include 'config.php'; 

$id = $_GET['id'];
$produto = $conn->query("SELECT * FROM produtos WHERE idProduto = $id")->fetch_assoc();
$categorias = $conn->query("SELECT * FROM categoria ORDER BY nomeCategoria ASC");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $desc  = $_POST['Descricao'];
    $preco = $_POST['precoVenda'];
    $cat   = $_POST['idCategoria'];

    $sql = "UPDATE produtos SET Descricao='$desc', precoVenda='$preco', idCategoria='$cat' WHERE idProduto=$id";
    
    if ($conn->query($sql)) {
        header("Location: produtos.php");
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Produto</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-6">
    <div class="max-w-md w-full bg-white p-10 rounded-[3rem] shadow-xl border border-slate-100">
        <h2 class="text-2xl font-black text-slate-800 mb-6 italic text-center text-amber-500">Editar Registro</h2>
        
        <form method="POST" class="space-y-5">
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Descrição do Produto</label>
                <input type="text" name="Descricao" value="<?= $produto['Descricao'] ?>" required 
                       class="w-full p-4 bg-slate-100 rounded-2xl outline-none font-bold">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Preço Venda</label>
                    <input type="number" step="0.01" name="precoVenda" value="<?= $produto['precoVenda'] ?>" required 
                           class="w-full p-4 bg-slate-100 rounded-2xl outline-none font-black text-blue-600">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">Categoria</label>
                    <select name="idCategoria" required class="w-full p-4 bg-slate-100 rounded-2xl outline-none font-bold">
                        <?php while($c = $categorias->fetch_assoc()): ?>
                            <option value="<?= $c['idCategoria'] ?>" <?= ($c['idCategoria'] == $produto['idCategoria']) ? 'selected' : '' ?>>
                                <?= $c['nomeCategoria'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <button type="submit" class="w-full bg-slate-900 text-white p-5 rounded-2xl font-black text-lg hover:shadow-lg transition">Salvar Alterações</button>
            <a href="produtos.php" class="block text-center text-slate-400 font-bold text-sm mt-2">Cancelar</a>
        </form>
    </div>
</body>
</html>