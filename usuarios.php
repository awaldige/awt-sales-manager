<?php

require_once 'auth.php';
require_once 'config.php';

restringirAdmin();

if (empty($_SESSION['csrf_usuarios'])) {
    $_SESSION['csrf_usuarios'] = bin2hex(random_bytes(32));
}

function escapar($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function redirecionar(string $codigo): void
{
    header('Location: usuarios.php?resultado=' . urlencode($codigo));
    exit();
}

$mensagens = [
    'criado' => ['sucesso', 'Usuário cadastrado com sucesso!'],
    'atualizado' => ['sucesso', 'Usuário atualizado com sucesso!'],
    'excluido' => ['sucesso', 'Usuário removido com sucesso!'],
    'dados_invalidos' => ['erro', 'Confira os dados informados.'],
    'email_existente' => ['erro', 'Este e-mail já está cadastrado.'],
    'senha_curta' => ['erro', 'A senha deve possuir pelo menos 8 caracteres.'],
    'senha_longa' => ['erro', 'A senha não pode ultrapassar 72 bytes.'],
    'csrf' => ['erro', 'Sessão expirada ou solicitação inválida. Atualize a página.'],
    'autoexclusao' => ['erro', 'Você não pode excluir sua própria conta.'],
    'ultimo_admin' => ['erro', 'Não é possível remover ou rebaixar o último administrador.'],
    'auto_rebaixamento' => ['erro', 'Você não pode alterar seu próprio nível de administrador.'],
    'nao_encontrado' => ['erro', 'Usuário não encontrado.'],
    'erro_banco' => ['erro', 'Não foi possível concluir a operação. Tente novamente.']
];

$tipoMensagem = '';
$textoMensagem = '';

$codigoResultado = $_GET['resultado'] ?? '';

if (isset($mensagens[$codigoResultado])) {
    [$tipoMensagem, $textoMensagem] = $mensagens[$codigoResultado];
}

// =====================================================
// CADASTRO, EDIÇÃO E EXCLUSÃO
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_usuarios'], $token)
    ) {
        redirecionar('csrf');
    }

    $acao = $_POST['acao'] ?? '';

    try {

        // =============================================
        // EXCLUSÃO
        // =============================================

        if ($acao === 'excluir') {

            $id = filter_var(
                $_POST['id_usuario'] ?? null,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            if (!$id) {
                redirecionar('dados_invalidos');
            }

            if ($id === (int) $_SESSION['usuario_idUsuario']) {
                redirecionar('autoexclusao');
            }

            $conn->begin_transaction();

            $stmt = $conn->prepare(
                "SELECT idUsuario, nivel
                 FROM usuarios
                 WHERE idUsuario = ?
                 FOR UPDATE"
            );

            $stmt->bind_param('i', $id);
            $stmt->execute();

            $usuarioAlvo = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$usuarioAlvo) {
                $conn->rollback();
                redirecionar('nao_encontrado');
            }

            if ($usuarioAlvo['nivel'] === 'admin') {

                // Bloqueia os registros de administradores
                // para proteger a contagem durante a operação.
                $admins = $conn->query(
                    "SELECT idUsuario
                     FROM usuarios
                     WHERE nivel = 'admin'
                     FOR UPDATE"
                );

                $totalAdmins = $admins->num_rows;

                if ($totalAdmins <= 1) {
                    $conn->rollback();
                    redirecionar('ultimo_admin');
                }
            }

            $stmt = $conn->prepare(
                "DELETE FROM usuarios WHERE idUsuario = ?"
            );

            $stmt->bind_param('i', $id);
            $stmt->execute();

            if ($stmt->affected_rows !== 1) {
                $stmt->close();
                $conn->rollback();
                redirecionar('nao_encontrado');
            }

            $stmt->close();
            $conn->commit();

            redirecionar('excluido');
        }

        // =============================================
        // CADASTRO E EDIÇÃO
        // =============================================

        if ($acao === 'salvar') {

            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $senha = $_POST['senha'] ?? '';
            $nivel = $_POST['nivel'] ?? '';

            $id = filter_var(
                $_POST['id_usuario'] ?? '',
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            $editando = !empty($_POST['id_usuario']);

            if (
                $nome === '' ||
                mb_strlen($nome, 'UTF-8') > 150 ||
                !filter_var($email, FILTER_VALIDATE_EMAIL) ||
                strlen($email) > 255 ||
                !in_array($nivel, ['admin', 'colaborador'], true)
            ) {
                redirecionar('dados_invalidos');
            }

            if (!$editando && $senha === '') {
                redirecionar('dados_invalidos');
            }

            if ($senha !== '') {

                if (strlen($senha) < 8) {
                    redirecionar('senha_curta');
                }

                if (strlen($senha) > 72) {
                    redirecionar('senha_longa');
                }
            }

            // =========================================
            // EDIÇÃO
            // =========================================

            if ($editando) {

                if (!$id) {
                    redirecionar('dados_invalidos');
                }

                $conn->begin_transaction();

                $stmt = $conn->prepare(
                    "SELECT idUsuario, nivel
                     FROM usuarios
                     WHERE idUsuario = ?
                     FOR UPDATE"
                );

                $stmt->bind_param('i', $id);
                $stmt->execute();

                $usuarioAtual = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!$usuarioAtual) {
                    $conn->rollback();
                    redirecionar('nao_encontrado');
                }

                $usuarioLogado = (
                    $id === (int) $_SESSION['usuario_idUsuario']
                );

                if (
                    $usuarioLogado &&
                    $usuarioAtual['nivel'] === 'admin' &&
                    $nivel !== 'admin'
                ) {
                    $conn->rollback();
                    redirecionar('auto_rebaixamento');
                }

                if (
                    $usuarioAtual['nivel'] === 'admin' &&
                    $nivel !== 'admin'
                ) {

                    $admins = $conn->query(
                        "SELECT idUsuario
                         FROM usuarios
                         WHERE nivel = 'admin'
                         FOR UPDATE"
                    );

                    if ($admins->num_rows <= 1) {
                        $conn->rollback();
                        redirecionar('ultimo_admin');
                    }
                }

                if ($senha !== '') {

                    $senhaHash = password_hash(
                        $senha,
                        PASSWORD_DEFAULT
                    );

                    $stmt = $conn->prepare(
                        "UPDATE usuarios
                         SET nome = ?, email = ?, senha = ?, nivel = ?
                         WHERE idUsuario = ?"
                    );

                    $stmt->bind_param(
                        'ssssi',
                        $nome,
                        $email,
                        $senhaHash,
                        $nivel,
                        $id
                    );

                } else {

                    $stmt = $conn->prepare(
                        "UPDATE usuarios
                         SET nome = ?, email = ?, nivel = ?
                         WHERE idUsuario = ?"
                    );

                    $stmt->bind_param(
                        'sssi',
                        $nome,
                        $email,
                        $nivel,
                        $id
                    );
                }

                $stmt->execute();
                $stmt->close();

                $conn->commit();

                redirecionar('atualizado');
            }

            // =========================================
            // NOVO USUÁRIO
            // =========================================

            $senhaHash = password_hash(
                $senha,
                PASSWORD_DEFAULT
            );

            $stmt = $conn->prepare(
                "INSERT INTO usuarios (nome, email, senha, nivel)
                 VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                'ssss',
                $nome,
                $email,
                $senhaHash,
                $nivel
            );

            $stmt->execute();
            $stmt->close();

            redirecionar('criado');
        }

        redirecionar('dados_invalidos');

    } catch (mysqli_sql_exception $e) {

        try {
            $conn->rollback();
        } catch (Throwable $rollbackError) {
            // Não há transação ativa para reverter.
        }

        if ((int) $e->getCode() === 1062) {
            redirecionar('email_existente');
        }

        error_log('Erro no gerenciamento de usuários: ' . $e->getMessage());

        redirecionar('erro_banco');

    } catch (Throwable $e) {

        try {
            $conn->rollback();
        } catch (Throwable $rollbackError) {
            // Não há transação ativa para reverter.
        }

        error_log('Erro no gerenciamento de usuários: ' . $e->getMessage());

        redirecionar('erro_banco');
    }
}

