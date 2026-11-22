<?php
require_once 'auth.php';
require_once 'config.php';
if (empty($_SESSION['csrf_exclusao_pedido'])) {
    $_SESSION['csrf_exclusao_pedido'] = bin2hex(random_bytes(32));
}


// CONSULTA SQL: Agrupa por código de venda para somar totais e listar produtos
// O uso de SUM(p.ValorTotal) garante que se você adicionar um item na edição, o total sobe aqui automaticamente.
$sql = "SELECT
            p.codigoVenda AS codigo,
            GROUP_CONCAT(p.Descricao ORDER BY p.idPedido SEPARATOR ' + ') AS produto_nome,
            SUM(p.quantidade) AS quantidade,
            SUM(p.ValorTotal) AS total,
            DATE_FORMAT(MAX(p.dataPedido), '%d/%m/%y %H:%i') AS data_formatada,
            CONCAT(MAX(c.Pnome), ' ', IFNULL(MAX(c.Sobrenome), '')) AS cliente_nome,
            MAX(p.StatusPedido) AS StatusPedido
        FROM pedido p
        LEFT JOIN cliente c ON p.idCliente = c.idCliente
        GROUP BY p.codigoVenda
        ORDER BY MAX(p.idPedido) DESC";

$res = $conn->query($sql);

if (!$res) {
    error_log("Erro na consulta de pedidos: " . $conn->error);
    http_response_code(500);
    exit("Não foi possível carregar os pedidos. Tente novamente mais tarde.");
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta charset="UTF-8">
    <title>Pedidos | AWTecnology</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 p-4 sm:p-6 lg:p-8">

    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-5 px-1">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-slate-900">Gerenciar <span class="text-blue-600">Pedidos</span></h1>
                <div class="flex items-center gap-4 mt-2">
                    <a href="index.php" class="text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-emerald-500 transition-all flex items-center gap-2">
                        <i class="fas fa-chart-pie"></i> Retornar ao Dashboard
                    </a>
                    <span class="text-slate-300">•</span>
                    <p class="text-[10px] font-black uppercase tracking-widest text-blue-600/50">AWTecnology List</p>
                </div>
            </div>

            <a href="novo_pedido.php" class="inline-flex items-center justify-center bg-blue-600 text-white px-6 py-3.5 rounded-xl font-bold text-xs tracking-wide shadow-lg shadow-blue-100 hover:bg-blue-700 hover:-translate-y-0.5 transition-all w-full sm:w-auto">
                <i class="fas fa-plus mr-2"></i> Novo Pedido
            </a>
        </div>

        <?php
        $mensagemExclusao = '';
        $tipoMensagem = 'erro';

        if (($_GET['status'] ?? '') === 'excluido') {
            $mensagemExclusao = 'Pedido excluído com sucesso.';
            $tipoMensagem = 'sucesso';
        } else {
            $mensagensErro = [
                'codigo_invalido' => 'Código do pedido inválido.',
                'pedido_nao_encontrado' => 'Pedido não encontrado.',
                'produto_sem_identificacao' => 'Exclusão bloqueada: existe item sem identificação segura do produto.',
                'pedido_cancelado' => 'Exclusão bloqueada: este pedido já está cancelado.',
                'produto_nao_encontrado' => 'Exclusão bloqueada: um dos produtos não foi encontrado no cadastro.',
                'erro_ao_devolver_estoque' => 'Não foi possível atualizar o estoque. Nenhuma alteração foi concluída.',
                'erro_ao_excluir_pedido' => 'Não foi possível excluir o pedido. Tente novamente.'
            ];

            $erroRecebido = $_GET['erro'] ?? '';

            if (is_string($erroRecebido) && isset($mensagensErro[$erroRecebido])) {
                $mensagemExclusao = $mensagensErro[$erroRecebido];
            }
        }
        ?>

        <?php if ($mensagemExclusao !== ''): ?>
            <div class="mb-6 rounded-2xl border <?= $tipoMensagem === 'sucesso' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-red-200 bg-red-50 text-red-700' ?> px-6 py-4 text-sm font-bold">
                <?= htmlspecialchars($mensagemExclusao, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto"><table class="w-full min-w-[760px] text-left border-collapse">
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
                            <span class="bg-blue-50 px-3 py-1 rounded-lg italic"><?= htmlspecialchars((string) $row['codigo'], ENT_QUOTES, 'UTF-8') ?></span>
                        </td>

                        <td class="p-6">
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-800 italic leading-tight mb-1"><?= htmlspecialchars((string) $row['produto_nome'], ENT_QUOTES, 'UTF-8') ?></span>
                                <div class="flex items-center gap-3">
                                    <span class="text-[10px] font-black uppercase text-slate-400">
                                        <i class="fas fa-user text-[8px] mr-1"></i> <?= htmlspecialchars((string) $row['cliente_nome'], ENT_QUOTES, 'UTF-8') ?>
                                        <span class="mx-2 text-slate-200">|</span>
                                        <i class="fas fa-calendar text-[8px] mr-1"></i> <?= $row['data_formatada'] ?>
                                    </span>
                                    <span class="text-[8px] font-black uppercase px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 italic">
                                        <?= htmlspecialchars((string) $row['StatusPedido'], ENT_QUOTES, 'UTF-8') ?>
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
                                <a href="gerar_recibo.php?cod=<?= rawurlencode((string) $row['codigo']) ?>" target="_blank" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 text-slate-400 hover:bg-blue-600 hover:text-white transition-all shadow-sm" title="Imprimir Recibo">
                                    <i class="fas fa-print text-sm"></i>
                                </a>

                                <a href="editar_pedido.php?cod=<?= rawurlencode((string) $row['codigo']) ?>" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 text-slate-400 hover:bg-amber-500 hover:text-white transition-all shadow-sm" title="Gerenciar Itens">
                                    <i class="fas fa-boxes-stacked text-sm"></i>
                                </a>

                                <?php if ($nivel === 'admin'): ?>
                                <form method="POST" action="excluir_pedido.php" class="inline-flex" onsubmit="return confirm('Deseja solicitar a exclusão deste pedido?')">
    <input type="hidden" name="cod" value="<?= htmlspecialchars((string) $row['codigo'], ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_exclusao_pedido'], ENT_QUOTES, 'UTF-8') ?>">
    <button type="submit" class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-50 text-red-400 hover:bg-red-600 hover:text-white transition-all shadow-sm" title="Excluir Pedido">
        <i class="fas fa-trash text-sm"></i>
    </button>
</form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table></div>
        </div>

        <div class="mt-6 text-center">
            <p class="text-[10px] font-black text-slate-300 uppercase tracking-[0.3em]">AWTecnology Premium Management</p>
        </div>
    </div>

</body>
</html>
