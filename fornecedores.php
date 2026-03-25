<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Fornecedores | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans text-gray-900">

    <div class="max-w-6xl mx-auto p-6">
        
        <!-- Alertas de Status -->
        <?php if(isset($_GET['status'])): ?>
            <?php if($_GET['status'] == 'success'): ?>
                <div class="mb-6 p-4 bg-indigo-100 border border-indigo-200 text-indigo-700 rounded-2xl flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span class="font-medium">Fornecedor cadastrado com sucesso!</span>
                </div>
            <?php elseif($_GET['status'] == 'deleted'): ?>
                <div class="mb-6 p-4 bg-red-100 border border-red-200 text-red-700 rounded-2xl flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    <span class="font-medium">Fornecedor removido.</span>
                </div>
            <?php endif; ?>
        <?php endif; ?>      
        
        <!-- Cabeçalho -->
        <header class="flex justify-between items-center mb-10">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">🏭 Fornecedores</h1>
                <p class="text-gray-500">Parceiros e distribuidores do seu estoque</p>
            </div>
            <div class="flex gap-3">
                <a href="index.php" class="bg-white border border-gray-200 text-gray-600 font-bold py-3 px-6 rounded-2xl hover:bg-gray-100 transition-all flex items-center gap-2">
                    Dashboard
                </a>
                <a href="cadastrar_fornecedor.php" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-2xl shadow-lg shadow-indigo-200 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"></path></svg>
                    Novo Fornecedor
                </a>
            </div>
        </header>

        <!-- Tabela -->
        <div class="bg-white rounded-3xl shadow-xl border border-gray-50 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="p-5 text-sm font-bold text-gray-400 uppercase">ID</th>
                        <th class="p-5 text-sm font-bold text-gray-400 uppercase">Razão Social</th>
                        <th class="p-5 text-sm font-bold text-gray-400 uppercase">CNPJ</th>
                        <th class="p-5 text-sm font-bold text-gray-400 uppercase">Contato</th>
                        <th class="p-5 text-sm font-bold text-gray-400 uppercase text-center">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php
                    $sql = "SELECT * FROM Fornecedor ORDER BY idFornecedor DESC";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo '
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="p-5 font-mono text-gray-400 text-sm">#'. $row["idFornecedor"] .'</td>
                                <td class="p-5 font-bold text-gray-800">'. $row["RazaoSocial"] .'</td>
                                <td class="p-5 text-gray-600 font-mono text-sm">'. $row["CNPJ"] .'</td>
                                <td class="p-5 text-sm text-gray-500">
                                    '. $row["Email"] .'<br>
                                    <span class="text-xs font-semibold">'. $row["Telefone"] .'</span>
                                </td>
                                <td class="p-5 text-center flex justify-center gap-2">
                                    <a href="editar_fornecedor.php?id='. $row["idFornecedor"] .'" class="p-2 text-gray-400 hover:text-indigo-600 transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </a>
                                    <a href="excluir_fornecedor.php?id='. $row["idFornecedor"] .'" onclick="return confirm(\'Excluir fornecedor?\')" class="p-2 text-gray-400 hover:text-red-600 transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </a>
                                </td>
                            </tr>';
                        }
                    } else {
                        echo "<tr><td colspan='5' class='p-20 text-center text-gray-500'>Nenhum fornecedor cadastrado.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>