// =====================================================
// BUSCAR USUÁRIO PARA EDIÇÃO
// =====================================================

$u_edit = null;

if (isset($_GET['editar'])) {

    $idEdit = filter_var(
        $_GET['editar'],
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($idEdit) {

        $stmt = $conn->prepare(
            "SELECT idUsuario, nome, email, nivel
             FROM usuarios
             WHERE idUsuario = ?
             LIMIT 1"
        );

        $stmt->bind_param('i', $idEdit);
        $stmt->execute();

        $u_edit = $stmt->get_result()->fetch_assoc();

        $stmt->close();
    }
}

// =====================================================
// LISTAGEM DE USUÁRIOS
// =====================================================

$usuarios = $conn->query(
    "SELECT idUsuario, nome, email, nivel
     FROM usuarios
     ORDER BY nome ASC"
);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gestão de Equipe | AWT Sales Manager</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    >

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-100 flex min-h-screen text-slate-800">

    <!-- SIDEBAR -->

    <aside class="w-64 bg-slate-900 min-h-screen p-6 text-white hidden lg:flex flex-col sticky top-0 h-screen">

        <div class="text-2xl font-black mb-10 flex items-center gap-2 italic">
            <i class="fas fa-rocket text-blue-500"></i>
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-lg not-italic">AW</span><span class="text-base not-italic">AWT Sales Manager</span>
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

            <div class="pt-4 pb-2 text-[10px] font-black uppercase text-slate-500 tracking-widest ml-3">
                Administração
            </div>

            <a href="usuarios.php" class="flex items-center gap-3 p-3 bg-blue-600 rounded-xl font-bold shadow-lg shadow-blue-900/20">
                <i class="fas fa-user-shield w-5"></i> Gestão de Equipe
            </a>

        </nav>

        <div class="mt-auto pt-6 border-t border-slate-800">

            <div class="flex items-center gap-3 mb-4">

                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center font-black text-white shadow-lg italic">
                    <?= escapar(mb_strtoupper(mb_substr($_SESSION['usuario_nome'] ?? '', 0, 1, 'UTF-8'), 'UTF-8')) ?>
                </div>

                <div class="truncate">

                    <p class="text-sm font-bold leading-none truncate">
                        <?= escapar($_SESSION['usuario_nome'] ?? '') ?>
                    </p>

                    <p class="text-[9px] text-blue-400 uppercase font-black tracking-widest mt-1 italic">
                        <?= escapar($_SESSION['usuario_nivel'] ?? '') ?>
                    </p>

                </div>

            </div>

            <a href="logout.php" class="flex items-center gap-3 p-3 text-red-400 hover:bg-red-500/10 rounded-xl transition font-bold text-xs uppercase tracking-tighter">
                <i class="fas fa-sign-out-alt w-4"></i>
                Sair do Sistema
            </a>

        </div>

    </aside>

    <!-- CONTEÚDO PRINCIPAL -->

    <main class="flex-1 min-w-0 p-4 sm:p-6 lg:p-8">

        <header class="mb-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">

            <h1 class="text-3xl font-black text-slate-800 uppercase italic leading-none">
                Gestão de Equipe
            </h1>

            <p class="text-slate-400 font-medium mt-2">
                Controle quem acessa o seu sistema.
            </p>

        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- FORMULÁRIO -->

            <div class="bg-white p-6 md:p-8 rounded-[2.5rem] shadow-sm border border-slate-100 h-fit lg:sticky lg:top-8">

                <h2 class="text-lg font-black mb-6 uppercase italic text-blue-600">
                    <?= $u_edit ? 'Editar Usuário' : 'Novo usuário' ?>
                </h2>

                <?php if ($textoMensagem !== ''): ?>

                    <div class="<?= $tipoMensagem === 'sucesso' ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-red-50 text-red-600 border-red-100' ?> p-4 rounded-xl mb-5 text-xs font-bold border">
                        <i class="fas <?= $tipoMensagem === 'sucesso' ? 'fa-check-circle' : 'fa-exclamation-triangle' ?> mr-2"></i>
                        <?= escapar($textoMensagem) ?>
                    </div>

                <?php endif; ?>

                <form method="POST" action="usuarios.php" class="space-y-4">

                    <input type="hidden" name="acao" value="salvar">

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= escapar($_SESSION['csrf_usuarios']) ?>"
                    >

                    <input
                        type="hidden"
                        name="id_usuario"
                        value="<?= escapar($u_edit['idUsuario'] ?? '') ?>"
                    >

                    <div>

                        <label class="text-[10px] font-black uppercase text-slate-400 ml-2">
                            Nome Completo
                        </label>

                        <input
                            type="text"
                            name="nome"
                            maxlength="150"
                            value="<?= escapar($u_edit['nome'] ?? '') ?>"
                            required
                            class="w-full bg-slate-50 border-none p-3 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-bold text-slate-700"
                        >

                    </div>

                    <div>

                        <label class="text-[10px] font-black uppercase text-slate-400 ml-2">
                            E-mail (Login)
                        </label>

                        <input
                            type="email"
                            name="email"
                            maxlength="255"
                            value="<?= escapar($u_edit['email'] ?? '') ?>"
                            required
                            class="w-full bg-slate-50 border-none p-3 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-bold text-slate-700"
                        >

                    </div>

                    <div>

                        <label class="text-[10px] font-black uppercase text-slate-400 ml-2">
                            Senha <?= $u_edit ? '(Deixe vazio para não alterar)' : '' ?>
                        </label>

                        <input
                            type="password"
                            name="senha"
                            <?= $u_edit ? '' : 'required' ?>
                            minlength="8"
                            maxlength="72"
                            autocomplete="new-password"
                            class="w-full bg-slate-50 border-none p-3 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-bold"
                        >

                        <p class="text-[10px] text-slate-400 mt-2 ml-2">
                            Mínimo de 8 caracteres.
                        </p>

                    </div>

                    <div>

                        <label class="text-[10px] font-black uppercase text-slate-400 ml-2">
                            Nível de Acesso
                        </label>

                        <select
                            name="nivel"
                            required
                            class="w-full bg-slate-50 border-none p-3 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-black text-slate-700 uppercase italic"
                        >

                            <option
                                value="colaborador"
                                <?= ($u_edit['nivel'] ?? 'colaborador') === 'colaborador' ? 'selected' : '' ?>
                            >
                                Colaborador
                            </option>

                            <option
                                value="admin"
                                <?= ($u_edit['nivel'] ?? '') === 'admin' ? 'selected' : '' ?>
                            >
                                Administrador
                            </option>

                        </select>

                    </div>

                    <button
                        type="submit"
                        class="w-full bg-slate-900 text-white p-4 rounded-xl font-black uppercase tracking-widest hover:bg-blue-600 transition-all shadow-lg shadow-slate-200"
                    >
                        <?= $u_edit ? 'Salvar Alterações' : 'Cadastrar Agora' ?>
                    </button>

                    <?php if ($u_edit): ?>

                        <a
                            href="usuarios.php"
                            class="block text-center text-[10px] font-black uppercase text-slate-400 hover:text-slate-600 mt-4 tracking-widest"
                        >
                            Cancelar Edição
                        </a>

                    <?php endif; ?>

                </form>

            </div>

            <!-- LISTA DE USUÁRIOS -->

            <div class="lg:col-span-2 bg-white p-6 md:p-8 rounded-[2.5rem] shadow-sm border border-slate-100">

                <h2 class="text-lg font-black mb-6 uppercase italic text-slate-800">
                    Equipe Ativa
                </h2>

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

                            <?php while ($user = $usuarios->fetch_assoc()): ?>

                                <tr class="group">

                                    <td class="px-4 py-4 bg-slate-50 rounded-l-2xl">

                                        <div class="font-black text-slate-700 italic">
                                            <?= escapar($user['nome']) ?>
                                        </div>

                                        <div class="text-[10px] text-slate-400 font-bold">
                                            <?= escapar($user['email']) ?>
                                        </div>

                                    </td>

                                    <td class="px-4 py-4 bg-slate-50 border-x border-white text-center">

                                        <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase <?= $user['nivel'] === 'admin' ? 'bg-blue-100 text-blue-600 border border-blue-200' : 'bg-slate-200 text-slate-600 border border-slate-300' ?>">
                                            <?= escapar($user['nivel']) ?>
                                        </span>

                                    </td>

                                    <td class="px-4 py-4 bg-slate-50 rounded-r-2xl text-right">

                                        <div class="flex justify-end gap-2">

                                            <a
                                                href="usuarios.php?editar=<?= (int) $user['idUsuario'] ?>"
                                                title="Editar usuário"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg bg-white text-blue-500 hover:bg-blue-500 hover:text-white transition shadow-sm border border-slate-100"
                                            >
                                                <i class="fas fa-edit text-xs"></i>
                                            </a>

                                            <?php if ((int) $user['idUsuario'] !== (int) $_SESSION['usuario_idUsuario']): ?>

                                                <form
                                                    method="POST"
                                                    action="usuarios.php"
                                                    onsubmit="return confirm('Tem certeza de que deseja remover este usuário?');"
                                                >

                                                    <input type="hidden" name="acao" value="excluir">

                                                    <input
                                                        type="hidden"
                                                        name="csrf_token"
                                                        value="<?= escapar($_SESSION['csrf_usuarios']) ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="id_usuario"
                                                        value="<?= (int) $user['idUsuario'] ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        title="Excluir usuário"
                                                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-white text-red-400 hover:bg-red-500 hover:text-white transition shadow-sm border border-slate-100"
                                                    >
                                                        <i class="fas fa-trash text-xs"></i>
                                                    </button>

                                                </form>

                                            <?php endif; ?>

                                        </div>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        </tbody>

                    </table>

                </div>

                <?php if ($usuarios->num_rows === 0): ?>

                    <p class="text-center text-slate-400 py-8">
                        Nenhum usuário cadastrado.
                    </p>

                <?php endif; ?>

            </div>

        </div>

    </main>

</body>
</html>