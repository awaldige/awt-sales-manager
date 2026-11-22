<?php
include 'auth.php';
include 'config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['csrf_cadastro_produto'])) {
    $_SESSION['csrf_cadastro_produto'] = bin2hex(random_bytes(32));
}

function escapar($valor): string
{
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

$erro = '';
$descricao = '';
$categoriaTexto = '';
$valorCusto = '';
$precoVenda = '';
$idFornecedor = '';
$idCategoria = '';
$estoqueInicial = '0';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descricao = trim($_POST['descricao'] ?? '');
    $categoriaTexto = trim($_POST['categoria_texto'] ?? '');
    $valorCusto = trim($_POST['valor_custo'] ?? '');
    $precoVenda = trim($_POST['preco_venda'] ?? '');
    $idFornecedor = $_POST['idFornecedor'] ?? '';
    $idCategoria = $_POST['idCategoria'] ?? '';
    $estoqueInicial = trim($_POST['estoque_inicial'] ?? '0');

    $token = $_POST['csrf_token'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_cadastro_produto'], $token)
    ) {
        $erro = 'Sessão de segurança inválida. Atualize a página e tente novamente.';
    } elseif ($descricao === '' || strlen($descricao) > 255) {
        $erro = 'Informe uma descrição válida de até 255 caracteres.';
    } else {
        $custoNormalizado = str_replace(',', '.', $valorCusto);
        $vendaNormalizada = str_replace(',', '.', $precoVenda);

        if (
            !preg_match('/^\d{1,8}(\.\d{1,2})?$/', $custoNormalizado) ||
            !preg_match('/^\d{1,8}(\.\d{1,2})?$/', $vendaNormalizada)
        ) {
            $erro = 'Informe valores de custo e venda válidos, com até duas casas decimais.';
        } elseif (
            !preg_match('/^\d+$/', $estoqueInicial) ||
            (int)$estoqueInicial < 0
        ) {
            $erro = 'Informe uma quantidade de estoque válida.';
        } elseif (
            $categoriaTexto !== '' &&
            strlen($categoriaTexto) > 45
        ) {
            $erro = 'A categoria deve ter no máximo 45 caracteres.';
        } else {
            $custo = (float)$custoNormalizado;
            $venda = (float)$vendaNormalizada;
            $estoque = (int)$estoqueInicial;

            $fornecedorId = null;
            $categoriaId = null;

            if ($idFornecedor !== '') {
                $fornecedorId = filter_var($idFornecedor, FILTER_VALIDATE_INT);

                if (!$fornecedorId || $fornecedorId <= 0) {
                    $erro = 'Selecione um fornecedor válido.';
                } else {
                    $stmt = $conn->prepare(
                        "SELECT idFornecedor FROM fornecedor WHERE idFornecedor = ?"
                    );
                    $stmt->bind_param('i', $fornecedorId);
                    $stmt->execute();
                    $fornecedorExiste = $stmt->get_result()->num_rows > 0;
                    $stmt->close();

                    if (!$fornecedorExiste) {
                        $erro = 'O fornecedor selecionado não foi encontrado.';
                    }
                }
            }

            if ($erro === '' && $idCategoria !== '') {
                $categoriaId = filter_var($idCategoria, FILTER_VALIDATE_INT);

                if (!$categoriaId || $categoriaId <= 0) {
                    $erro = 'Selecione uma categoria válida.';
                } else {
                    $stmt = $conn->prepare(
                        "SELECT nomeCategoria FROM categoria WHERE idCategoria = ?"
                    );
                    $stmt->bind_param('i', $categoriaId);
                    $stmt->execute();
                    $categoriaEncontrada = $stmt->get_result()->fetch_assoc();
                    $stmt->close();

                    if (!$categoriaEncontrada) {
                        $erro = 'A categoria selecionada não foi encontrada.';
                    } elseif ($categoriaTexto === '') {
                        $categoriaTexto = $categoriaEncontrada['nomeCategoria'];
                    }
                }
            }

            if ($erro === '' && $categoriaTexto === '') {
                $erro = 'Informe uma categoria ou selecione uma categoria cadastrada.';
            }

            if ($erro === '' && strlen($categoriaTexto) > 45) {
                $erro = 'A categoria deve ter no máximo 45 caracteres.';
            }

            if ($erro === '') {
                try {
                    $stmt = $conn->prepare(
                        "INSERT INTO produtos
                            (Categoria, Descricao, Valor, idFornecedor, idCategoria, precoVenda, estoque)
                         VALUES (?, ?, ?, ?, ?, ?, ?)"
                    );

                    $stmt->bind_param(
                        'ssdiidi',
                        $categoriaTexto,
                        $descricao,
                        $custo,
                        $fornecedorId,
                        $categoriaId,
                        $venda,
                        $estoque
                    );

                    $stmt->execute();
                    $stmt->close();

                    header('Location: produtos.php?status=cadastrado');
                    exit();

                } catch (Throwable $e) {
                    error_log('Erro ao cadastrar produto: ' . $e->getMessage());
                    $erro = 'Não foi possível cadastrar o produto. Verifique os dados e tente novamente.';
                }
            }
        }
    }
}

$fornecedores = $conn->query(
    "SELECT idFornecedor, RazaoSocial FROM fornecedor ORDER BY RazaoSocial ASC"
);

