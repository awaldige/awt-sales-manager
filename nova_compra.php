<?php
include 'auth.php';
include 'config.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['csrf_nova_compra'])) {
    $_SESSION['csrf_nova_compra'] = bin2hex(random_bytes(32));
}

$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transacaoAtiva = false;
    $arquivoSalvo = null;

    try {
        $token = $_POST['csrf_token'] ?? '';

        if (!is_string($token) || !hash_equals($_SESSION['csrf_nova_compra'], $token)) {
            throw new InvalidArgumentException('Sessão expirada ou solicitação inválida. Atualize a página.');
        }

        $idFornecedor = filter_var($_POST['idFornecedor'] ?? null, FILTER_VALIDATE_INT);
        $idFormaPgto = filter_var($_POST['idFormaPagamento'] ?? null, FILTER_VALIDATE_INT);
        $status = $_POST['status'] ?? '';
        $dataCompra = $_POST['dataCompra'] ?? '';

        if (!$idFornecedor || $idFornecedor < 1 || !$idFormaPgto || $idFormaPgto < 1) {
            throw new InvalidArgumentException('Selecione um fornecedor e uma forma de pagamento válidos.');
        }

        if (!in_array($status, ['Pendente', 'Recebido'], true)) {
            throw new InvalidArgumentException('Status de recebimento inválido.');
        }

        if (!is_string($dataCompra)) {
            throw new InvalidArgumentException('Data da compra inválida.');
        }

        $dataValidada = DateTime::createFromFormat('!Y-m-d', $dataCompra);

        if (!$dataValidada || $dataValidada->format('Y-m-d') !== $dataCompra) {
            throw new InvalidArgumentException('Informe uma data válida.');
        }

        $produtosIds = $_POST['idProduto'] ?? null;
        $quantidades = $_POST['quantidade'] ?? null;
        $valores = $_POST['valorUnitario'] ?? null;

        if (!is_array($produtosIds) || !is_array($quantidades) || !is_array($valores)) {
            throw new InvalidArgumentException('A lista de produtos está inválida.');
        }

        if (
            count($produtosIds) < 1 ||
            count($produtosIds) > 100 ||
            count($produtosIds) !== count($quantidades) ||
            count($produtosIds) !== count($valores) ||
            array_keys($produtosIds) !== array_keys($quantidades) ||
            array_keys($produtosIds) !== array_keys($valores)
        ) {
            throw new InvalidArgumentException('Confira os itens informados na compra.');
        }

        $itens = [];
        $totalGeral = 0.0;

        foreach ($produtosIds as $indice => $produtoInformado) {
            $idProduto = filter_var($produtoInformado, FILTER_VALIDATE_INT);
            $quantidadeTexto = $quantidades[$indice];
            $valorTexto = $valores[$indice];

            if (!$idProduto || $idProduto < 1) {
                throw new InvalidArgumentException('Selecione um produto válido em todos os itens.');
            }

            if (
                !is_scalar($quantidadeTexto) ||
                !preg_match('/^[0-9]{1,7}$/', (string) $quantidadeTexto)
            ) {
                throw new InvalidArgumentException('Informe quantidades inteiras válidas.');
            }

            $quantidade = (int) $quantidadeTexto;

            if ($quantidade < 1 || $quantidade > 1000000) {
                throw new InvalidArgumentException('A quantidade informada está fora do limite permitido.');
            }

            if (
                !is_scalar($valorTexto) ||
                !preg_match('/^[0-9]{1,8}(\.[0-9]{1,2})?$/', (string) $valorTexto)
            ) {
                throw new InvalidArgumentException('Informe valores unitários válidos, usando ponto nos centavos.');
            }

            $valorUnitario = (float) $valorTexto;
            $totalLinha = round($quantidade * $valorUnitario, 2);

            if (!is_finite($totalLinha) || $totalLinha < 0 || $totalLinha > 99999999.99) {
                throw new InvalidArgumentException('O valor total de um item ultrapassa o limite permitido.');
            }

            $totalGeral += $totalLinha;

            if (!is_finite($totalGeral) || $totalGeral > 99999999.99) {
                throw new InvalidArgumentException('O total da compra ultrapassa o limite permitido.');
            }

            $itens[] = [
                'idProduto' => $idProduto,
                'quantidade' => $quantidade,
                'valorUnitario' => $valorUnitario,
                'totalLinha' => $totalLinha
            ];
        }

        $nomeArquivo = null;

        if (isset($_FILES['arquivo_nf'])) {
            $upload = $_FILES['arquivo_nf'];

            if (!is_array($upload) || !isset($upload['error'])) {
                throw new InvalidArgumentException('O anexo enviado é inválido.');
            }

            if ($upload['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($upload['error'] !== UPLOAD_ERR_OK) {
                    throw new InvalidArgumentException('Não foi possível receber o anexo. Tente novamente.');
                }

                if (
                    !isset($upload['tmp_name'], $upload['name'], $upload['size']) ||
                    !is_uploaded_file($upload['tmp_name']) ||
                    $upload['size'] < 1 ||
                    $upload['size'] > 10 * 1024 * 1024
                ) {
                    throw new InvalidArgumentException('O anexo deve ter até 10 MB.');
                }

                $extensao = strtolower(pathinfo($upload['name'], PATHINFO_EXTENSION));

                if (!in_array($extensao, ['pdf', 'xml'], true)) {
                    throw new InvalidArgumentException('Anexe somente arquivos PDF ou XML.');
                }

                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($upload['tmp_name']);

                $mimesPermitidos = [
                    'pdf' => ['application/pdf'],
                    'xml' => ['application/xml', 'text/xml', 'application/octet-stream']
                ];

                if (!in_array($mime, $mimesPermitidos[$extensao], true)) {
                    throw new InvalidArgumentException('O conteúdo do anexo não corresponde ao formato informado.');
                }

                $diretorio = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';

                if (!is_dir($diretorio) && !mkdir($diretorio, 0755, true) && !is_dir($diretorio)) {
                    throw new RuntimeException('Não foi possível preparar o diretório de anexos.');
                }

                $nomeArquivo = 'NF_' . bin2hex(random_bytes(16)) . '.' . $extensao;
                $destino = $diretorio . DIRECTORY_SEPARATOR . $nomeArquivo;

                if (!move_uploaded_file($upload['tmp_name'], $destino)) {
                    throw new RuntimeException('Não foi possível salvar o anexo.');
                }

                $arquivoSalvo = $destino;
            }
        }

        $codigoAgrupador = 'COMPRA-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));

        $conn->begin_transaction();
        $transacaoAtiva = true;

        $stmtFornecedor = $conn->prepare(
            "SELECT idFornecedor FROM fornecedor WHERE idFornecedor = ? FOR UPDATE"
        );
        $stmtFornecedor->bind_param('i', $idFornecedor);
        $stmtFornecedor->execute();
        $stmtFornecedor->store_result();

        if ($stmtFornecedor->num_rows !== 1) {
            $stmtFornecedor->close();
            throw new InvalidArgumentException('O fornecedor selecionado não foi encontrado.');
        }

        $stmtFornecedor->close();

        $stmtForma = $conn->prepare(
            "SELECT idFormaPagamento FROM formapagamento WHERE idFormaPagamento = ? FOR UPDATE"
        );
        $stmtForma->bind_param('i', $idFormaPgto);
        $stmtForma->execute();
        $stmtForma->store_result();

        if ($stmtForma->num_rows !== 1) {
            $stmtForma->close();
            throw new InvalidArgumentException('A forma de pagamento selecionada não foi encontrada.');
        }

        $stmtForma->close();

        $sqlInsert = "INSERT INTO compra
            (codigo_controle, idProduto, idFornecedor, idFormaPagamento, quantidade,
             ValorTotal, DataCompra, StatusCompra, arquivo_nf)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmtInsert = $conn->prepare($sqlInsert);

        $stmtProduto = $conn->prepare(
            "SELECT idProduto FROM produtos WHERE idProduto = ? FOR UPDATE"
        );

        $stmtEstoque = $conn->prepare(
            "UPDATE produtos
             SET estoque = COALESCE(estoque, 0) + ?, preco_custo_atual = ?
             WHERE idProduto = ?"
        );

        foreach ($itens as $item) {
            $idProduto = $item['idProduto'];
            $quantidade = $item['quantidade'];
            $valorUnitario = $item['valorUnitario'];
            $totalLinha = $item['totalLinha'];

            $stmtProduto->bind_param('i', $idProduto);
            $stmtProduto->execute();
            $stmtProduto->store_result();

            $produtoExiste = ($stmtProduto->num_rows === 1);
            $stmtProduto->free_result();

            if (!$produtoExiste) {
                throw new InvalidArgumentException('Um dos produtos selecionados não foi encontrado.');
            }

            $stmtInsert->bind_param(
                'siiiidsss',
                $codigoAgrupador,
                $idProduto,
                $idFornecedor,
                $idFormaPgto,
                $quantidade,
                $totalLinha,
                $dataCompra,
                $status,
                $nomeArquivo
            );

            $stmtInsert->execute();

            if ($status === 'Recebido') {
                $stmtEstoque->bind_param('idi', $quantidade, $valorUnitario, $idProduto);
                $stmtEstoque->execute();

                if ($stmtEstoque->affected_rows !== 1) {
                    throw new RuntimeException('Não foi possível atualizar o estoque de um produto.');
                }
            }
        }

        $stmtInsert->close();
        $stmtProduto->close();
        $stmtEstoque->close();

        $conn->commit();
        $transacaoAtiva = false;

        header('Location: compra.php?sucesso=1');
        exit;

    } catch (InvalidArgumentException $e) {
        if ($transacaoAtiva) {
            $conn->rollback();
        }

        if ($arquivoSalvo !== null && is_file($arquivoSalvo)) {
            @unlink($arquivoSalvo);
        }

        $erro = $e->getMessage();

    } catch (Throwable $e) {
        if ($transacaoAtiva) {
            $conn->rollback();
        }

        if ($arquivoSalvo !== null && is_file($arquivoSalvo)) {
            @unlink($arquivoSalvo);
        }

        error_log('Erro ao registrar compra: ' . $e->getMessage());
        $erro = 'Não foi possível registrar a compra. Verifique os dados e tente novamente.';
    }
}

