<?php
include 'auth.php';
include 'config.php';

// 1. Processar Cadastro de Nova Forma
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['novo_tipo'])) {
    $tipo = trim(mysqli_real_escape_string($conn, $_POST['novo_tipo']));
    
    if (!empty($tipo)) {
        // Busca se já existe para evitar duplicados
        $check = $conn->query("SELECT idFormaPagamento FROM formapagamento WHERE Tipo = '$tipo'");
        
        if ($check->num_rows == 0) {
            $stmt = $conn->prepare("INSERT INTO formapagamento (Tipo) VALUES (?)");
            $stmt->bind_param("s", $tipo);
            if($stmt->execute()){
                header("Location: formas_pagamento.php?sucesso=1");
            } else {
                die("Erro ao inserir: " . $conn->error);
            }
        } else {
            header("Location: formas_pagamento.php?erro=duplicado");
        }
    }
    exit();
}

// 2. Busca as formas cadastradas
$res = $conn->query("SELECT * FROM formapagamento ORDER BY idFormaPagamento DESC");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formas de pagamento | AWT Sales Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="min-h-screen bg-slate-100 text-slate-800">
<div class="flex min-h-screen">
    <aside class="hidden w-64 shrink-0 flex-col bg-slate-950 text-white lg:flex">
        <div class="border-b border-white/10 px-6 py-7">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-600 text-lg font-extrabold">AW</div>
                <div>
                    <p class="text-sm font-extrabold tracking-wide">AW TECHNOLOGY</p>
                    <p class="mt-1 text-xs text-slate-400">Sales Manager</p>
                </div>
            </div>
        </div>
        <nav class="flex-1 space-y-2 px-4 py-6 text-sm font-semibold">
            <a href="index.php" class="flex items-center gap-3 rounded-xl px-4 py-3 text-slate-400 transition hover:bg-slate-800 hover:text-white">
                <i class="fa-solid fa-chart-pie w-5"></i> Dashboard
            </a>
            <a href="formas_pagamento.php" class="flex items-center gap-3 rounded-xl bg-blue-600 px-4 py-3 text-white">
                <i class="fa-solid fa-credit-card w-5"></i> Formas de pagamento
            </a>
            <a href="compra.php" class="flex items-center gap-3 rounded-xl px-4 py-3 text-slate-400 transition hover:bg-slate-800 hover:text-white">
                <i class="fa-solid fa-cart-shopping w-5"></i> Compras
            </a>
        </nav>
        <div class="border-t border-white/10 px-6 py-5">
            <p class="text-xs text-slate-500">AW TECHNOLOGY</p>
            <p class="mt-1 text-xs font-semibold text-slate-400">Gestão inteligente</p>
        </div>
    </aside>

    <main class="min-w-0 flex-1">
        <header class="border-b border-slate-200 bg-white px-5 py-5 sm:px-8">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
            <div class="mx-auto flex max-w-7xl flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="mb-1 flex items-center gap-2 text-xs font-semibold text-slate-400">
                        <a href="index.php" class="hover:text-blue-600">Dashboard</a>
                        <i class="fa-solid fa-chevron-right text-[9px]"></i>
                        <span class="text-blue-600">Financeiro</span>
                    </div>
                    <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Formas de pagamento</h1>
                    <p class="mt-1 text-sm text-slate-500">Gerencie os métodos de recebimento utilizados no PDV.</p>
                </div>
                <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-xs font-semibold text-slate-500">
                    <i class="fa-solid fa-credit-card text-blue-600"></i> Configurações financeiras
                </div>
            </div>
        </header>

        <div class="mx-auto max-w-7xl space-y-6 px-5 py-7 sm:px-8 sm:py-9">
            <?php if (isset($_GET['sucesso'])): ?>
                <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">
                    <i class="fa-solid fa-circle-check text-lg"></i> Forma de pagamento cadastrada com sucesso.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['erro']) && $_GET['erro'] === 'duplicado'): ?>
                <div class="flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm font-semibold text-amber-700">
                    <i class="fa-solid fa-triangle-exclamation text-lg"></i> Essa forma de pagamento já está cadastrada.
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
                <section class="xl:col-span-4">
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 px-6 py-5">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                                    <i class="fa-solid fa-plus"></i>
                                </div>
                                <div>
                                    <h2 class="font-bold text-slate-900">Novo método</h2>
                                    <p class="mt-1 text-xs text-slate-500">Adicione uma forma de pagamento</p>
                                </div>
                            </div>
                        </div>
                        <form action="" method="POST" class="space-y-5 p-6">
                            <div>
                                <label for="novo_tipo" class="mb-2 block text-sm font-semibold text-slate-700">Nome do pagamento</label>
                                <input type="text" id="novo_tipo" name="novo_tipo" required maxlength="100"
                                    placeholder="Ex.: Dinheiro, Pix, Cartão..."
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                                <p class="mt-2 text-xs text-slate-400">Informe o nome que será exibido no PDV.</p>
                            </div>
                            <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3.5 text-sm font-bold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200">
                                <i class="fa-solid fa-plus"></i> Cadastrar método
                            </button>
                        </form>
                    </div>
                    <div class="mt-4 rounded-2xl border border-blue-100 bg-blue-50 p-5">
                        <div class="flex gap-3">
                            <i class="fa-solid fa-circle-info mt-0.5 text-blue-600"></i>
                            <div>
                                <p class="text-sm font-bold text-blue-900">Dica</p>
                                <p class="mt-1 text-xs leading-relaxed text-blue-800">Cadastre nomes claros para facilitar a identificação das formas de pagamento durante as vendas.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="min-w-0 xl:col-span-8">
                    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <div class="flex flex-col gap-2 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="font-bold text-slate-900">Métodos cadastrados</h2>
                                <p class="mt-1 text-xs text-slate-500">Formas de recebimento disponíveis no sistema.</p>
                            </div>
                            <span class="inline-flex w-fit items-center gap-2 rounded-lg bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span> Cadastro ativo
                            </span>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[600px] text-left">
                                <thead>
                                    <tr class="border-b border-slate-100 bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                                        <th class="px-6 py-4">ID</th>
                                        <th class="px-6 py-4">Forma de pagamento</th>
                                        <th class="px-6 py-4 text-center">Status</th>
                                        <th class="px-6 py-4 text-center">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php if ($res && $res->num_rows > 0): ?>
                                        <?php while ($f = $res->fetch_assoc()):
                                            $nome = $f['Tipo'];
                                            $cor = '#64748b';
                                            $bg = '#f1f5f9';
                                            $icon = 'fa-wallet';
                                            if (stripos($nome, 'Pix') !== false) {
                                                $cor = '#0891b2'; $bg = '#ecfeff'; $icon = 'fa-bolt';
                                            } elseif (stripos($nome, 'Dinheiro') !== false) {
                                                $cor = '#16a34a'; $bg = '#f0fdf4'; $icon = 'fa-money-bill-wave';
                                            } elseif (stripos($nome, 'Cartao') !== false || stripos($nome, 'Cartão') !== false) {
                                                $cor = '#4f46e5'; $bg = '#eef2ff'; $icon = 'fa-credit-card';
                                            } elseif (stripos($nome, 'Boleto') !== false) {
                                                $cor = '#d97706'; $bg = '#fffbeb'; $icon = 'fa-barcode';
                                            }
                                        ?>
                                            <tr class="transition hover:bg-slate-50">
                                                <td class="whitespace-nowrap px-6 py-4">
                                                    <span class="rounded-lg bg-slate-100 px-2.5 py-1.5 text-xs font-bold text-slate-500">#<?= (int)$f['idFormaPagamento'] ?></span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <div class="flex items-center gap-3">
                                                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl" style="background-color: <?= $bg ?>; color: <?= $cor ?>;">
                                                            <i class="fa-solid <?= $icon ?> text-lg"></i>
                                                        </div>
                                                        <span class="text-sm font-bold text-slate-700"><?= htmlspecialchars($nome, ENT_QUOTES, 'UTF-8') ?></span>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Ativo
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 text-center">
                                                    <a href="deletar_forma.php?id=<?= (int)$f['idFormaPagamento'] ?>"
                                                        onclick="return confirm('Deseja realmente excluir esta forma de pagamento?')"
                                                        title="Excluir forma de pagamento" aria-label="Excluir forma de pagamento"
                                                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-600">
                                                        <i class="fa-solid fa-trash-can text-sm"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="px-6 py-14 text-center">
                                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                                    <i class="fa-solid fa-credit-card text-xl"></i>
                                                </div>
                                                <p class="mt-4 text-sm font-bold text-slate-700">Nenhuma forma cadastrada</p>
                                                <p class="mt-1 text-xs text-slate-400">Utilize o formulário para adicionar o primeiro método.</p>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>
</div>
</body>
</html>