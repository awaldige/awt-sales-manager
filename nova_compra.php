<?php
include 'auth.php';
include 'config.php';

// --- PROCESSAMENTO DO FORMULÁRIO ---
if (isset($_POST['btn_salvar'])) {
    // 1. GERAR UM CÓDIGO ÚNICO PARA ESTA SESSÃO DE COMPRA
    $codigoAgrupador = "COMPRA-" . date('Ymd') . "-" . strtoupper(substr(uniqid(), -4)); 
    
    $idFornecedor   = $_POST['idFornecedor'];
    $idFormaPgto    = $_POST['idFormaPagamento'];
    $status         = $_POST['status'];
    $dataCompra     = $_POST['dataCompra'];
    
    // Arrays vindos do formulário dinâmico
    $produtos_ids   = $_POST['idProduto']; 
    $quantidades    = $_POST['quantidade']; 
    $valores        = $_POST['valorUnitario']; 

    $nomeArquivo = null;

    // --- UPLOAD DA NOTA FISCAL (Único para o lote todo) ---
    if (isset($_FILES['arquivo_nf']) && $_FILES['arquivo_nf']['error'] === 0) {
        $extensao = pathinfo($_FILES['arquivo_nf']['name'], PATHINFO_EXTENSION);
        $nomeArquivo = "NF_" . time() . "_" . uniqid() . "." . $extensao;
        $diretorio = "uploads/";
        if (!is_dir($diretorio)) mkdir($diretorio, 0777, true);
        move_uploaded_file($_FILES['arquivo_nf']['tmp_name'], $diretorio . $nomeArquivo);
    }

    $conn->begin_transaction();

    try {
        // Percorre cada produto da lista e salva com o MESMO codigo_controle
        foreach ($produtos_ids as $key => $idProduto) {
            $qty = (int)$quantidades[$key];
            $vlr = (float)$valores[$key];
            $totalLinha = $qty * $vlr;

            $sql = "INSERT INTO compra (codigo_controle, idProduto, idFornecedor, idFormaPagamento, quantidade, ValorTotal, DataCompra, StatusCompra, arquivo_nf) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("siiiidsss", $codigoAgrupador, $idProduto, $idFornecedor, $idFormaPgto, $qty, $totalLinha, $dataCompra, $status, $nomeArquivo);
            $stmt->execute();

            // Atualiza estoque apenas se estiver "Recebido"
            if ($status == 'Recebido') {
                $upd = $conn->prepare("UPDATE produtos SET estoque = estoque + ?, preco_custo_atual = ? WHERE idProduto = ?");
                $upd->bind_param("idi", $qty, $vlr, $idProduto);
                $upd->execute();
            }
        }

        $conn->commit();
        header("Location: compra.php?sucesso=1");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $erro = "Erro crítico: " . $e->getMessage();
    }
}

