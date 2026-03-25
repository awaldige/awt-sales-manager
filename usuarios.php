<?php 
include 'auth.php'; 
include 'config.php'; 

// BLOQUEIO: Apenas admins acessam esta página
restringirAdmin();

// 1. LÓGICA DE EXCLUSÃO
if (isset($_GET['excluir'])) {
    $id_excluir = intval($_GET['excluir']);
    
    // Proteção: Impede que o usuário logado exclua a si mesmo
    if ($id_excluir == $_SESSION['usuario_id']) {
        header("Location: usuarios.php?erro=Você não pode excluir sua própria conta!");
        exit();
    } else {
        // Corrigido para idUsuario
        $conn->query("DELETE FROM usuarios WHERE idUsuario = $id_excluir");
        header("Location: usuarios.php?sucesso=Usuário removido com sucesso!");
        exit();
    }
}

// 2. LÓGICA DE CADASTRO OU EDIÇÃO
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome  = $_POST['nome'];
    $email = $_POST['email'];
    $nivel = $_POST['nivel'];
    
    // Se houver ID, é EDIÇÃO
    if (isset($_POST['id_usuario']) && !empty($_POST['id_usuario'])) {
        $id = intval($_POST['id_usuario']);
        
        if (!empty($_POST['senha'])) {
            $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET nome='$nome', email='$email', nivel='$nivel', senha='$senha' WHERE idUsuario=$id";
        } else {
            $sql = "UPDATE usuarios SET nome='$nome', email='$email', nivel='$nivel' WHERE idUsuario=$id";
        }
        $msg = "Usuário atualizado com sucesso!";
    } 
    // Se não houver ID, é CADASTRO NOVO
    else {
        $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuarios (nome, email, senha, nivel) VALUES ('$nome', '$email', '$senha', '$nivel')";
        $msg = "Usuário cadastrado com sucesso!";
    }

    if ($conn->query($sql)) {
        header("Location: usuarios.php?sucesso=$msg");
        exit();
    } else {
        header("Location: usuarios.php?erro=Erro no banco de dados: " . $conn->error);
        exit();
    }
}

