<?php
include 'auth.php';
include 'config.php';

if (empty($_SESSION['csrf_recebimento_compra'])) {
    $_SESSION['csrf_recebimento_compra'] = bin2hex(random_bytes(32));
}

if (empty($_SESSION['csrf_exclusao_compra'])) {
    $_SESSION['csrf_exclusao_compra'] = bin2hex(random_bytes(32));
}

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
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta charset="UTF-8">
    <title>Gestão de Compras | ERP PRO</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="p-4 sm:p-6 lg:p-8">

    <div class="max-w-7xl mx-auto">

        <?php
        $mensagem = null;

if (($_GET['exclusao'] ?? '') === 'sucesso') {
    $mensagem = [
        'tipo' => 'sucesso',
        'texto' => 'Compra pendente excluída com sucesso.'
    ];
} elseif (isset($_GET['sucesso']) && $_GET['sucesso'] === '1') {
    $mensagem = [
        'tipo' => 'sucesso',
        'texto' => 'Recebimento confirmado! O estoque foi atualizado.'
    ];
} elseif (($_GET['info'] ?? '') === 'ja_recebido') {
    $mensagem = [
        'tipo' => 'info',
        'texto' => 'Esta compra já foi recebida anteriormente.'
    ];
} elseif (($_GET['erro'] ?? '') === 'metodo') {
    $mensagem = [
        'tipo' => 'erro',
        'texto' => 'Acesso inválido. Utilize os botões da tela para realizar esta operação.'
    ];
} elseif (($_GET['erro'] ?? '') === 'seguranca') {
    $mensagem = [
        'tipo' => 'erro',
        'texto' => 'Não foi possível validar a segurança da solicitação. Atualize a página e tente novamente.'
    ];
} elseif (($_GET['erro'] ?? '') === 'exclusao') {
    $mensagem = [
        'tipo' => 'erro',
        'texto' => 'Esta compra não pode ser excluída. Somente compras pendentes podem ser removidas.'
    ];
} elseif (isset($_GET['erro'])) {
    $mensagem = [
        'tipo' => 'erro',
        'texto' => 'Não foi possível concluir a operação. Verifique os dados e tente novamente.'
    ];
}
        $estilosMensagem = [
            'sucesso' => 'bg-emerald-50 border-emerald-200 text-emerald-700',
            'info' => 'bg-blue-50 border-blue-200 text-blue-700',
            'erro' => 'bg-red-50 border-red-200 text-red-700'
        ];

        $iconesMensagem = [
            'sucesso' => 'fa-check-circle',
            'info' => 'fa-info-circle',
            'erro' => 'fa-exclamation-circle'
        ];
        ?>

        <?php if ($mensagem !== null): ?>
            <div class="mb-6 p-4 border rounded-2xl font-bold text-sm flex items-center gap-3 shadow-sm <?= $estilosMensagem[$mensagem['tipo']] ?>">
                <i class="fas <?= $iconesMensagem[$mensagem['tipo']] ?> text-lg"></i>
                <?= htmlspecialchars($mensagem['texto'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
            <div>
                <h1 class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight uppercase">Gestão de Compras</h1>
                <p class="text-slate-500 text-sm font-medium tracking-wide mt-2">Controle de entradas agrupadas por Nota Fiscal</p>
            </div>
            <div class="flex gap-3">
                <a href="index.php" class="px-4 py-3 bg-white border border-slate-200 rounded-xl font-bold text-slate-600 hover:bg-slate-50 transition text-xs uppercase shadow-sm">
                    <i class="fas fa-home mr-2"></i>Início
                </a>
                <a href="nova_compra.php" class="px-4 py-3 bg-blue-600 text-white rounded-xl font-bold shadow-sm hover:bg-blue-700 transition text-xs uppercase tracking-wide">
                    <i class="fas fa-plus mr-2"></i>Nova Entrada
                </a>
            </div>
        </header>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[950px] text-left border-collapse">
                    <thead class="bg-slate-50/50 text-slate-400 text-[10px] uppercase font-black tracking-[0.2em]">
                        <tr>
                            <th class="px-4 py-4 sm:px-5 sm:py-5">Controle / Fornecedor</th>
                            <th class="px-4 py-4 sm:px-5 sm:py-5">Produtos</th>
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
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="px-4 py-4 sm:px-5 sm:py-5">
                                    <span class="text-blue-600 font-bold text-[10px] block mb-1 uppercase tracking-wide"><?= $cod ?></span>
                                    <span class="font-semibold text-slate-800 block"><?= $row['RazaoSocial'] ?? 'Fornecedor não identificado' ?></span>
                                    <span class="text-[10px] text-slate-400 font-bold italic"><?= date('d/m/Y', strtotime($row['DataCompra'])) ?></span>
                                </td>

                                <td class="px-4 py-4 sm:px-5 sm:py-5">
                                    <div class="max-w-xs text-[11px] text-slate-500 font-medium italic truncate" title="<?= $row['resumo_produtos'] ?>">
                                        <?= $row['resumo_produtos'] ?>
                                    </div>
                                    <span class="text-[9px] bg-slate-100 text-slate-500 px-2 py-0.5 rounded font-black uppercase mt-1 inline-block italic">
                                        <?= $row['FormaPgto'] ?>
                                    </span>
                                </td>

                                <td class="p-6 text-center">
                                    <?php
                                        $nomeArquivoNFLista = basename((string)($arquivo ?? ''));
                                        $arquivoNFListaDisponivel = $nomeArquivoNFLista !== '' &&
                                            is_file(__DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $nomeArquivoNFLista);
                                    ?>

                                    <?php if ($arquivoNFListaDisponivel): ?>
                                        <a href="uploads/<?= rawurlencode($nomeArquivoNFLista) ?>" target="_blank" rel="noopener noreferrer" class="w-10 h-10 inline-flex items-center justify-center bg-indigo-50 text-indigo-600 rounded-xl hover:bg-indigo-600 hover:text-white transition shadow-sm" title="Ver Nota Fiscal">
                                            <i class="fas fa-file-invoice"></i>
                                        </a>
                                    <?php elseif (!empty($arquivo)): ?>
                                        <span class="w-10 h-10 inline-flex items-center justify-center text-slate-300 cursor-not-allowed" title="Nota fiscal não disponível nesta demonstração">
                                            <i class="fas fa-file-circle-xmark"></i>
                                        </span>
                                    <?php else: ?>
                                        <div class="text-slate-200" title="Nenhuma nota fiscal anexada">
                                            <i class="fas fa-file-circle-xmark"></i>
                                        </div>
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
                                    <span class="font-black text-slate-900 text-lg tracking-tight"><?= number_format($row['ValorGeral'], 2, ',', '.') ?></span>
                                </td>

                                <td class="px-4 py-4 sm:px-5 sm:py-5">
                                    <div class="flex justify-center gap-1">

                                        <?php if($status == 'Pendente'): ?>
                                            <form method="POST" action="editar_status_compra.php"
      onsubmit="return confirm('Confirmar o recebimento desta mercadoria? O estoque será atualizado agora.');">
    <input type="hidden" name="cod" value="<?= htmlspecialchars((string) $cod, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_recebimento_compra'], ENT_QUOTES, 'UTF-8') ?>">
    <button type="submit"
            class="w-9 h-9 flex items-center justify-center rounded-xl text-amber-500 hover:bg-emerald-500 hover:text-white transition shadow-sm"
            title="Dar Baixa / Recebido">
        <i class="fas fa-check-double text-sm"></i>
    </button>
</form>
                                        <?php endif; ?>

                                        <a href="visualizar_compra.php?cod=<?= $cod ?>" class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:bg-blue-600 hover:text-white transition shadow-sm" title="Ver Detalhes">
                                            <i class="fas fa-eye text-sm"></i>
                                        </a>

                                        <form method="POST" action="excluir_compra.php"
      onsubmit="return confirm('ATENÇÃO: A exclusão desta compra será permitida somente se ela ainda não tiver sido recebida. Deseja continuar?');">
    <input type="hidden" name="cod" value="<?= htmlspecialchars((string) $cod, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_exclusao_compra'], ENT_QUOTES, 'UTF-8') ?>">
    <button type="submit"
            class="w-9 h-9 flex items-center justify-center rounded-xl text-slate-400 hover:bg-red-500 hover:text-white transition shadow-sm"
            title="Excluir Tudo">
        <i class="fas fa-trash text-sm"></i>
    </button>
</form>
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