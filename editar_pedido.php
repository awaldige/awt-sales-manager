<?php
session_start();
include 'config.php';
include 'auth.php';

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
    <meta charset="UTF-8">
    <title>Editar Itens | AWTecnology</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 p-6 md:p-12">

    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-end mb-8 px-4">
            <div>
                <a href="pedidos.php" class="text-[10px] font-black uppercase text-slate-400 hover:text-blue-600 transition-all tracking-widest flex items-center gap-2">
                    <i class="fas fa-chevron-left"></i> Voltar aos Pedidos
                </a>
                <h1 class="text-3xl font-black uppercase italic mt-2 text-slate-900">Gerenciar <span class="text-blue-600">Itens</span></h1>
                <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase">Ref: <?= $codigo ?> • Cliente: <?= $pedido_info['Pnome'] ?></p>
            </div>
            
            <form action="atualizar_status.php" method="POST" class="flex items-center gap-3 bg-white p-2 rounded-2xl shadow-sm border border-slate-100">
                <input type="hidden" name="codigoVenda" value="<?= $codigo ?>">
                <select name="novoStatus" class="bg-transparent border-none text-[10px] font-black uppercase text-slate-600 focus:ring-0 cursor-pointer">
                    <option value="Confirmado" <?= $pedido_info['StatusPedido'] == 'Confirmado' ? 'selected' : '' ?>>🟡 Confirmado</option>
                    <option value="Pago" <?= $pedido_info['StatusPedido'] == 'Pago' ? 'selected' : '' ?>>🟢 Pago</option>
                    <option value="Cancelado" <?= $pedido_info['StatusPedido'] == 'Cancelado' ? 'selected' : '' ?>>🔴 Cancelado</option>
                </select>
                <button type="submit" class="bg-blue-600 text-white text-[10px] font-black px-4 py-2 rounded-xl hover:bg-blue-700 transition-all">Salvar Status</button>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <div class="lg:col-span-2 space-y-4">
                <h2 class="text-[10px] font-black uppercase tracking-widest text-slate-400 ml-4 mb-2">Produtos no Pedido</h2>
                
                <?php foreach($itens as $item): ?>
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-slate-100 flex justify-between items-center group hover:border-blue-200 transition-all">
                    <div class="flex items-center gap-5">
                        <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center text-blue-600 font-black italic">
                            <?= $item['quantidade'] ?>x
                        </div>
                        <div>
                            <p class="font-black text-slate-800 italic"><?= $item['Descricao'] ?></p>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">Vlr. Unitário: R$ <?= number_format($item['ValorTotal'] / $item['quantidade'], 2, ',', '.') ?></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-6">
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
                <div class="bg-slate-900 p-8 rounded-[3rem] shadow-2xl text-white sticky top-8">
                    <h3 class="text-xs font-black uppercase tracking-widest mb-6 italic text-blue-400">Adicionar Produto</h3>
                    
                    <form action="incluir_item_edicao.php" method="POST" class="space-y-5">
                        <input type="hidden" name="codigoVenda" value="<?= $codigo ?>">
                        <input type="hidden" name="idCliente" value="<?= $pedido_info['idCliente'] ?>">
                        <input type="hidden" name="status" value="<?= $pedido_info['StatusPedido'] ?>">

                        <div>
                            <label class="block text-[9px] font-black uppercase text-slate-500 mb-2 ml-1">Produto Disponível</label>
                            <select name="idProduto" required class="w-full bg-slate-800 border-none rounded-2xl p-4 text-xs font-bold text-white outline-none focus:ring-2 focus:ring-blue-500 transition-all">
                                <option value="">Selecione...</option>
                                <?php while($p = $lista_produtos->fetch_assoc()): ?>
                                    <option value="<?= $p['idProduto'] ?>"><?= $p['Descricao'] ?> (R$ <?= $p['Valor'] ?>)</option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[9px] font-black uppercase text-slate-500 mb-2 ml-1">Quantidade</label>
                            <input type="number" name="quantidade" value="1" min="1" class="w-full bg-slate-800 border-none rounded-2xl p-4 text-xs font-bold text-white outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 py-4 rounded-2xl font-black uppercase text-[10px] tracking-[0.2em] transition-all shadow-lg shadow-blue-900/20">
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