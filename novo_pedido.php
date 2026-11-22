<?php
require_once 'auth.php';
require_once 'config.php';

if (empty($_SESSION['csrf_nova_venda'])) {
    $_SESSION['csrf_nova_venda'] = bin2hex(random_bytes(32));
}

$erro = $_SESSION['erro_venda'] ?? null;
unset($_SESSION['erro_venda']);

$clientes = $conn->query(
    "SELECT idCliente, Pnome, Sobrenome FROM cliente ORDER BY Pnome ASC"
);

$formas_pgto = $conn->query(
    "SELECT idFormaPagamento, Tipo FROM formapagamento ORDER BY Tipo ASC"
);

$produtos_json = [];
$res_produtos = $conn->query(
    "SELECT idProduto, Descricao, precoVenda, estoque
     FROM produtos
     WHERE estoque > 0
     ORDER BY Descricao ASC"
);

while ($produto = $res_produtos->fetch_assoc()) {
    $produtos_json[] = [
        'idProduto' => (int) $produto['idProduto'],
        'Descricao' => $produto['Descricao'],
        'precoVenda' => (float) $produto['precoVenda'],
        'estoque' => (int) $produto['estoque']
    ];
}

$jsonProdutos = json_encode(
    $produtos_json,
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Venda | AWT Sales Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 p-4 sm:p-6 lg:p-8 text-slate-800">

    <main class="max-w-5xl mx-auto">
        <header class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
            <div>
                <a href="pedidos.php" class="text-xs font-bold uppercase tracking-wider text-slate-400 hover:text-blue-600">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar aos pedidos
                </a>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-slate-900 mt-3">
                    Nova <span class="text-blue-600">Venda</span>
                </h1>
                <p class="text-sm text-slate-500 mt-1">Registre uma venda com um ou mais produtos.</p>
            </div>
            <a href="index.php" class="bg-white border border-slate-200 rounded-xl px-5 py-3 text-xs font-bold text-slate-600 hover:bg-slate-100">
                <i class="fas fa-house mr-2"></i> Painel
            </a>
        </header>

        <?php if ($erro): ?>
            <div class="mb-6 p-4 rounded-xl border border-red-200 bg-red-50 text-red-700 text-sm font-semibold">
                <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form action="processar_venda.php" method="POST" id="form-venda">
            <input type="hidden" name="csrf_token"
                   value="<?= htmlspecialchars($_SESSION['csrf_nova_venda'], ENT_QUOTES, 'UTF-8') ?>">

            <section class="bg-white p-5 md:p-7 rounded-2xl shadow-sm border border-slate-200 mb-6">
                <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-4">Dados da venda</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="idCliente" class="block text-xs font-bold text-slate-600 mb-2">Cliente</label>
                        <select name="idCliente" id="idCliente" required
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 text-sm font-semibold text-slate-700">
                            <option value="">Selecione o cliente...</option>
                            <?php while ($cliente = $clientes->fetch_assoc()): ?>
                                <option value="<?= (int) $cliente['idCliente'] ?>">
                                    <?= htmlspecialchars($cliente['Pnome'] . ' ' . $cliente['Sobrenome'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div>
                        <label for="idFormaPagamento" class="block text-xs font-bold text-slate-600 mb-2">Forma de pagamento</label>
                        <select name="idFormaPagamento" id="idFormaPagamento" required
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 text-sm font-semibold text-slate-700">
                            <option value="">Selecione a forma de pagamento...</option>
                            <?php while ($forma = $formas_pgto->fetch_assoc()): ?>
                                <option value="<?= (int) $forma['idFormaPagamento'] ?>">
                                    <?= htmlspecialchars($forma['Tipo'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </section>

            <section class="bg-white p-5 md:p-7 rounded-2xl shadow-sm border border-slate-200">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div>
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-500">Produtos da venda</h2>
                        <p class="text-xs text-slate-400 mt-1">Adicione os produtos e suas respectivas quantidades.</p>
                    </div>
                    <button type="button" id="adicionar-item"
                            class="bg-blue-50 text-blue-700 hover:bg-blue-100 px-4 py-3 rounded-xl text-xs font-bold">
                        <i class="fas fa-plus mr-2"></i>Adicionar produto
                    </button>
                </div>

                <div id="container-produtos" class="space-y-4"></div>

                <div class="mt-8 pt-6 border-t border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <span class="block text-xs font-bold uppercase text-slate-500">Total estimado</span>
                        <span class="text-3xl font-extrabold text-slate-900">
                            R$ <span id="valorTotalExibido">0,00</span>
                        </span>
                        <p class="text-xs text-slate-400 mt-1">O valor definitivo será calculado pelo servidor.</p>
                    </div>
                    <button type="submit"
                            class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-8 py-4 rounded-xl font-bold uppercase text-xs tracking-wider shadow-lg shadow-blue-100">
                        <i class="fas fa-check-circle mr-2"></i>Finalizar venda
                    </button>
                </div>
            </section>
        </form>
    </main>

    <script>
        const produtosDB = <?= $jsonProdutos ?: '[]' ?>;
        const container = document.getElementById('container-produtos');
        const totalExibido = document.getElementById('valorTotalExibido');

        function formatarMoeda(valor) {
            return Number(valor).toLocaleString('pt-BR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function calcularTotal() {
            let total = 0;

            container.querySelectorAll('.linha-produto').forEach(linha => {
                const select = linha.querySelector('select');
                const quantidade = Number(linha.querySelector('input').value);
                const produto = produtosDB.find(p => p.idProduto === Number(select.value));

                if (produto && Number.isInteger(quantidade) && quantidade > 0) {
                    total += produto.precoVenda * quantidade;
                }

                linha.querySelector('.subtotal').textContent =
                    'R$ ' + formatarMoeda(
                        produto && quantidade > 0 ? produto.precoVenda * quantidade : 0
                    );
            });

            totalExibido.textContent = formatarMoeda(total);
        }

        function adicionarLinha() {
            const linha = document.createElement('div');
            linha.className = 'linha-produto grid grid-cols-1 sm:grid-cols-[1fr_110px_130px_40px] gap-3 items-end p-4 bg-slate-50 border border-slate-200 rounded-xl';

            const grupoProduto = document.createElement('div');
            const labelProduto = document.createElement('label');
            labelProduto.className = 'block text-xs font-bold text-slate-600 mb-2';
            labelProduto.textContent = 'Produto';

            const select = document.createElement('select');
            select.name = 'produtos[]';
            select.required = true;
            select.className = 'w-full bg-white border border-slate-200 rounded-xl p-3 text-sm font-semibold text-slate-700';
            select.add(new Option('Selecione um produto...', ''));

            produtosDB.forEach(produto => {
                const opcao = new Option(
                    `${produto.Descricao} (Estoque: ${produto.estoque})`,
                    produto.idProduto
                );
                select.add(opcao);
            });

            grupoProduto.append(labelProduto, select);

            const grupoQuantidade = document.createElement('div');
            const labelQuantidade = document.createElement('label');
            labelQuantidade.className = 'block text-xs font-bold text-slate-600 mb-2';
            labelQuantidade.textContent = 'Quantidade';

            const input = document.createElement('input');
            input.type = 'number';
            input.name = 'quantidades[]';
            input.min = '1';
            input.max = '9999999';
            input.value = '1';
            input.required = true;
            input.className = 'w-full bg-white border border-slate-200 rounded-xl p-3 text-sm font-semibold text-center';

            grupoQuantidade.append(labelQuantidade, input);

            const grupoSubtotal = document.createElement('div');
            const labelSubtotal = document.createElement('span');
            labelSubtotal.className = 'block text-xs font-bold text-slate-600 mb-2';
            labelSubtotal.textContent = 'Subtotal';

            const subtotal = document.createElement('div');
            subtotal.className = 'subtotal font-bold text-blue-600 p-3';
            subtotal.textContent = 'R$ 0,00';

            grupoSubtotal.append(labelSubtotal, subtotal);

            const remover = document.createElement('button');
            remover.type = 'button';
            remover.className = 'h-10 rounded-lg bg-white text-slate-400 hover:bg-red-500 hover:text-white';
            remover.title = 'Remover produto';
            remover.innerHTML = '<i class="fas fa-times"></i>';
            remover.addEventListener('click', () => {
                linha.remove();
                calcularTotal();
            });

            select.addEventListener('change', calcularTotal);
            input.addEventListener('input', calcularTotal);

            linha.append(grupoProduto, grupoQuantidade, grupoSubtotal, remover);
            container.appendChild(linha);
            calcularTotal();
        }

        document.getElementById('adicionar-item').addEventListener('click', adicionarLinha);

        document.getElementById('form-venda').addEventListener('submit', event => {
            if (container.querySelectorAll('.linha-produto').length === 0) {
                event.preventDefault();
                alert('Adicione pelo menos um produto à venda.');
            }
        });

        adicionarLinha();
    </script>
</body>
</html>