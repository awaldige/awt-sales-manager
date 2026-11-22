<?php
include 'auth.php';
include 'config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function escapar($valor): string
{
    return htmlspecialchars((string)($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

if (empty($_SESSION['csrf_editar_produto'])) {
    $_SESSION['csrf_editar_produto'] = bin2hex(random_bytes(32));
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header('Location: produtos.php');
    exit();
}

$erro = '';

$stmt = $conn->prepare(
    "SELECT idProduto, Descricao, Valor, idFornecedor, precoVenda, idCategoria
     FROM produtos
     WHERE idProduto = ?"
);
$stmt->bind_param('i', $id);
$stmt->execute();
$produto = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$produto) {
    header('Location: produtos.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $descricao = trim($_POST['Descricao'] ?? '');
    $custoTexto = trim($_POST['valor_custo'] ?? '');
    $fornecedorTexto = $_POST['idFornecedor'] ?? '';
    $precoTexto = trim($_POST['precoVenda'] ?? '');
    $categoriaTexto = $_POST['idCategoria'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_editar_produto'], $token)
    ) {
        $erro = 'Sessão de segurança inválida. Atualize a página e tente novamente.';
    } elseif ($descricao === '' || strlen($descricao) > 255) {
        $erro = 'Informe uma descrição válida de até 255 caracteres.';
    } else {
        $custoNormalizado = str_replace(',', '.', $custoTexto);
        $precoNormalizado = str_replace(',', '.', $precoTexto);

        if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $custoNormalizado)) {
            $erro = 'Informe um custo unitário válido, com até duas casas decimais.';
        } elseif (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $precoNormalizado)) {
            $erro = 'Informe um preço de venda válido, com até duas casas decimais.';
        } else {
            $custo = (float)$custoNormalizado;
            $preco = (float)$precoNormalizado;
            $categoriaId = filter_var($categoriaTexto, FILTER_VALIDATE_INT);
            $fornecedorId = null;

            if ($fornecedorTexto !== '') {
                $fornecedorId = filter_var($fornecedorTexto, FILTER_VALIDATE_INT);

                if (!$fornecedorId || $fornecedorId <= 0) {
                    $erro = 'Selecione um fornecedor válido.';
                }
            }

            if ($erro === '' && (!$categoriaId || $categoriaId <= 0)) {
                $erro = 'Selecione uma categoria válida.';
            }

            if ($erro === '') {
                try {
                    $stmt = $conn->prepare(
                        "SELECT idCategoria FROM categoria WHERE idCategoria = ?"
                    );
                    $stmt->bind_param('i', $categoriaId);
                    $stmt->execute();
                    $categoriaExiste = $stmt->get_result()->num_rows > 0;
                    $stmt->close();

                    if (!$categoriaExiste) {
                        $erro = 'A categoria selecionada não foi encontrada.';
                    }

                    if ($erro === '' && $fornecedorId !== null) {
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

                    if ($erro === '') {
                        $stmt = $conn->prepare(
                            "UPDATE produtos
                             SET Descricao = ?, Valor = ?, idFornecedor = ?,
                                 precoVenda = ?, idCategoria = ?
                             WHERE idProduto = ?"
                        );

                        $stmt->bind_param(
                            'sdidii',
                            $descricao,
                            $custo,
                            $fornecedorId,
                            $preco,
                            $categoriaId,
                            $id
                        );

                        $stmt->execute();
                        $stmt->close();

                        header('Location: produtos.php');
                        exit();
                    }
                } catch (Throwable $e) {
                    error_log('Erro ao editar produto: ' . $e->getMessage());
                    $erro = 'Não foi possível salvar as alterações. Tente novamente.';
                }
            }
        }
    }

    if ($erro !== '') {
        $produto['Descricao'] = $descricao;
        $produto['Valor'] = $custoTexto;
        $produto['idFornecedor'] = $fornecedorTexto;
        $produto['precoVenda'] = $precoTexto;
        $produto['idCategoria'] = $categoriaTexto;
    }
}
$categorias = $conn->query(
    "SELECT idCategoria, nomeCategoria
     FROM categoria
     ORDER BY nomeCategoria ASC"
);

$fornecedores = $conn->query(
    "SELECT idFornecedor, RazaoSocial
     FROM fornecedor
     ORDER BY RazaoSocial ASC"
);

if (!$fornecedores) {
    error_log('Erro ao carregar fornecedores: ' . $conn->error);
    http_response_code(500);
    exit('Não foi possível carregar os fornecedores.');
}

if (!$categorias) {
    error_log('Erro ao carregar categorias: ' . $conn->error);
    http_response_code(500);
    exit('Não foi possível carregar as categorias.');
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Produto</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-4 sm:p-6">

    <div class="max-w-xl w-full bg-white p-6 sm:p-9 rounded-2xl shadow-sm border border-slate-200">
        <h2 class="text-2xl sm:text-3xl font-black text-slate-800 mb-2 text-center tracking-tight">
            Editar Registro
        </h2>

        <?php if ($erro !== ''): ?>
            <div class="mb-5 p-4 rounded-2xl border border-red-200 bg-red-50 text-red-700 text-sm font-semibold">
                <?= escapar($erro) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5">
            <input
                type="hidden"
                name="csrf_token"
                value="<?= escapar($_SESSION['csrf_editar_produto']) ?>">

            <div>
                <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">
                    Descrição do Produto
                </label>
                <input
                    type="text"
                    name="Descricao"
                    maxlength="255"
                    value="<?= escapar($produto['Descricao']) ?>"
                    required
                    class="w-full p-4 bg-slate-50 border border-slate-200 focus:border-blue-500 focus:bg-white rounded-xl outline-none font-semibold text-slate-800 transition">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">
                        Custo Unitário (R$)
                    </label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        max="99999999.99"
                        name="valor_custo"
                        value="<?= escapar($produto['Valor']) ?>"
                        required
                        class="w-full p-4 bg-slate-50 border border-slate-200 focus:border-blue-500 rounded-xl outline-none font-semibold text-slate-800 transition">
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">
                        Fornecedor Principal
                    </label>
                    <select
                        name="idFornecedor"
                        class="w-full p-4 bg-slate-50 border border-slate-200 focus:border-blue-500 rounded-xl outline-none font-semibold text-slate-800 transition">
                        <option value="">Sem fornecedor</option>
                        <?php while ($f = $fornecedores->fetch_assoc()): ?>
                            <option
                                value="<?= (int)$f['idFornecedor'] ?>"
                                <?= (string)$f['idFornecedor'] === (string)($produto['idFornecedor'] ?? '') ? 'selected' : '' ?>>
                                <?= escapar($f['RazaoSocial']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">
                        Preço Venda
                    </label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        max="99999999.99"
                        name="precoVenda"
                        value="<?= escapar($produto['precoVenda']) ?>"
                        required
                        class="w-full p-4 bg-blue-50 border border-blue-100 focus:border-blue-500 rounded-xl outline-none font-black text-blue-700 text-lg transition">
                </div>

                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-2">
                        Categoria
                    </label>
                    <select
                        name="idCategoria"
                        required
                        class="w-full p-4 bg-slate-50 border border-slate-200 focus:border-blue-500 focus:bg-white rounded-xl outline-none font-semibold text-slate-800 transition">
                        <option value="">Selecione...</option>
                        <?php while ($c = $categorias->fetch_assoc()): ?>
                            <option
                                value="<?= (int)$c['idCategoria'] ?>"
                                <?= (string)$c['idCategoria'] === (string)$produto['idCategoria'] ? 'selected' : '' ?>>
                                <?= escapar($c['nomeCategoria']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>

            <button
                type="submit"
                class="w-full bg-blue-600 text-white p-4 rounded-xl font-bold text-base hover:bg-blue-700 hover:shadow-md transition">
                Salvar Alterações
            </button>

            <a
                href="produtos.php"
                class="block text-center text-slate-500 font-semibold text-sm mt-3 hover:text-slate-800 transition">
                Cancelar
            </a>
        </form>
    </div>

</body>
</html>