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
    <meta charset="UTF-8">
    <title>Detalhes da Compra | ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 p-6 md:p-12">

    <div class="max-w-4xl mx-auto">
        
        <a href="compra.php" class="inline-flex items-center text-slate-400 hover:text-slate-600 font-bold text-xs uppercase tracking-widest mb-8 transition">
            <i class="fas fa-arrow-left mr-2"></i> Voltar para a lista
        </a>

        <div class="bg-white rounded-[2.5rem] shadow-2xl shadow-slate-200 border border-slate-100 overflow-hidden">
            
            <div class="p-8 md:p-12 bg-slate-900 text-white flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div>
                    <span class="text-blue-400 font-black text-[10px] uppercase tracking-[0.3em]">Resumo da Entrada</span>
                    <h1 class="text-3xl font-black italic uppercase tracking-tighter"><?= $dados['codigo_controle'] ?></h1>
                </div>
                <div class="text-right">
                    <div class="px-4 py-2 rounded-full bg-white/10 border border-white/20 font-black text-[10px] uppercase tracking-widest">
                        Status: <?= $dados['StatusCompra'] ?>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-8 md:p-12 border-b border-slate-50">
                <div>
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Fornecedor</h3>
                    <p class="text-xl font-bold text-slate-800"><?= $dados['RazaoSocial'] ?></p>
                    <p class="text-sm text-slate-500 italic"><?= $dados['FornecedorEmail'] ?></p>
                </div>
                <div class="md:text-right">
                    <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Data e Pagamento</h3>
                    <p class="text-slate-700 font-bold"><?= date('d/m/Y', strtotime($dados['DataCompra'])) ?></p>
                    <p class="text-sm text-indigo-500 font-black uppercase tracking-tighter"><?= $dados['FormaPgto'] ?></p>
                </div>
            </div>

            <div class="p-8 md:p-12">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b border-slate-100">
                            <th class="pb-4">Produto</th>
                            <th class="pb-4 text-center">Qtd</th>
                            <th class="pb-4 text-right">Unitário</th>
                            <th class="pb-4 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php 
                        $totalGeral = 0;
                        while($item = $itens->fetch_assoc()): 
                            $totalGeral += $item['ValorTotal'];
                        ?>
                        <tr>
                            <td class="py-4 font-bold text-slate-700"><?= $item['Descricao'] ?></td>
                            <td class="py-4 text-center font-black text-slate-400 text-sm"><?= $item['quantidade'] ?></td>
                            <td class="py-4 text-right text-slate-500 text-sm">R$ <?= number_format($item['valor_unit'], 2, ',', '.') ?></td>
                            <td class="py-4 text-right font-black text-slate-900 uppercase tracking-tighter">R$ <?= number_format($item['ValorTotal'], 2, ',', '.') ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="pt-8 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Valor Total da Nota</td>
                            <td class="pt-8 text-right font-black text-3xl text-indigo-600 tracking-tighter">R$ <?= number_format($totalGeral, 2, ',', '.') ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <?php if($dados['arquivo_nf']): ?>
            <div class="p-8 bg-slate-50 border-t border-slate-100 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-red-500 shadow-sm border border-slate-200">
                        <i class="fas fa-file-pdf text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs font-black text-slate-800 uppercase italic">Nota Fiscal Anexa</p>
                        <p class="text-[10px] text-slate-400"><?= $dados['arquivo_nf'] ?></p>
                    </div>
                </div>
                <a href="uploads/<?= $dados['arquivo_nf'] ?>" target="_blank" class="bg-slate-900 text-white px-6 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-indigo-600 transition shadow-lg shadow-indigo-100">
                    <i class="fas fa-download mr-2"></i> Abrir Documento
                </a>
            </div>
            <?php endif; ?>

        </div>
    </div>

</body>
</html>