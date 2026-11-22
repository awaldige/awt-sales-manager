<?php
require_once 'auth.php';
require_once 'config.php';

if (empty($_SESSION['csrf_status_pedido'])) {
    $_SESSION['csrf_status_pedido'] = bin2hex(random_bytes(32));
}

$codigo = isset($_GET['cod']) ? $_GET['cod'] : die("Código não fornecido.");

// 1. Busca todos os itens individuais deste pedido
$stmt = $conn->prepare("SELECT p.*, c.Pnome FROM pedido p LEFT JOIN cliente c ON p.idCliente = c.idCliente WHERE p.codigoVenda = ?");
$stmt->bind_param("s", $codigo);
$stmt->execute();
$itens = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (empty($itens)) die("Pedido não encontrado.");

$pedido_info = $itens[0]; // Dados base para o cabeçalho

// 2. Busca todos os produtos para o select de "Adicionar Novo"
$lista_produtos = $conn->query("SELECT idProduto, Descricao, Valor, estoque FROM produtos WHERE estoque > 0 ORDER BY Descricao ASC");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta charset="UTF-8">
    <title>Editar Itens | AWTecnology</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 p-4 sm:p-6 lg:p-8">

    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8 gap-6">
            <div>
                <a href="pedidos.php" class="text-[10px] font-black uppercase text-slate-400 hover:text-blue-600 transition-all tracking-widest flex items-center gap-2">
                    <i class="fas fa-chevron-left"></i> Voltar aos Pedidos
                </a>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight mt-3 text-slate-900">Gerenciar <span class="text-blue-600">Itens</span></h1>
                <p class="text-xs font-semibold text-slate-500 mt-2 uppercase tracking-wide">Ref: <?= $codigo ?> • Cliente: <?= $pedido_info['Pnome'] ?></p>
            </div>
            
            <form action="atualizar_status.php" method="POST" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 bg-white p-3 rounded-2xl shadow-sm border border-slate-200 w-full lg:w-auto">
                <input type="hidden" name="codigoVenda" value="<?= $codigo ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_status_pedido'], ENT_QUOTES, 'UTF-8') ?>">
                <select name="novoStatus" class="bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:ring-2 focus:ring-blue-500 cursor-pointer px-4 py-3 flex-1">
                    <option value="Confirmado" <?= $pedido_info['StatusPedido'] == 'Confirmado' ? 'selected' : '' ?>>🟡 Confirmado</option>
                    <option value="Em processamento" <?= $pedido_info['StatusPedido'] == 'Em processamento' ? 'selected' : '' ?>>Em processamento</option>
                    <option value="Cancelado" <?= $pedido_info['StatusPedido'] == 'Cancelado' ? 'selected' : '' ?>>🔴 Cancelado</option>
                </select>
                <button type="submit" class="bg-blue-600 text-white text-xs font-bold px-5 py-3 rounded-xl hover:bg-blue-700 transition-all whitespace-nowrap">Salvar Status</button>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">
            
            <div class="lg:col-span-2 space-y-4">
                <h2 class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-4 mb-2">Produtos no Pedido</h2>
                
                <?php foreach($itens as $item): ?>
                <div class="bg-white p-5 md:p-6 rounded-2xl shadow-sm border border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 hover:border-blue-200 hover:shadow-md transition-all">
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center text-blue-600 font-black italic">
                            <?= $item['quantidade'] ?>x
                        </div>
                        <div>
                            <p class="font-bold text-slate-800"><?= $item['Descricao'] ?></p>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">Vlr. Unitário: R$ <?= number_format($item['ValorTotal'] / $item['quantidade'], 2, ',', '.') ?></p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between sm:justify-end gap-4 w-full sm:w-auto">
                        <p class="font-black text-slate-900 italic">R$ <?= number_format($item['ValorTotal'], 2, ',', '.') ?></p>
                        <a href="remover_item_edicao.php?id=<?= $item['idPedido'] ?>&cod=<?= $codigo ?>&desc=<?= urlencode($item['Descricao']) ?>&qtd=<?= $item['quantidade'] ?>" 
                           onclick="return confirm('Excluir este item e devolver ao estoque?')"
                           class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-50 text-red-400 hover:bg-red-500 hover:text-white transition-all">
                            <i class="fas fa-trash-alt text-xs"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-slate-900 p-6 md:p-8 rounded-2xl shadow-xl text-white lg:sticky lg:top-8">
                    <h3 class="text-sm font-bold uppercase tracking-wider mb-6 text-blue-400">Adicionar Produto</h3>
                    
                    <form action="incluir_item_edicao.php" method="POST" class="space-y-5">
                        <input type="hidden" name="codigoVenda" value="<?= $codigo ?>">
                        <input type="hidden" name="idCliente" value="<?= $pedido_info['idCliente'] ?>">
                        <input type="hidden" name="status" value="<?= $pedido_info['StatusPedido'] ?>">

                        <div>
                            <label class="block text-[9px] font-black uppercase text-slate-500 mb-2 ml-1">Produto Disponível</label>
                            <select name="idProduto" required class="w-full bg-slate-800 border border-slate-700 rounded-xl p-4 text-sm font-semibold text-white outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                                <option value="">Selecione...</option>
                                <?php while($p = $lista_produtos->fetch_assoc()): ?>
                                    <option value="<?= $p['idProduto'] ?>"><?= $p['Descricao'] ?> (R$ <?= $p['Valor'] ?>)</option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[9px] font-black uppercase text-slate-500 mb-2 ml-1">Quantidade</label>
                            <input type="number" name="quantidade" value="1" min="1" class="w-full bg-slate-800 border border-slate-700 rounded-xl p-4 text-sm font-semibold text-white outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 py-4 rounded-xl font-bold uppercase text-xs tracking-wider transition-all shadow-lg shadow-blue-900/20">
                            <i class="fas fa-plus mr-2"></i> Incluir no Pedido
                        </button>
                    </form>
                    
                    <p class="text-[8px] text-center text-slate-500 uppercase font-black mt-8 tracking-widest">AWTecnology System</p>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
