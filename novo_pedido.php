<?php
session_start();
include 'config.php';
include 'auth.php';

// 1. Busca clientes
$clientes = $conn->query("SELECT idCliente, Pnome, Sobrenome FROM cliente ORDER BY Pnome ASC");

// 2. Busca produtos (Ajustado para suas colunas reais: idProduto, Descricao, precoVenda, estoque)
$produtos_json = [];
try {
    $res_produtos = $conn->query("SELECT idProduto, Descricao, precoVenda, estoque FROM produtos WHERE estoque > 0 ORDER BY Descricao ASC");
    while($p = $res_produtos->fetch_assoc()){
        $produtos_json[] = $p;
    }
} catch (mysqli_sql_exception $e) {
    die("Erro: Verifique se a tabela se chama 'produtos' ou 'produto'. Erro atual: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Novo Pedido | AWTecnology</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 p-6 md:p-12 text-slate-800">

    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-10">
            <a href="pedidos.php" class="text-slate-400 hover:text-blue-600 transition-all flex items-center gap-2 font-black uppercase text-[10px] tracking-widest">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
            <h1 class="text-2xl font-black uppercase italic text-slate-900">Novo <span class="text-blue-600">Pedido</span></h1>
        </div>

        <form action="processar_venda.php" method="POST">
            <div class="bg-white p-8 rounded-[2.5rem] shadow-xl border border-slate-100 mb-6">
                <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest mb-4">Cliente</label>
                <select name="idCliente" required class="w-full bg-slate-50 border-none rounded-2xl p-4 font-bold text-slate-700 focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                    <option value="">Selecione o cliente...</option>
                    <?php foreach($clientes as $c): ?>
                        <option value="<?= $c['idCliente'] ?>"><?= $c['Pnome'] ?> <?= $c['Sobrenome'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="bg-white p-8 rounded-[2.5rem] shadow-xl border border-slate-100">
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-[10px] font-black uppercase tracking-widest text-slate-400">Produtos</h2>
                    <button type="button" onclick="adicionarLinha()" class="text-blue-600 font-black text-[10px] uppercase tracking-widest hover:underline">
                        + Adicionar Item
                    </button>
                </div>

                <div id="container-produtos" class="space-y-4"></div>

                <div class="mt-10 pt-8 border-t border-slate-50 flex justify-between items-center">
                    <div>
                        <span class="block text-[10px] font-black uppercase text-slate-400 tracking-widest">Total</span>
                        <span class="text-3xl font-black italic text-slate-900">R$ <span id="valorTotalExibido">0,00</span></span>
                        <input type="hidden" name="valorFinalTotal" id="inputTotalOculto" value="0">
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-10 py-5 rounded-2xl font-black uppercase text-xs tracking-widest shadow-xl shadow-blue-100 hover:bg-blue-700 transition-all">
                        Finalizar Venda
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        const produtosDB = <?= json_encode($produtos_json) ?>;
        let contador = 0;

        function adicionarLinha() {
            const container = document.getElementById('container-produtos');
            const id = `item-${contador}`;
            const html = `
                <div id="${id}" class="flex gap-4 p-4 bg-slate-50 rounded-2xl items-end">
                    <div class="flex-1">
                        <select name="produtos[]" onchange="atualizarPreco(this)" required class="w-full bg-white border-none rounded-xl p-3 font-bold text-slate-700 shadow-sm">
                            <option value="">Produto...</option>
                            ${produtosDB.map(p => `<option value="${p.idProduto}" data-preco="${p.precoVenda}">${p.Descricao} (Qtd: ${p.estoque})</option>`).join('')}
                        </select>
                    </div>
                    <div class="w-20">
                        <input type="number" name="quantidades[]" value="1" min="1" oninput="calcularTotal()" class="w-full bg-white border-none rounded-xl p-3 font-bold text-center">
                    </div>
                    <div class="w-24 font-black text-blue-600 preco-display">R$ 0,00</div>
                    <button type="button" onclick="document.getElementById('${id}').remove(); calcularTotal();" class="text-slate-300 hover:text-red-500"><i class="fas fa-times"></i></button>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            contador++;
        }

        function atualizarPreco(select) {
            const preco = select.options[select.selectedIndex].dataset.preco || 0;
            select.closest('div.flex').querySelector('.preco-display').innerText = 'R$ ' + parseFloat(preco).toLocaleString('pt-br', {minimumFractionDigits: 2});
            calcularTotal();
        }

        function calcularTotal() {
            let total = 0;
            document.querySelectorAll('#container-produtos > div').forEach(linha => {
                const select = linha.querySelector('select');
                const preco = parseFloat(select.options[select.selectedIndex]?.dataset.preco || 0);
                const qtd = parseInt(linha.querySelector('input[type="number"]').value || 0);
                total += (preco * qtd);
            });
            document.getElementById('valorTotalExibido').innerText = total.toLocaleString('pt-br', {minimumFractionDigits: 2});
            document.getElementById('inputTotalOculto').value = total.toFixed(2);
        }

        adicionarLinha();
    </script>
</body>
</html>