$resProdutos = $conn->query(
    "SELECT idProduto, Descricao FROM produtos ORDER BY Descricao ASC"
);
$listaProdutos = $resProdutos->fetch_all(MYSQLI_ASSOC);

$fornecedores = $conn->query(
    "SELECT idFornecedor, RazaoSocial FROM fornecedor ORDER BY RazaoSocial ASC"
);

$formas = $conn->query(
    "SELECT idFormaPagamento, Tipo FROM formapagamento ORDER BY Tipo ASC"
);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta charset="UTF-8">
    <title>Entrada de Mercadoria | ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="p-4 sm:p-6 lg:p-8">

    <div class="max-w-5xl mx-auto bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_nova_compra'], ENT_QUOTES, 'UTF-8') ?>">
            <div class="p-5 sm:p-8 border-b border-slate-100 flex justify-between items-center bg-slate-50/70 gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-black text-slate-800 uppercase tracking-tight">Nova Compra em Lote</h1>
                    <p class="text-slate-500 text-xs font-semibold uppercase tracking-wider mt-2">Vincule múltiplos produtos a uma única NF</p>
                </div>
                <a href="compra.php" class="text-slate-300 hover:text-red-500 transition-all text-2xl"><i class="fas fa-times-circle"></i></a>
            </div>

                        <?php if ($erro !== null): ?>
                <div class="mx-5 sm:mx-8 mt-6 p-4 rounded-xl border border-red-200 bg-red-50 text-red-700 text-sm font-semibold" role="alert">
                    <i class="fas fa-circle-exclamation mr-2"></i>
                    <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>