// Busca dados para os campos Select
$resProdutos = $conn->query("SELECT idProduto, Descricao FROM produtos ORDER BY Descricao ASC");
$listaProdutos = $resProdutos->fetch_all(MYSQLI_ASSOC);
$fornecedores = $conn->query("SELECT idFornecedor, RazaoSocial FROM fornecedor ORDER BY RazaoSocial ASC");
$formas = $conn->query("SELECT idFormaPagamento, Tipo FROM formapagamento ORDER BY Tipo ASC");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Entrada de Mercadoria | ERP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="p-4 md:p-10">

    <div class="max-w-5xl mx-auto bg-white rounded-[2.5rem] shadow-2xl shadow-slate-200 border border-slate-100 overflow-hidden">
        
        <form method="POST" enctype="multipart/form-data">
            <div class="p-8 md:p-12 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
                <div>
                    <h1 class="text-3xl font-black text-slate-800 uppercase italic tracking-tighter">Nova Compra em Lote</h1>
                    <p class="text-slate-400 text-xs font-bold uppercase tracking-[0.2em]">Vincule múltiplos produtos a uma única NF</p>
                </div>
                <a href="compra.php" class="text-slate-300 hover:text-red-500 transition-all text-2xl"><i class="fas fa-times-circle"></i></a>
            </div>

            <div class="p-8 md:p-12 space-y-10">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">Fornecedor</label>
                        <select name="idFornecedor" required class="w-full p-4 bg-slate-100 border-none rounded-2xl font-bold text-slate-700 focus:ring-4 focus:ring-blue-100 transition outline-none">
                            <option value="">Selecione...</option>
                            <?php foreach($fornecedores as $f): ?>
                                <option value="<?= $f['idFornecedor'] ?>"><?= $f['RazaoSocial'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">Forma Pagto</label>
                        <select name="idFormaPagamento" required class="w-full p-4 bg-slate-100 border-none rounded-2xl font-bold text-slate-700 focus:ring-4 focus:ring-blue-100 transition outline-none">
                            <?php foreach($formas as $fp): ?>
                                <option value="<?= $fp['idFormaPagamento'] ?>"><?= $fp['Tipo'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest ml-2">Data Entrada</label>
                        <input type="date" name="dataCompra" value="<?= date('Y-m-d') ?>" class="w-full p-4 bg-slate-100 border-none rounded-2xl font-bold text-slate-700 outline-none">
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex justify-between items-center border-b border-slate-100 pb-4">
                        <h2 class="text-sm font-black text-slate-800 uppercase italic">Produtos da Nota</h2>
                        <button type="button" onclick="adicionarLinha()" class="bg-slate-900 text-white px-5 py-2 rounded-xl text-[10px] font-black uppercase hover:bg-blue-600 transition shadow-lg">
                            <i class="fas fa-plus mr-2"></i> Adicionar Item
                        </button>
                    </div>

                    <div id="container-produtos" class="space-y-4">
                        <div class="linha-produto grid grid-cols-1 md:grid-cols-12 gap-4 bg-slate-50 p-6 rounded-3xl border border-white shadow-sm relative transition-all hover:shadow-md">
                            <div class="md:col-span-6">
                                <label class="text-[9px] font-bold text-slate-400 ml-1 uppercase">Produto</label>
                                <select name="idProduto[]" required class="w-full p-3 bg-white border border-slate-200 rounded-xl font-bold text-slate-600 outline-none">
                                    <option value="">Selecione...</option>
                                    <?php foreach($listaProdutos as $p): ?>
                                        <option value="<?= $p['idProduto'] ?>"><?= $p['Descricao'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-[9px] font-bold text-slate-400 ml-1 uppercase">Qtd</label>
                                <input type="number" name="quantidade[]" required min="1" placeholder="0" class="w-full p-3 bg-white border border-slate-200 rounded-xl font-black text-blue-600 outline-none">
                            </div>
                            <div class="md:col-span-3">
                                <label class="text-[9px] font-bold text-slate-400 ml-1 uppercase">Vlr Unitário</label>
                                <input type="number" name="valorUnitario[]" step="0.01" required placeholder="0.00" class="w-full p-3 bg-white border border-slate-200 rounded-xl font-black text-emerald-600 outline-none">
                            </div>
                            <div class="md:col-span-1 flex items-end justify-center">
                                 <button type="button" onclick="removerLinha(this)" class="w-10 h-10 flex items-center justify-center rounded-xl text-slate-300 hover:bg-red-50 hover:text-red-500 transition">
                                    <i class="fas fa-trash-alt"></i>
                                 </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-6">
                    <div class="p-6 bg-blue-50/50 rounded-3xl border border-blue-100/50">
                        <label class="block text-[10px] font-black uppercase text-blue-400 mb-4 tracking-widest">Anexar XML ou PDF da Nota</label>
                        <input type="file" name="arquivo_nf" class="text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-[10px] file:font-black file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                    </div>

                    <div class="p-6 bg-slate-50 rounded-3xl border border-slate-100">
                        <label class="block text-[10px] font-black uppercase text-slate-400 mb-4 tracking-widest text-center">Status do Recebimento</label>
                        <div class="flex gap-4">
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="status" value="Pendente" checked class="hidden peer">
                                <div class="p-3 text-center rounded-xl border-2 border-white bg-white shadow-sm font-bold text-slate-300 peer-checked:border-amber-400 peer-checked:text-amber-600 transition-all text-[10px]">📦 PENDENTE</div>
                            </label>
                            <label class="flex-1 cursor-pointer">
                                <input type="radio" name="status" value="Recebido" class="hidden peer">
                                <div class="p-3 text-center rounded-xl border-2 border-white bg-white shadow-sm font-bold text-slate-300 peer-checked:border-emerald-500 peer-checked:text-emerald-600 transition-all text-[10px]">✅ RECEBIDO</div>
                            </label>
                        </div>
                    </div>
                </div>

                <button name="btn_salvar" class="w-full bg-slate-900 text-white py-6 rounded-[2rem] font-black uppercase tracking-[0.4em] shadow-2xl hover:bg-blue-600 transition-all transform hover:-translate-y-1 active:scale-95">
                    Finalizar Entrada de Estoque
                </button>
            </div>
        </form>
    </div>

    <script>
    function adicionarLinha() {
        const container = document.getElementById('container-produtos');
        const novaLinha = container.firstElementChild.cloneNode(true);
        novaLinha.querySelectorAll('input').forEach(i => i.value = '');
        novaLinha.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
        container.appendChild(novaLinha);
    }

    function removerLinha(btn) {
        const container = document.getElementById('container-produtos');
        if (container.children.length > 1) {
            btn.closest('.linha-produto').remove();
        } else {
            alert("Mínimo de um item por compra!");
        }
    }
    </script>
</body>
</html>