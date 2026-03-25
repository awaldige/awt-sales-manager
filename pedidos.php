<?php
session_start();
include 'config.php';
include 'auth.php';

// CONSULTA SQL: Agrupa por código de venda para somar totais e listar produtos
// O uso de SUM(p.ValorTotal) garante que se você adicionar um item na edição, o total sobe aqui automaticamente.
$sql = "SELECT 
            p.codigoVenda AS codigo, 
            GROUP_CONCAT(p.Descricao SEPARATOR ' + ') AS produto_nome,
            SUM(p.quantidade) AS quantidade,
            SUM(p.ValorTotal) AS total,
            DATE_FORMAT(MAX(p.dataPedido), '%d/%m/%y %H:%i') AS data_formatada,
            CONCAT(c.Pnome, ' ', IFNULL(c.Sobrenome, '')) AS cliente_nome,
            p.StatusPedido
        FROM pedido p
        LEFT JOIN cliente c ON p.idCliente = c.idCliente
        GROUP BY p.codigoVenda
        ORDER BY MAX(p.idPedido) DESC";

$res = $conn->query($sql);

if (!$res) {
    die("Erro na consulta SQL: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Pedidos | AWTecnology</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 p-6 md:p-10">

    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-6 px-4">
            <div>
                <h1 class="text-3xl font-black uppercase italic text-slate-900">Gerenciar <span class="text-blue-600">Pedidos</span></h1>
                <div class="flex items-center gap-4 mt-2">
                    <a href="index.php" class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-emerald-500 transition-all flex items-center gap-2">
                        <i class="fas fa-chart-pie"></i> Retornar ao Dashboard
                    </a>
                    <span class="text-slate-300">•</span>
                    <p class="text-[10px] font-black uppercase tracking-widest text-blue-600/50">AWTecnology List</p>
                </div>
            </div>

            <a href="novo_pedido.php" class="bg-blue-600 text-white px-8 py-4 rounded-2xl font-black uppercase text-xs tracking-widest shadow-xl shadow-blue-100 hover:bg-blue-700 hover:-translate-y-1 transition-all">
                <i class="fas fa-plus mr-2"></i> Novo Pedido
            </a>
        </div>

        <div class="bg-white rounded-[2.5rem] shadow-xl overflow-hidden border border-slate-100">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50 text-[10px] font-black uppercase text-slate-400 tracking-widest">
                        <th class="p-6 text-center">Código</th>
                        <th class="p-6">Cliente / Produtos</th>
                        <th class="p-6 text-center">Qtd Total</th>
                        <th class="p-6 text-right">Total Pedido</th>
                        <th class="p-6 text-center">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php while($row = $res->fetch_assoc()): ?>
                    <tr class="hover:bg-blue-50/20 transition-all">
                        <td class="p-6 text-center font-bold text-blue-600">
                            <span class="bg-blue-50 px-3 py-1 rounded-lg italic"><?= $row['codigo'] ?></span>
                        </td>
                        
                        <td class="p-6">
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-800 italic leading-tight mb-1"><?= $row['produto_nome'] ?></span>
                                <div class="flex items-center gap-3">
                                    <span class="text-[10px] font-black uppercase text-slate-400">
                                        <i class="fas fa-user text-[8px] mr-1"></i> <?= $row['cliente_nome'] ?> 
                                        <span class="mx-2 text-slate-200">|</span> 
                                        <i class="fas fa-calendar text-[8px] mr-1"></i> <?= $row['data_formatada'] ?>
                                    </span>
                                    <span class="text-[8px] font-black uppercase px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 italic">
                                        <?= $row['StatusPedido'] ?>
                                    </span>
                                </div>
                            </div>
                        </td>

                        <td class="p-6 text-center font-bold text-slate-500"><?= $row['quantidade'] ?> un.</td>
                        
                        <td class="p-6 text-right font-black text-slate-900 text-lg">
                            R$ <?= number_format($row['total'], 2, ',', '.') ?>
                        </td>

                        <td class="p-6 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="gerar_recibo.php?cod=<?= $row['codigo'] ?>" target="_blank" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 text-slate-400 hover:bg-blue-600 hover:text-white transition-all shadow-sm" title="Imprimir Recibo">
                                    <i class="fas fa-print text-sm"></i>
                                </a>

                                <a href="editar_pedido.php?cod=<?= $row['codigo'] ?>" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 text-slate-400 hover:bg-amber-500 hover:text-white transition-all shadow-sm" title="Gerenciar Itens">
                                    <i class="fas fa-boxes-stacked text-sm"></i>
                                </a>

                                <a href="excluir_pedido.php?cod=<?= $row['codigo'] ?>" onclick="return confirm('ATENÇÃO: Isso excluirá todos os itens deste pedido e estornará o estoque. Confirmar?')" class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-50 text-red-400 hover:bg-red-600 hover:text-white transition-all shadow-sm" title="Excluir Pedido">
                                    <i class="fas fa-trash text-sm"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-6 text-center">
            <p class="text-[10px] font-black text-slate-300 uppercase tracking-[0.3em]">AWTecnology Premium Management</p>
        </div>
    </div>

</body>
</html>