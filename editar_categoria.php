<?php 
include 'config.php'; 

// 1. Verificar se o ID foi passado na URL
if (!isset($_GET['id'])) {
    header("Location: categorias.php");
    exit;
}

$id = $_GET['id'];

// 2. Buscar os dados atuais da categoria
$resultado = $conn->query("SELECT * FROM categoria WHERE idCategoria = $id");
$cat = $resultado->fetch_assoc();

if (!$cat) {
    header("Location: categorias.php");
    exit;
}

// 3. Lógica para ATUALIZAR
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $novoNome = $_POST['nomeCategoria'];
    
    $sql = "UPDATE categoria SET nomeCategoria = '$novoNome' WHERE idCategoria = $id";
    
    if ($conn->query($sql)) {
        header("Location: categorias.php");
        exit;
    } else {
        $erro = "Erro ao atualizar: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Categoria | ERP PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-6">

    <div class="max-w-md w-full bg-white p-10 rounded-[3rem] shadow-xl border border-slate-100">
        
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-amber-50 text-amber-500 rounded-3xl mb-4">
                <i class="fas fa-edit fa-2xl"></i>
            </div>
            <h1 class="text-2xl font-black text-slate-800">Editar Categoria</h1>
            <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mt-1">ID do Registro: #<?php echo $id; ?></p>
        </div>

        <?php if(isset($erro)): ?>
            <div class="bg-red-50 text-red-500 p-4 rounded-2xl mb-6 text-sm font-bold text-center">
                <?php echo $erro; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase mb-2 ml-1">Nome Atualizado</label>
                <input type="text" name="nomeCategoria" required 
                       value="<?php echo $cat['nomeCategoria']; ?>"
                       class="w-full p-5 bg-slate-100 rounded-2xl outline-none focus:ring-4 ring-amber-100 border-2 border-transparent focus:border-amber-400 transition-all font-bold text-slate-700 text-lg">
            </div>

            <div class="flex flex-col gap-3">
                <button type="submit" class="w-full bg-slate-900 text-white p-5 rounded-2xl font-black text-lg hover:bg-black transition shadow-lg shadow-slate-200">
                    Atualizar Agora
                </button>
                
                <a href="categorias.php" class="text-center p-4 text-slate-400 font-bold hover:text-slate-600 transition text-sm">
                    <i class="fas fa-times mr-1"></i> Cancelar Alteração
                </a>
            </div>
        </form>

    </div>

</body>
</html>