$categoriasDb = $conn->query(
    "SELECT idCategoria, nomeCategoria FROM categoria ORDER BY nomeCategoria ASC"
);

if (!$fornecedores || !$categoriasDb) {
    error_log('Erro ao carregar opções do cadastro de produtos: ' . $conn->error);
    http_response_code(500);
    exit('Não foi possível carregar os dados do formulário.');
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Produto | ERP PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-slate-50 p-4 sm:p-6 lg:p-10 font-['Inter']">

    <div class="max-w-4xl mx-auto bg-white p-5 sm:p-8 lg:p-10 rounded-2xl shadow-sm border border-slate-200">
        <header class="mb-8 flex justify-between items-center gap-4">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 uppercase tracking-tight">Novo Produto</h1>
                <p class="text-slate-400 font-bold text-xs uppercase italic">Cadastro técnico de inventário</p>
            </div>
            <a href="produtos.php" class="text-slate-400 hover:text-slate-600 transition" title="Voltar">
                <i class="fas fa-times fa-lg"></i>
            </a>
        </header>

        <?php if ($erro !== ''): ?>
            <div class="mb-6 p-4 rounded-2xl border border-red-200 bg-red-50 text-red-700 text-sm font-semibold">
                <?= escapar($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-8">
            <input type="hidden" name="csrf_token" value="<?= escapar($_SESSION['csrf_cadastro_produto']) ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">

                <div class="md:col-span-2">
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-4 mb-2 block">
                        Descrição do Produto
                    </label>
                    <input
                        type="text"
                        name="descricao"
                        required
                        maxlength="255"
                        value="<?= escapar($descricao) ?>"
                        placeholder="Ex: Cadeira Gamer Black Edition"
                        class="w-full p-4 bg-slate-50 border border-slate-200 focus:border-blue-500 focus:bg-white rounded-xl font-bold text-slate-700 transition outline-none">
                </div>

                <div>
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-4 mb-2 block">
                        Categoria (Nome)
                    </label>
                    <input
                        type="text"
                        name="categoria_texto"
                        maxlength="45"
                        value="<?= escapar($categoriaTexto) ?>"
                        placeholder="Ex: Móveis"
                        class="w-full p-4 bg-slate-50 border border-slate-200 focus:border-blue-500 rounded-xl font-bold text-slate-700 outline-none">
                </div>

                <div>
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-4 mb-2 block">
                        Vincular Categoria
                    </label>
                    <select
                        name="idCategoria"
                        class="w-full p-4 bg-slate-50 border border-slate-200 focus:border-blue-500 rounded-xl font-bold text-slate-700 outline-none appearance-none">
                        <option value="">Selecione...</option>
                        <?php while ($c = $categoriasDb->fetch_assoc()): ?>
                            <option
                                value="<?= (int)$c['idCategoria'] ?>"
                                <?= (string)$idCategoria === (string)$c['idCategoria'] ? 'selected' : '' ?>>
                                <?= escapar($c['nomeCategoria']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div>
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-4 mb-2 block">
                        Custo Unitário (R$)
                    </label>
                    <input
                        type="text"
                        name="valor_custo"
                        required
                        inputmode="decimal"
                        value="<?= escapar($valorCusto) ?>"
                        placeholder="0,00"
                        class="w-full p-4 bg-slate-50 border border-slate-200 focus:border-blue-500 rounded-xl font-black text-slate-800 outline-none text-xl">
                </div>

                <div>
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-4 mb-2 block">
                        Preço de Venda (R$)
                    </label>
                    <input
                        type="text"
                        name="preco_venda"
                        required
                        inputmode="decimal"
                        value="<?= escapar($precoVenda) ?>"
                        placeholder="0,00"
                        class="w-full p-4 bg-blue-50 border border-blue-100 focus:border-blue-500 rounded-xl font-black text-blue-600 outline-none text-xl">
                </div>

                <div>
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-4 mb-2 block">
                        Fornecedor Principal
                    </label>
                    <select
                        name="idFornecedor"
                        class="w-full p-4 bg-slate-50 border border-slate-200 focus:border-blue-500 rounded-xl font-bold text-slate-700 outline-none appearance-none">
                        <option value="">Sem fornecedor</option>
                        <?php while ($f = $fornecedores->fetch_assoc()): ?>
                            <option
                                value="<?= (int)$f['idFornecedor'] ?>"
                                <?= (string)$idFornecedor === (string)$f['idFornecedor'] ? 'selected' : '' ?>>
                                <?= escapar($f['RazaoSocial']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div>
                    <label class="text-[10px] font-black uppercase text-slate-400 ml-4 mb-2 block">
                        Estoque Inicial
                    </label>
                    <input
                        type="number"
                        name="estoque_inicial"
                        min="0"
                        step="1"
                        required
                        value="<?= escapar($estoqueInicial) ?>"
                        class="w-full p-4 bg-slate-50 border border-slate-200 focus:border-blue-500 rounded-xl font-black text-slate-700 text-center outline-none">
                </div>

            </div>

            <div class="pt-6">
                <button
                    type="submit"
                    class="w-full p-5 bg-slate-900 text-white rounded-xl font-black uppercase tracking-[0.2em] hover:bg-blue-600 transition shadow-2xl shadow-blue-100">
                    Finalizar Cadastro
                </button>
            </div>
        </form>
    </div>

</body>
</html>