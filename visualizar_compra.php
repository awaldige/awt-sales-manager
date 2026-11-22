<?php
include 'auth.php';
include 'config.php';

// Pegamos o código da URL
$cod = $_GET['cod'] ?? '';

if (empty($cod)) {
    header("Location: compra.php");
    exit();
}

// 1. BUSCAR DADOS GERAIS DA COMPRA (Fornecedor, Data, NF)
$sqlCabecalho = "SELECT 
                    cp.codigo_controle, 
                    cp.DataCompra, 
                    cp.StatusCompra, 
                    cp.arquivo_nf,
                    f.RazaoSocial, 
                    f.Email as FornecedorEmail,
                    fp.Tipo as FormaPgto
                 FROM compra cp
                 LEFT JOIN fornecedor f ON cp.idFornecedor = f.idFornecedor
                 LEFT JOIN formapagamento fp ON cp.idFormaPagamento = fp.idFormaPagamento
                 WHERE cp.codigo_controle = ?
                 LIMIT 1";

$stmt = $conn->prepare($sqlCabecalho);
$stmt->bind_param("s", $cod);
$stmt->execute();
$dados = $stmt->get_result()->fetch_assoc();

if (!$dados) {
    die("Compra não encontrada ou código inválido.");
}

// 2. BUSCAR OS ITENS (PRODUTOS) DESTA COMPRA
$sqlItens = "SELECT 
                cp.quantidade, 
                cp.ValorTotal, 
                p.Descricao, 
                (cp.ValorTotal / cp.quantidade) as valor_unit
             FROM compra cp
             LEFT JOIN produtos p ON cp.idProduto = p.idProduto
             WHERE cp.codigo_controle = ?";

