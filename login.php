<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $senha = $_POST['senha'];

    $sql = "SELECT * FROM usuarios WHERE email = '$email'";
    $res = $conn->query($sql);

    if ($res->num_rows > 0) {
        $user = $res->fetch_assoc();
        // Verifica a senha criptografada        
if (password_verify($senha, $user['senha'])) {
    $_SESSION['usuario_idUsuario'] = $user['idUsuario']; // Adicionado 'Usuario' no final
    $_SESSION['usuario_nome']      = $user['nome'];
    $_SESSION['usuario_nivel']     = $user['nivel'];
    
    header("Location: index.php");
    exit();
}
    }
    $erro = "E-mail ou senha incorretos!";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login | ERP PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 h-screen flex items-center justify-center p-6">
    <div class="bg-white w-full max-w-md rounded-[2.5rem] p-10 shadow-2xl">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-black text-slate-800 italic uppercase">ERP <span class="text-blue-600">PRO</span></h2>
            <p class="text-slate-400 font-medium">Faça login para continuar</p>
        </div>

        <?php if(isset($erro)): ?>
            <div class="bg-red-50 text-red-500 p-4 rounded-xl mb-6 text-sm font-bold border border-red-100">
                <?= $erro ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="text-[10px] font-black uppercase text-slate-400 ml-4">E-mail</label>
                <input type="email" name="email" required class="w-full bg-slate-50 border-none p-4 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <div>
                <label class="text-[10px] font-black uppercase text-slate-400 ml-4">Senha</label>
                <input type="password" name="senha" required class="w-full bg-slate-50 border-none p-4 rounded-2xl focus:ring-2 focus:ring-blue-500 outline-none">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white p-4 rounded-2xl font-black uppercase tracking-widest hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/30">
                Entrar no Sistema
            </button>
        </form>
    </div>
</body>
</html>