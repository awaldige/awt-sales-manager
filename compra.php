<?php 
include 'auth.php'; 
include 'config.php'; 

// SQL OTIMIZADA: Agrupa pelo codigo_controle para não repetir a mesma compra
$sql = "SELECT 
            cp.codigo_controle,
            MAX(cp.idCompra) as idExibicao, 
            f.RazaoSocial, 
            fp.Tipo as FormaPgto,
            cp.DataCompra,
            cp.StatusCompra,
            cp.arquivo_nf,
            SUM(cp.ValorTotal) as ValorGeral,
            GROUP_CONCAT(p.Descricao SEPARATOR ', ') as resumo_produtos
        FROM compra cp
        LEFT JOIN fornecedor f ON cp.idFornecedor = f.idFornecedor
        LEFT JOIN formapagamento fp ON cp.idFormaPagamento = fp.idFormaPagamento
        LEFT JOIN produtos p ON cp.idProduto = p.idProduto
        GROUP BY cp.codigo_controle 
        ORDER BY cp.DataCompra DESC, idExibicao DESC";

$res = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gestão de Compras | ERP PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="p-8">

    <div class="max-w-7xl mx-auto">
        
        <?php if(isset($_GET['sucesso'])): ?>
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-100 text-emerald-600 rounded-2xl font-bold text-sm flex items-center gap-3 shadow-sm">
                <div class="w-8 h-8 bg-emerald-500 text-white rounded-full flex items-center justify-center shadow-lg shadow-emerald-200">
                    <i class="fas fa-check text-xs"></i>
                </div>
                Operação realizada com sucesso!
            </div>
        <?php endif; ?>

        <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight italic uppercase">Gestão de Compras</h1>
                <p class="text-slate-400 text-sm font-semibold tracking-wide mt-1">Controle de entradas agrupadas por Nota Fiscal</p>
            </div>
            <div class="flex gap-3">
                <a href="index.php" class="p-4 px-6 bg-white border border-slate-200 rounded-2xl font-bold text-slate-500 hover:bg-slate-50 transition text-xs uppercase shadow-sm">
                    <i class="fas fa-home mr-2"></i>Início
                </a>
                <a href="nova_compra.php" class="p-4 px-6 bg-slate-900 text-white rounded-2xl font-black shadow-xl hover:bg-indigo-600 transition text-xs uppercase tracking-widest">
                    <i class="fas fa-plus mr-2"></i>Nova Entrada
                </a>
            </div>
        </header>

        <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-slate-50/50 text-slate-400 text-[10px] uppercase font-black tracking-[0.2em]">
                        <tr>
                            <th class="p-6">Controle / Fornecedor</th>
                            <th class="p-6">Produtos</th>
                            <th class="p-6 text-center">NF</th>
                            <th class="p-6 text-center">Status</th>
                            <th class="p-6 text-right">Valor Total</th>
                            <th class="p-6 text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if ($res && $res->num_rows > 0): ?>
                            <?php while($row = $res->fetch_assoc()): 
                                $status = $row['StatusCompra'];
                                $cor = $status == 'Recebido' ? 'emerald' : ($status == 'Cancelado' ? 'red' : 'amber');
                                $arquivo = $row['arquivo_nf'];
                                $cod = $row['codigo_controle'] ?? "AVULSO-".$row['idExibicao'];
                            ?>
                            <tr class="hover:bg-blue-50/30 transition group">
                                <td class="p-6">
                                    <span class="text-blue-600 font-black text-[9px] block mb-1 uppercase tracking-tighter opacity-70"><?= $cod ?></span>
                                    <span class="font-bold text-slate-700 block"><?= $row['RazaoSocial'] ?? 'Fornecedor não identificado' ?></span>
                                    <span class="text-[10px] text-slate-400 font-bold italic"><?= date('d/m/Y', strtotime($row['DataCompra'])) ?></span>
                                </td>
                                
                                <td class="p-6">
                                    <div class="max-w-xs text-[11px] text-slate-500 font-medium italic truncate" title="<?= $row['resumo_produtos'] ?>">
                                        <?= $row['resumo_produtos'] ?>
                                    </div>
                                    <span class="text-[9px] bg-slate-100 text-slate-500 px-2 py-0.5 rounded font-black uppercase mt-1 inline-block italic">
                                        <?= $row['FormaPgto'] ?>
                                    </span>
                                </td>

                                <td class="p-6 text-center">
                                    <?php if($arquivo): ?>
                                        <a href="uploads/<?= $arquivo ?>" target="_blank" class="w-10 h-10 inline-flex items-center justify-center bg-indigo-50 text-indigo-600 rounded-xl hover:bg-indigo-600 hover:text-white transition shadow-sm" title="Ver Nota Fiscal">
                                            <i class="fas fa-file-invoice"></i>
                                        </a>
                                    <?php else: ?>
                                        <div class="text-slate-200"><i class="fas fa-file-circle-xmark"></i></div>
                                    <?php endif; ?>
                                </td>

                                <td class="p-6 text-center">
                                    <span class="px-3 py-1.5 rounded-xl text-[9px] font-black uppercase bg-<?= $cor ?>-50 text-<?= $cor ?>-600 border border-<?= $cor ?>-100 inline-block shadow-sm">
                                        <i class="fas <?= $status == 'Recebido' ? 'fa-check' : 'fa-clock' ?> mr-1"></i>
                                        <?= $status ?>
                                    </span>
                                </td>

                                <td class="p-6 text-right">
                                    <span class="text-[10px] text-slate-400 font-bold mr-1 italic">R$</span>
                                    <span class="font-black text-slate-900 text-lg tracking-tighter"><?= number_format($row['ValorGeral'], 2, ',', '.') ?></span>
                                </td>

                                <td class="p-6">
                                    <div class="flex justify-center gap-1">
                                        
                                        <?php if($status == 'Pendente'): ?>
                                            <a href="editar_status_compra.php?cod=<?= $cod ?>" 
                                               onclick="return confirm('Confirmar o recebimento desta mercadoria? O estoque será atualizado agora.')"
                                               class="w-9 h-9 flex items-center justify-center rounded-xl text-amber-500 hover:bg-emerald-500 hover:text-white transition shadow-sm" 
                                               title="Dar Baixa / Recebido">
                                                <i class="fas fa-check-double text-sm"></i>
                                            </a>
                                        <?php endif; ?>

                                        <a href="visualizar_compra.php?cod=<?= $cod ?>" class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:bg-blue-600 hover:text-white transition shadow-sm" title="Ver Detalhes">
                                            <i class="fas fa-eye text-sm"></i>
                                        </a>
                                        
                                        <a href="excluir_compra.php?cod=<?= $cod ?>" 
                                           onclick="return confirm('ATENÇÃO: Isso excluirá todos os itens desta nota fiscal (Controle: <?= $cod ?>). Deseja continuar?')"
                                           class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:bg-red-500 hover:text-white transition shadow-sm" title="Excluir Tudo">
                                            <i class="fas fa-trash text-sm"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="p-24 text-center">
                                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-200">
                                        <i class="fas fa-cart-flatbed fa-3x"></i>
                                    </div>
                                    <p class="text-slate-400 font-bold italic text-sm uppercase tracking-widest">Nenhuma compra registrada.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>