<div class="p-5 sm:p-8 space-y-8 sm:space-y-10">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">Fornecedor</label>
                        <select name="idFornecedor" required class="w-full p-3 sm:p-4 bg-slate-50 border border-slate-200 rounded-xl font-semibold text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition outline-none">
                            <option value="">Selecione...</option>
                            <?php foreach($fornecedores as $f): ?>
                                <option value="<?= $f['idFornecedor'] ?>"><?= htmlspecialchars($f['RazaoSocial'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">Forma Pagto</label>
                        <select name="idFormaPagamento" required class="w-full p-3 sm:p-4 bg-slate-50 border border-slate-200 rounded-xl font-semibold text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition outline-none">
                            <?php foreach($formas as $fp): ?>
                                <option value="<?= $fp['idFormaPagamento'] ?>"><?= htmlspecialchars($fp['Tipo'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">Data Entrada</label>
                        <input type="date" name="dataCompra" value="<?= date('Y-m-d') ?>" class="w-full p-3 sm:p-4 bg-slate-50 border border-slate-200 rounded-xl font-semibold text-slate-700 outline-none focus:border-blue-500 transition">
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex justify-between items-center border-b border-slate-100 pb-4">
                        <h2 class="text-sm font-black text-slate-800 uppercase italic">Produtos da Nota</h2>
                        <button type="button" onclick="adicionarLinha()" class="bg-blue-600 text-white px-4 py-3 rounded-xl text-xs font-bold uppercase hover:bg-blue-700 transition shadow-sm">
                            <i class="fas fa-plus mr-2"></i> Adicionar Item
                        </button>
                    </div>

                    <div id="container-produtos" class="space-y-4">
                        <div class="linha-produto grid grid-cols-1 md:grid-cols-12 gap-4 bg-slate-50 p-6 rounded-3xl border border-white shadow-sm relative transition-all hover:shadow-md">
                            <div class="md:col-span-6">
                                <label class="text-[9px] font-bold text-slate-400 ml-1 uppercase">Produto</label>
                                <select name="idProduto[]" required class="w-full p-3 bg-white border border-slate-200 rounded-xl font-semibold text-slate-700 outline-none focus:border-blue-500 transition">
                                    <option value="">Selecione...</option>
                                    <?php foreach($listaProdutos as $p): ?>
                                        <option value="<?= $p['idProduto'] ?>"><?= htmlspecialchars($p['Descricao'], ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-[9px] font-bold text-slate-400 ml-1 uppercase">Qtd</label>
                                <input type="number" name="quantidade[]" required min="1" placeholder="0" class="w-full p-3 bg-white border border-slate-200 rounded-xl font-bold text-blue-700 outline-none focus:border-blue-500 transition">
                            </div>
                            <div class="md:col-span-3">
                                <label class="text-[9px] font-bold text-slate-400 ml-1 uppercase">Valor Unitário</label>
                                <input type="number" name="valorUnitario[]" step="0.01" required placeholder="0.00" class="w-full p-3 bg-white border border-slate-200 rounded-xl font-bold text-emerald-700 outline-none focus:border-emerald-500 transition">
                            </div>
                            <div class="md:col-span-1 flex items-end justify-center">
                                 <button type="button" onclick="removerLinha(this)" class="w-10 h-10 flex items-center justify-center rounded-xl text-slate-300 hover:bg-red-50 hover:text-red-500 transition">
                                    <i class="fas fa-trash-alt"></i>
                                 </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-6">
                    <div class="p-5 sm:p-6 bg-blue-50/60 rounded-2xl border border-blue-100">
                        <label class="block text-[10px] font-black uppercase text-blue-400 mb-4 tracking-widest">Anexar XML ou PDF da Nota</label>
                        <input type="file" name="arquivo_nf" class="text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-black file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                    </div>

                    <div class="p-5 sm:p-6 bg-slate-50 rounded-2xl border border-slate-200">
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-4 tracking-widest text-center">Status do Recebimento</label>
                        <div class="flex gap-4">
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="status" value="Pendente" checked class="hidden peer">
                                <div class="p-3 text-center rounded-xl border-2 border-white bg-white shadow-sm font-bold text-slate-300 peer-checked:border-amber-400 peer-checked:text-amber-600 transition-all text-[10px]">PENDENTE</div>
                            </label>
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="status" value="Recebido" class="hidden peer">
                                <div class="p-3 text-center rounded-xl border-2 border-white bg-white shadow-sm font-bold text-slate-300 peer-checked:border-emerald-500 peer-checked:text-emerald-600 transition-all text-[10px]">RECEBIDO</div>
                            </label>
                        </div>
                    </div>
                </div>

                <button name="btn_salvar" class="w-full bg-blue-600 text-white py-4 rounded-xl font-bold uppercase tracking-wider shadow-sm hover:bg-blue-700 transition-all active:scale-[0.99]">
                    Finalizar Entrada de Estoque
                </button>
            </div>
        </form>
    </div>

    <script>
    function adicionarLinha() {
        const container = document.getElementById('container-produtos');
        const novaLinha = container.firstElementChild.cloneNode(true);
        novaLinha.querySelectorAll('input').forEach(i => i.value = '');
        novaLinha.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
        container.appendChild(novaLinha);
    }

    function removerLinha(btn) {
        const container = document.getElementById('container-produtos');
        if (container.children.length > 1) {
            btn.closest('.linha-produto').remove();
        } else {
            alert("Mínimo de um item por compra!");
        }
    }
    </script>
</body>
</html>