$stmtItens = $conn->prepare($sqlItens);
$stmtItens->bind_param("s", $cod);
$stmtItens->execute();
$itens = $stmtItens->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da Compra | AWT Sales Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-100 p-4 sm:p-6 lg:p-8">

    <main class="max-w-5xl mx-auto">
        <a href="compra.php" class="inline-flex items-center gap-2 text-slate-500 hover:text-blue-600 font-bold text-xs uppercase tracking-wider mb-6 transition">
            <i class="fas fa-arrow-left"></i>
            Voltar para Gestão de Compras
        </a>

        <section class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

            <header class="bg-slate-900 text-white p-6 sm:p-8 lg:p-10 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-5">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
                <div class="min-w-0">
                    <span class="text-blue-400 font-bold text-[10px] uppercase tracking-[0.2em]">Resumo da Entrada</span>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight mt-2 break-all">
                        <?= htmlspecialchars($dados['codigo_controle'], ENT_QUOTES, 'UTF-8') ?>
                    </h1>
                    <p class="text-slate-400 text-xs mt-2">Detalhamento dos produtos e valores da compra</p>
                </div>

                <div class="shrink-0">
                    <?php if ($dados['StatusCompra'] === 'Recebido'): ?>
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-500/15 border border-emerald-400/30 text-emerald-300 font-bold text-xs uppercase">
                            <i class="fas fa-check-circle"></i>
                            <?= htmlspecialchars($dados['StatusCompra'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-500/15 border border-amber-400/30 text-amber-300 font-bold text-xs uppercase">
                            <i class="fas fa-clock"></i>
                            <?= htmlspecialchars($dados['StatusCompra'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    <?php endif; ?>
                </div>
            </header>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 p-5 sm:p-8 border-b border-slate-100">
                <div class="rounded-xl bg-slate-50 border border-slate-200 p-5">
                    <div class="flex items-center gap-2 text-slate-400 mb-3">
                        <i class="fas fa-truck text-sm"></i>
                        <h2 class="text-[10px] font-black uppercase tracking-widest">Fornecedor</h2>
                    </div>
                    <p class="text-lg font-bold text-slate-800 break-words">
                        <?= htmlspecialchars($dados['RazaoSocial'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </p>
                    <p class="text-sm text-slate-500 mt-1 break-all">
                        <?= htmlspecialchars($dados['FornecedorEmail'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </p>
                </div>

                <div class="rounded-xl bg-slate-50 border border-slate-200 p-5">
                    <div class="flex items-center gap-2 text-slate-400 mb-3">
                        <i class="fas fa-calendar-alt text-sm"></i>
                        <h2 class="text-[10px] font-black uppercase tracking-widest">Data e Pagamento</h2>
                    </div>
                    <p class="text-lg font-bold text-slate-800">
                        <?= date('d/m/Y', strtotime($dados['DataCompra'])) ?>
                    </p>
                    <p class="text-sm text-blue-600 font-bold mt-1">
                        <?= htmlspecialchars($dados['FormaPgto'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </p>
                </div>
            </div>

            <div class="p-5 sm:p-8">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i class="fas fa-boxes-stacked"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-black text-slate-800 uppercase">Produtos da Compra</h2>
                        <p class="text-xs text-slate-400 mt-1">Itens registrados nesta entrada</p>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full min-w-[650px] text-left">
                        <thead class="bg-slate-50">
                            <tr class="text-[10px] font-black text-slate-500 uppercase tracking-wider">
                                <th class="px-5 py-4">Produto</th>
                                <th class="px-5 py-4 text-center">Quantidade</th>
                                <th class="px-5 py-4 text-right">Valor Unitário</th>
                                <th class="px-5 py-4 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php
                            $totalGeral = 0;
                            while($item = $itens->fetch_assoc()):
                                $totalGeral += $item['ValorTotal'];
                            ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-5 py-4 font-semibold text-slate-700">
                                    <?= htmlspecialchars($item['Descricao'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td class="px-5 py-4 text-center font-bold text-slate-500 text-sm">
                                    <?= $item['quantidade'] ?>
                                </td>
                                <td class="px-5 py-4 text-right text-slate-500 text-sm whitespace-nowrap">
                                    R$ <?= number_format($item['valor_unit'], 2, ',', '.') ?>
                                </td>
                                <td class="px-5 py-4 text-right font-bold text-slate-800 whitespace-nowrap">
                                    R$ <?= number_format($item['ValorTotal'], 2, ',', '.') ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                        <tfoot class="bg-slate-50">
                            <tr>
                                <td colspan="3" class="px-5 py-5 text-right text-[10px] font-black text-slate-500 uppercase tracking-wider">
                                    Valor Total da Nota
                                </td>
                                <td class="px-5 py-5 text-right font-black text-xl sm:text-2xl text-blue-600 whitespace-nowrap">
                                    R$ <?= number_format($totalGeral, 2, ',', '.') ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <?php
    $nomeArquivoNF = basename((string)($dados['arquivo_nf'] ?? ''));
    $caminhoArquivoNF = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $nomeArquivoNF;
    $arquivoNFDisponivel = $nomeArquivoNF !== '' && is_file($caminhoArquivoNF);
?>

<?php if (!empty($dados['arquivo_nf'])): ?>
<div class="p-5 sm:p-8 border-t border-slate-100 bg-slate-50 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-5">
    <div class="flex items-center gap-4 min-w-0">
        <div class="w-12 h-12 shrink-0 bg-white rounded-xl flex items-center justify-center text-red-500 border border-slate-200 shadow-sm">
            <i class="fas fa-file-pdf text-xl"></i>
        </div>
        <div class="min-w-0">
            <p class="text-xs font-black text-slate-800 uppercase">Nota Fiscal Anexa</p>
            <p class="text-xs text-slate-500 mt-1 break-all">
                <?= htmlspecialchars($nomeArquivoNF, ENT_QUOTES, 'UTF-8') ?>
            </p>
        </div>
    </div>

    <?php if ($arquivoNFDisponivel): ?>
        <a href="uploads/<?= rawurlencode($nomeArquivoNF) ?>" target="_blank" rel="noopener noreferrer" class="inline-flex justify-center items-center gap-2 bg-slate-900 text-white px-5 py-3 rounded-xl font-bold text-xs uppercase tracking-wider hover:bg-blue-600 transition shadow-sm">
            <i class="fas fa-external-link-alt"></i>
            Abrir Documento
        </a>
    <?php else: ?>
        <div class="inline-flex items-center gap-2 text-amber-700 bg-amber-50 border border-amber-200 px-4 py-3 rounded-xl text-xs font-semibold">
            <i class="fas fa-info-circle"></i>
            Documento não disponível nesta demonstração.
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

        </section>
    </main>

</body>
</html>