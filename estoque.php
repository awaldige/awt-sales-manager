<?php

require_once 'auth.php';
require_once 'config.php';

// Escape seguro para conteúdo HTML.
function escapar($valor): string
{
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

// Gera o token CSRF da sessão.
if (empty($_SESSION['csrf_estoque'])) {
    $_SESSION['csrf_estoque'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_estoque'];

// Mensagens permitidas para retorno dos ajustes.
$mensagens = [
    'estoque_atualizado' => [
        'texto' => 'Estoque atualizado com sucesso!',
        'tipo' => 'sucesso'
    ],
    'estoque_zerado' => [
        'texto' => 'Não há unidades disponíveis para retirar.',
        'tipo' => 'erro'
    ],
    'produto_nao_encontrado' => [
        'texto' => 'Produto não encontrado.',
        'tipo' => 'erro'
    ],
    'csrf' => [
        'texto' => 'Sessão expirada ou solicitação inválida. Atualize a página.',
        'tipo' => 'erro'
    ],
    'dados_invalidos' => [
        'texto' => 'Dados da operação inválidos.',
        'tipo' => 'erro'
    ],
    'solicitacao_invalida' => [
        'texto' => 'Solicitação inválida.',
        'tipo' => 'erro'
    ],
    'erro_estoque' => [
        'texto' => 'Não foi possível atualizar o estoque.',
        'tipo' => 'erro'
    ]
];

$resultado = $_GET['resultado'] ?? '';
$mensagem = null;

if (is_string($resultado) && isset($mensagens[$resultado])) {
    $mensagem = $mensagens[$resultado];
}

// Busca os produtos.
$res = false;
$erroConsulta = false;

try {
    $sql = "SELECT idProduto, Descricao, Categoria, estoque, Valor
            FROM produtos
            ORDER BY estoque ASC, Descricao ASC";

    $res = $conn->query($sql);

} catch (Throwable $e) {
    error_log('Erro ao consultar estoque: ' . $e->getMessage());
    $erroConsulta = true;
}

$nomeUsuario = $_SESSION['usuario_nome'] ?? 'Usuário';
$nivelUsuario = $_SESSION['usuario_nivel'] ?? '';
$primeiraLetra = mb_strtoupper(mb_substr($nomeUsuario, 0, 1, 'UTF-8'), 'UTF-8');

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Controle de Estoque | ERP PRO</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    >

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        @media print {
            body {
                background: #ffffff !important;
            }

            aside,
            .no-print {
                display: none !important;
            }

            main {
                padding: 0 !important;
                width: 100% !important;
            }

            .print-container {
                border: 0 !important;
                box-shadow: none !important;
                overflow: visible !important;
            }

            table {
                width: 100% !important;
            }

            thead {
                display: table-header-group;
            }

            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body class="bg-slate-50 flex min-h-screen">

    <!-- BARRA LATERAL -->
    <aside class="w-64 bg-slate-900 min-h-screen p-6 text-white hidden lg:flex flex-col sticky top-0 h-screen">

        <div class="text-2xl font-black mb-10 flex items-center gap-2 italic">
            <i class="fas fa-rocket text-blue-500"></i>
            ERP <span class="text-blue-500">PRO</span>
        </div>

        <nav class="space-y-2 flex-1">

            <a href="index.php"
               class="flex items-center gap-3 p-3 hover:bg-slate-800 rounded-xl transition text-slate-400 hover:text-white font-medium">
                <i class="fas fa-chart-pie w-5"></i>
                Dashboard
            </a>

            <a href="estoque.php"
               class="flex items-center gap-3 p-3 bg-blue-600 rounded-xl font-bold shadow-lg shadow-blue-900/20">
                <i class="fas fa-boxes w-5"></i>
                Estoque Atual
            </a>

            <a href="produtos.php"
               class="flex items-center gap-3 p-3 hover:bg-slate-800 rounded-xl transition text-slate-400 hover:text-white font-medium">
                <i class="fas fa-box w-5"></i>
                Cadastrar Produtos
            </a>

            <a href="clientes.php"
               class="flex items-center gap-3 p-3 hover:bg-slate-800 rounded-xl transition text-slate-400 hover:text-white font-medium">
                <i class="fas fa-users w-5"></i>
                Clientes
            </a>

            <?php if ($nivelUsuario === 'admin'): ?>

                <div class="pt-4 pb-2 text-[10px] font-black uppercase text-slate-500 tracking-widest ml-3">
                    Administração
                </div>

                <a href="usuarios.php"
                   class="flex items-center gap-3 p-3 hover:bg-slate-800 rounded-xl transition text-slate-400 hover:text-white font-medium">
                    <i class="fas fa-user-shield w-5"></i>
                    Gestão de Equipe
                </a>

            <?php endif; ?>

        </nav>

        <div class="mt-auto pt-6 border-t border-slate-800">

            <div class="flex items-center gap-3 mb-4">

                <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center font-black text-white shadow-lg italic">
                    <?= escapar($primeiraLetra) ?>
                </div>

                <div class="truncate">
                    <p class="text-sm font-bold leading-none truncate">
                        <?= escapar($nomeUsuario) ?>
                    </p>

                    <p class="text-[9px] text-blue-400 uppercase font-black tracking-widest mt-1 italic">
                        <?= escapar($nivelUsuario) ?>
                    </p>
                </div>

            </div>

            <a href="logout.php"
               class="flex items-center gap-3 p-3 text-red-400 hover:bg-red-500/10 rounded-xl transition font-bold text-xs uppercase tracking-tighter">
                <i class="fas fa-sign-out-alt w-4"></i>
                Sair
            </a>

        </div>

    </aside>

    <!-- CONTEÚDO PRINCIPAL -->
    <main class="flex-1 min-w-0 p-4 sm:p-6 lg:p-8">

        <header class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-5 mb-8 lg:mb-10">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">

            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight leading-tight">
                    Inventário
                </h1>

                <p class="text-slate-500 text-sm font-medium mt-2">
                    Status em tempo real das mercadorias
                </p>
            </div>

            <button
                type="button"
                onclick="window.print()"
                class="no-print self-start sm:self-auto p-3 px-5 bg-white border border-slate-200 rounded-xl font-bold text-slate-600 hover:bg-slate-100 hover:border-slate-300 transition text-xs uppercase shadow-sm"
            >
                <i class="fas fa-print mr-2"></i>
                Imprimir Relatório
            </button>

        </header>

        <!-- MENSAGENS -->
        <?php if ($mensagem): ?>

            <div class="mb-6 p-4 rounded-xl border flex items-center gap-3
                <?= $mensagem['tipo'] === 'sucesso'
                    ? 'bg-emerald-50 border-emerald-200 text-emerald-700'
                    : 'bg-red-50 border-red-200 text-red-700' ?>">

                <i class="fas <?= $mensagem['tipo'] === 'sucesso'
                    ? 'fa-check-circle'
                    : 'fa-exclamation-circle' ?>"></i>

                <span class="text-sm font-bold">
                    <?= escapar($mensagem['texto']) ?>
                </span>

            </div>

        <?php endif; ?>

        <?php if ($erroConsulta): ?>

            <div class="mb-6 p-4 rounded-xl border border-red-200 bg-red-50 text-red-700 flex items-center gap-3">
                <i class="fas fa-exclamation-triangle"></i>

                <span class="text-sm font-bold">
                    Não foi possível carregar os produtos. Verifique a conexão com o banco de dados.
                </span>
            </div>

        <?php endif; ?>

        <!-- TABELA DE PRODUTOS -->
        <div class="print-container bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            <div class="overflow-x-auto">

                <table class="w-full min-w-[760px] text-left">

                    <thead class="bg-slate-50 text-slate-500 text-[10px] uppercase font-bold tracking-wider border-b border-slate-200">

                        <tr>
                            <th class="p-5 sm:p-6">
                                Identificação do Produto
                            </th>

                            <th class="p-5 sm:p-6">
                                Categoria
                            </th>

                            <th class="p-5 sm:p-6 text-center">
                                Quantidade
                            </th>

                            <th class="p-5 sm:p-6 text-center">
                                Status
                            </th>

                            <th class="p-5 sm:p-6 text-right no-print">
                                Ajuste Rápido
                            </th>
                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        <?php if ($res && $res->num_rows > 0): ?>

                            <?php while ($row = $res->fetch_assoc()): ?>

                                <?php

                                $qtd = (int) ($row['estoque'] ?? 0);
                                $idProduto = (int) $row['idProduto'];

                                if ($qtd <= 0) {
                                    $status = 'Sem Estoque';
                                    $css = 'bg-red-50 text-red-600 border-red-100';

                                } elseif ($qtd <= 5) {
                                    $status = 'Estoque Baixo';
                                    $css = 'bg-amber-50 text-amber-600 border-amber-100';

                                } else {
                                    $status = 'Normal';
                                    $css = 'bg-emerald-50 text-emerald-600 border-emerald-100';
                                }

                                ?>

                                <tr class="hover:bg-blue-50/40 transition-colors group">

                                    <td class="p-5 sm:p-6">

                                        <span class="font-bold text-slate-800 block uppercase tracking-wide">
                                            <?= escapar($row['Descricao'] ?? '') ?>
                                        </span>

                                        <span class="text-[10px] text-blue-600 font-bold uppercase tracking-wide mt-1">
                                            SKU: #<?= $idProduto ?>
                                        </span>

                                    </td>

                                    <td class="p-5 sm:p-6">

                                        <span class="inline-block px-3 py-1 bg-slate-100 text-slate-500 text-[10px] font-black uppercase rounded-lg border border-slate-200">

                                            <i class="fas fa-tag mr-1 text-[8px] text-slate-400"></i>

                                            <?= escapar(
                                                !empty($row['Categoria'])
                                                    ? $row['Categoria']
                                                    : 'Geral'
                                            ) ?>

                                        </span>

                                    </td>

                                    <td class="p-5 sm:p-6 text-center">

                                        <span class="text-xl font-extrabold text-slate-900 tabular-nums">
                                            <?= $qtd ?>
                                        </span>

                                        <span class="text-[10px] text-slate-500 block uppercase font-semibold mt-1">
                                            Unidades
                                        </span>

                                    </td>

                                    <td class="p-5 sm:p-6 text-center">

                                        <span class="inline-block px-3 py-2 rounded-lg text-[10px] font-bold uppercase border <?= $css ?>">
                                            <?= escapar($status) ?>
                                        </span>

                                    </td>

                                    <td class="p-5 sm:p-6 text-right no-print">

                                        <div class="flex justify-end gap-2 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 focus-within:opacity-100 transition-opacity">

                                            <!-- ADICIONAR ESTOQUE -->
                                            <form method="POST" action="ajuste_estoque.php">

                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= escapar($csrfToken) ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= $idProduto ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="acao"
                                                    value="add"
                                                >

                                                <button
                                                    type="submit"
                                                    class="w-10 h-10 flex items-center justify-center bg-white border border-slate-200 text-emerald-600 rounded-xl hover:bg-emerald-600 hover:text-white hover:border-emerald-600 transition shadow-sm"
                                                    title="Adicionar entrada ao estoque"
                                                    aria-label="Adicionar uma unidade ao estoque"
                                                >
                                                    <i class="fas fa-plus text-xs"></i>
                                                </button>

                                            </form>

                                            <!-- RETIRAR ESTOQUE -->
                                            <form method="POST" action="ajuste_estoque.php">

                                                <input
                                                    type="hidden"
                                                    name="csrf_token"
                                                    value="<?= escapar($csrfToken) ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?= $idProduto ?>"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="acao"
                                                    value="sub"
                                                >

                                                <button
                                                    type="submit"
                                                    class="w-10 h-10 flex items-center justify-center bg-white border border-slate-200 text-rose-500 rounded-xl hover:bg-rose-500 hover:text-white hover:border-rose-500 transition shadow-sm"
                                                    title="Registrar saída do estoque"
                                                    aria-label="Retirar uma unidade do estoque"
                                                >
                                                    <i class="fas fa-minus text-xs"></i>
                                                </button>

                                            </form>

                                        </div>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>
                                <td colspan="5" class="p-10 text-center text-slate-400">

                                    <i class="fas fa-box-open text-3xl mb-3 block"></i>

                                    <span class="font-bold text-sm">
                                        <?= $erroConsulta
                                            ? 'Não foi possível consultar os produtos.'
                                            : 'Nenhum produto cadastrado.' ?>
                                    </span>

                                </td>
                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

        <p class="text-xs text-slate-500 mt-4 flex items-center gap-1">
            <i class="fas fa-info-circle mr-1"></i>
            Os ajustes rápidos alteram uma unidade por operação.
        </p>

    </main>

</body>
</html>