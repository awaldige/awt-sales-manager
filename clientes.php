<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Clientes | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans text-gray-900">

    <div class="max-w-6xl mx-auto p-6">
        
        <!-- Alertas de Status -->
        <?php if(isset($_GET['status'])): ?>
            <?php if($_GET['status'] == 'success'): ?>
                <div class="mb-6 p-4 bg-green-100 border border-green-200 text-green-700 rounded-2xl flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span class="font-medium">Cliente cadastrado com sucesso!</span>
                </div>
            <?php elseif($_GET['status'] == 'deleted'): ?>
                <div class="mb-6 p-4 bg-red-100 border border-red-200 text-red-700 rounded-2xl flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    <span class="font-medium">Cliente removido do sistema.</span>
                </div>
            <?php elseif($_GET['status'] == 'updated'): ?>
                <div class="mb-6 p-4 bg-blue-100 border border-blue-200 text-blue-700 rounded-2xl flex items-center gap-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    <span class="font-medium">Dados do cliente atualizados!</span>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Cabeçalho -->
        <header class="flex justify-between items-center mb-10">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-800 tracking-tight">👥 Clientes Cadastrados</h1>
                <p class="text-gray-500">Gerencie a base de usuários do seu sistema</p>
            </div>
            <div class="flex gap-3">
                <a href="index.php" class="bg-white border border-gray-200 text-gray-600 font-bold py-3 px-6 rounded-2xl hover:bg-gray-100 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Dashboard
                </a>
                <a href="cadastrar_cliente.php" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-6 rounded-2xl shadow-lg shadow-purple-200 transition-all flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Novo Cliente
                </a>
            </div>
        </header>

        <!-- Tabela de Clientes -->
        <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-50 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="p-5 text-sm font-bold text-gray-400 uppercase tracking-wider">ID</th>
                        <th class="p-5 text-sm font-bold text-gray-400 uppercase tracking-wider">Nome Completo</th>
                        <th class="p-5 text-sm font-bold text-gray-400 uppercase tracking-wider">Documento</th>
                        <th class="p-5 text-sm font-bold text-gray-400 uppercase tracking-wider">Tipo</th>
                        <th class="p-5 text-sm font-bold text-gray-400 uppercase tracking-wider text-center">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php
                    $sql = "SELECT * FROM Cliente ORDER BY idCliente DESC";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            $nomeExibir = trim($row["Pnome"] . " " . $row["NomeMeio"] . " " . $row["Sobrenome"]);
                            $nomeExibir = str_replace("  ", " ", $nomeExibir);

                            $isPF = ($row["Tipo"] == "PF");
                            $tipoCor = $isPF ? "bg-purple-50 text-purple-600" : "bg-orange-50 text-orange-600";
                            $tipoNome = $isPF ? "Pessoa Física" : "Pessoa Jurídica";

                            echo '
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="p-5 font-mono text-gray-400 text-sm">#'. $row["idCliente"] .'</td>
                                <td class="p-5 font-bold text-gray-800">'. $nomeExibir .'</td>
                                <td class="p-5 text-gray-600 font-mono text-sm">'. $row["Documento"] .'</td>
                                <td class="p-5">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold '. $tipoCor .'">
                                        '. $tipoNome .'
                                    </span>
                                </td>
                                <td class="p-5 text-center flex justify-center gap-2">
                                    <!-- Editar -->
                                    <a href="editar_cliente.php?id='. $row["idCliente"] .'" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </a>
                                    <!-- Excluir -->
                                    <a href="excluir_cliente.php?id='. $row["idCliente"] .'" 
                                       onclick="return confirm(\'Tem certeza que deseja excluir '. $nomeExibir .'?\')"
                                       class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </a>
                                </td>
                            </tr>';
                        }
                    } else {
                        echo "<tr><td colspan='5' class='p-20 text-center text-gray-500 font-medium'>Nenhum cliente cadastrado no momento.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>