// 3. BUSCAR DADOS PARA EDIÇÃO
$u_edit = null;
if (isset($_GET['editar'])) {
    $id_edit = intval($_GET['editar']);
    $res_edit = $conn->query("SELECT * FROM usuarios WHERE idUsuario = $id_edit");
    $u_edit = $res_edit->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gestão de Usuários | ERP PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 flex">

    <!-- SIDEBAR INTEGRADA -->
    <aside class="w-64 bg-slate-900 min-h-screen p-6 text-white hidden lg:flex flex-col sticky top-0 h-screen">
        <div class="text-2xl font-black mb-10 flex items-center gap-2 italic">
            <i class="fas fa-rocket text-blue-500"></i> ERP <span class="text-blue-500">PRO</span>
        </div>
        
        <nav class="space-y-2 flex-1">
            <a href="index.php" class="flex items-center gap-3 p-3 hover:bg-slate-800 rounded-xl transition text-slate-400 hover:text-white font-medium">
                <i class="fas fa-chart-pie w-5"></i> Dashboard
            </a>
            <a href="estoque.php" class="flex items-center gap-3 p-3 hover:bg-slate-800 rounded-xl transition text-slate-400 hover:text-white font-medium">
                <i class="fas fa-boxes w-5"></i> Estoque
            </a>
            <a href="produtos.php" class="flex items-center gap-3 p-3 hover:bg-slate-800 rounded-xl transition text-slate-400 hover:text-white font-medium">
                <i class="fas fa-box w-5"></i> Produtos
            </a>
            <a href="clientes.php" class="flex items-center gap-3 p-3 hover:bg-slate-800 rounded-xl transition text-slate-400 hover:text-white font-medium">
                <i class="fas fa-users w-5"></i> Clientes
            </a>
            <a href="pedidos.php" class="flex items-center gap-3 p-3 hover:bg-slate-800 rounded-xl transition text-slate-400 hover:text-white font-medium">
                <i class="fas fa-shopping-cart w-5"></i> Vendas
            </a>

            <?php if($_SESSION['usuario_nivel'] == 'admin'): ?>
            <div class="pt-4 pb-2 text-[10px] font-black uppercase text-slate-500 tracking-widest ml-3">Administração</div>
            <a href="usuarios.php" class="flex items-center gap-3 p-3 bg-blue-600 rounded-xl font-bold shadow-lg shadow-blue-900/20">
                <i class="fas fa-user-shield w-5"></i> Gestão de Equipe
            </a>
            <?php endif; ?>
        </nav>

        <div class="mt-auto pt-6 border-t border-slate-800">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center font-black text-white shadow-lg italic">
                    <?= strtoupper(substr($_SESSION['usuario_nome'], 0, 1)) ?>
                </div>
                <div class="truncate">
                    <p class="text-sm font-bold leading-none truncate"><?= $_SESSION['usuario_nome'] ?></p>
                    <p class="text-[9px] text-blue-400 uppercase font-black tracking-widest mt-1 italic"><?= $_SESSION['usuario_nivel'] ?></p>
                </div>
            </div>
            <a href="logout.php" class="flex items-center gap-3 p-3 text-red-400 hover:bg-red-500/10 rounded-xl transition font-bold text-xs uppercase tracking-tighter">
                <i class="fas fa-sign-out-alt w-4"></i> Sair do Sistema
            </a>
        </div>
    </aside>

    <main class="flex-1 p-8">
        <header class="mb-10">
            <h1 class="text-3xl font-black text-slate-800 uppercase italic leading-none">Gestão de Equipe</h1>
            <p class="text-slate-400 font-medium mt-1">Controle quem acessa o seu sistema.</p>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- FORMULÁRIO -->
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100 h-fit sticky top-8">
                <h2 class="text-lg font-black mb-6 uppercase italic text-blue-600">
                    <?= $u_edit ? 'Editar Usuário' : 'Novo Usuário' ?>
                </h2>
                
                <?php if(isset($_GET['sucesso'])): ?>
                    <div class="bg-emerald-50 text-emerald-600 p-4 rounded-xl mb-4 text-xs font-bold border border-emerald-100 italic">
                        <i class="fas fa-check-circle mr-2"></i> <?= $_GET['sucesso'] ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($_GET['erro'])): ?>
                    <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-4 text-xs font-bold border border-red-100 italic">
                        <i class="fas fa-exclamation-triangle mr-2"></i> <?= $_GET['erro'] ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <input type="hidden" name="id_usuario" value="<?= $u_edit['idUsuario'] ?? '' ?>">
                    
                    <div>
                        <label class="text-[10px] font-black uppercase text-slate-400 ml-2">Nome Completo</label>
                        <input type="text" name="nome" value="<?= $u_edit['nome'] ?? '' ?>" required class="w-full bg-slate-50 border-none p-3 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-bold text-slate-700">
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase text-slate-400 ml-2">E-mail (Login)</label>
                        <input type="email" name="email" value="<?= $u_edit['email'] ?? '' ?>" required class="w-full bg-slate-50 border-none p-3 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-bold text-slate-700">
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase text-slate-400 ml-2">Senha <?= $u_edit ? '(Deixe vazio para não alterar)' : '' ?></label>
                        <input type="password" name="senha" <?= $u_edit ? '' : 'required' ?> class="w-full bg-slate-50 border-none p-3 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-bold">
                    </div>
                    <div>
                        <label class="text-[10px] font-black uppercase text-slate-400 ml-2">Nível de Acesso</label>
                        <select name="nivel" class="w-full bg-slate-50 border-none p-3 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-black text-slate-700 uppercase italic">
                            <option value="colaborador" <?= (isset($u_edit) && $u_edit['nivel'] == 'colaborador') ? 'selected' : '' ?>>Colaborador</option>
                            <option value="admin" <?= (isset($u_edit) && $u_edit['nivel'] == 'admin') ? 'selected' : '' ?>>Administrador</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-slate-900 text-white p-4 rounded-xl font-black uppercase tracking-widest hover:bg-blue-600 transition-all shadow-lg shadow-slate-200">
                        <?= $u_edit ? 'Salvar Alterações' : 'Cadastrar Agora' ?>
                    </button>

                    <?php if($u_edit): ?>
                        <a href="usuarios.php" class="block text-center text-[10px] font-black uppercase text-slate-400 hover:text-slate-600 mt-4 tracking-widest">Cancelar Edição</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- LISTA DE USUÁRIOS -->
            <div class="lg:col-span-2 bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
                <h2 class="text-lg font-black mb-6 uppercase italic text-slate-800">Equipe Ativa</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-separate border-spacing-y-2">
                        <thead>
                            <tr class="text-[10px] text-slate-400 uppercase tracking-widest">
                                <th class="px-4 pb-2">Usuário</th>
                                <th class="px-4 pb-2 text-center">Acesso</th>
                                <th class="px-4 pb-2 text-right">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $usuarios = $conn->query("SELECT * FROM usuarios ORDER BY nome ASC");
                            while($user = $usuarios->fetch_assoc()): 
                            ?>
                            <tr class="group">
                                <td class="px-4 py-4 bg-slate-50 rounded-l-2xl">
                                    <div class="font-black text-slate-700 italic"><?= $user['nome'] ?></div>
                                    <div class="text-[10px] text-slate-400 font-bold"><?= $user['email'] ?></div>
                                </td>
                                <td class="px-4 py-4 bg-slate-50 border-x border-white text-center">
                                    <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase <?= $user['nivel'] == 'admin' ? 'bg-blue-100 text-blue-600 border border-blue-200' : 'bg-slate-200 text-slate-600 border border-slate-300' ?>">
                                        <?= $user['nivel'] ?>
                                    </span>
                                </td>
                                <td class="px-4 py-4 bg-slate-50 rounded-r-2xl text-right">
                                    <div class="flex justify-end gap-2">
                                        <a href="usuarios.php?editar=<?= $user['idUsuario'] ?>" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white text-blue-500 hover:bg-blue-500 hover:text-white transition shadow-sm border border-slate-100">
                                            <i class="fas fa-edit text-xs"></i>
                                        </a>
                                        <a href="javascript:if(confirm('Remover <?= addslashes($user['nome']) ?>?')) { window.location.href='usuarios.php?excluir=<?= $user['idUsuario'] ?>'; }" 
                                           class="w-8 h-8 flex items-center justify-center rounded-lg bg-white text-red-400 hover:bg-red-500 hover:text-white transition shadow-sm border border-slate-100">
                                            <i class="fas fa-trash text-xs"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
</body>
</html>