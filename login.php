
$conteudo = @'
<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once 'config.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $erro = 'E-mail ou senha incorretos!';

    } else {

        $stmt = $conn->prepare(
            "SELECT idUsuario, nome, email, senha, nivel
             FROM usuarios
             WHERE email = ?
             LIMIT 1"
        );

        if ($stmt) {

            $stmt->bind_param('s', $email);
            $stmt->execute();

            $resultado = $stmt->get_result();
            $user = $resultado->fetch_assoc();

            $stmt->close();

            if (
                $user &&
                password_verify($senha, $user['senha']) &&
                in_array($user['nivel'], ['admin', 'colaborador'], true)
            ) {

                session_regenerate_id(true);

                $_SESSION['usuario_idUsuario'] = (int) $user['idUsuario'];
                $_SESSION['usuario_nome'] = $user['nome'];
                $_SESSION['usuario_nivel'] = $user['nivel'];

                header('Location: index.php');
                exit();

            } else {

                $erro = 'E-mail ou senha incorretos!';
            }

        } else {

            error_log('Erro ao preparar consulta de login: ' . $conn->error);
            $erro = 'Não foi possível realizar o login. Tente novamente.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#080f1f">
    <title>Entrar | AWT Sales Manager</title>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background:
                radial-gradient(ellipse at 15% 15%, rgba(37, 99, 235, .20), transparent 38%),
                radial-gradient(ellipse at 85% 85%, rgba(14, 165, 233, .12), transparent 35%),
                #080f1f;
        }

        .login-card {
            box-shadow: 0 30px 100px rgba(0, 0, 0, .35);
        }

        .input-field {
            transition: border-color .2s, box-shadow .2s, background .2s;
        }

        .input-field:focus {
            border-color: #3b82f6;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, .12);
            outline: none;
        }
    </style>
</head>

<body class="min-h-screen flex items-center justify-center p-4 sm:p-6">

    <main class="login-card w-full max-w-5xl overflow-hidden rounded-3xl border border-white/10 bg-white">

        <div class="grid min-h-[620px] md:grid-cols-2">

            <!-- PAINEL INSTITUCIONAL -->
            <section class="relative flex flex-col justify-between overflow-hidden bg-slate-950 p-8 text-white sm:p-12">

                <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-blue-600/20 blur-3xl"></div>
                <div class="absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-sky-500/10 blur-3xl"></div>

                <div class="relative z-10">

                    <div class="mb-14 flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl border border-blue-400/30 bg-blue-500/10 text-xl font-black tracking-tighter">
                            <span class="text-white">A</span><span class="text-sky-400">W</span>
                        </div>

                        <div>
                            <p class="text-sm font-black tracking-[.22em]">AW TECHNOLOGY</p>
                            <p class="text-[10px] font-semibold tracking-[.18em] text-slate-400">SOLUÇÕES EM SOFTWARE</p>
                        </div>
                    </div>

                    <p class="mb-4 text-xs font-bold uppercase tracking-[.25em] text-blue-400">
                        Gestão inteligente
                    </p>

                    <h1 class="max-w-md text-4xl font-black leading-tight sm:text-5xl">
                        Controle seu negócio com
                        <span class="text-blue-400">mais eficiência.</span>
                    </h1>

                    <p class="mt-6 max-w-md text-sm leading-7 text-slate-400">
                        Uma plataforma para organizar vendas, compras, estoque e informações em um só lugar.
                    </p>

                    <div class="mt-10 space-y-5">

                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500/10 text-blue-400">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3v18h18M7 14l4-4 4 4 6-7"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold">Vendas e pedidos</p>
                                <p class="text-xs text-slate-500">Acompanhamento das operações</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500/10 text-blue-400">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold">Compras e estoque</p>
                                <p class="text-xs text-slate-500">Organização de produtos e entradas</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500/10 text-blue-400">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold">Gestão centralizada</p>
                                <p class="text-xs text-slate-500">Informações acessíveis em um só sistema</p>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="relative z-10 mt-12 border-t border-white/10 pt-6">
                    <p class="text-xs text-slate-500">
                        Projeto demonstrativo desenvolvido por
                        <span class="font-bold text-slate-300">AW TECHNOLOGY</span>
                    </p>
                </div>

            </section>

            <!-- FORMULÁRIO DE ACESSO -->
            <section class="flex items-center justify-center bg-white px-7 py-12 sm:px-12">

                <div class="w-full max-w-sm">

                    <div class="mb-10">
                        <div class="mb-5 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 7a3 3 0 11-6 0 3 3 0 016 0zM5 20a7 7 0 0114 0M19 10v6m-3-3h6"/>
                            </svg>
                        </div>

                        <p class="mb-2 text-xs font-bold uppercase tracking-[.2em] text-blue-600">
                            Área restrita
                        </p>

                        <h2 class="text-3xl font-black tracking-tight text-slate-900">
                            Bem-vindo!
                        </h2>

                        <p class="mt-2 text-sm text-slate-500">
                            Entre com suas credenciais para acessar o sistema.
                        </p>
                    </div>

                    <?php if ($erro !== ''): ?>
                        <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                            <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="login.php" class="space-y-6">

                        <div>
                            <label for="email" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-600">
                                E-mail
                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                required
                                autocomplete="username"
                                maxlength="255"
                                placeholder="seu@email.com"
                                value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                class="input-field w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-800 placeholder:text-slate-400"
                            >
                        </div>

                        <div>
                            <label for="senha" class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-600">
                                Senha
                            </label>

                            <input
                                type="password"
                                id="senha"
                                name="senha"
                                required
                                autocomplete="current-password"
                                placeholder="Digite sua senha"
                                class="input-field w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-800 placeholder:text-slate-400"
                            >
                        </div>

                        <button
                            type="submit"
                            class="group flex w-full items-center justify-center gap-3 rounded-xl bg-blue-600 px-5 py-4 text-sm font-black uppercase tracking-wider text-white shadow-lg shadow-blue-600/20 transition hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-blue-600/30 focus:outline-none focus:ring-4 focus:ring-blue-500/20"
                        >
                            Acessar sistema

                            <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-6-6l6 6-6 6"/>
                            </svg>
                        </button>

                    </form>

                    <div class="mt-10 border-t border-slate-100 pt-6 text-center">
                        <p class="text-xs text-slate-400">
                            AWT Sales Manager
                            <span class="mx-1">•</span>
                            Ambiente demonstrativo
                        </p>
                    </div>

                </div>

            </section>

        </div>

    </main>

</body>
</html>
'@

Set-Content -Path .\login.php -Value $conteudo -Encoding UTF8
