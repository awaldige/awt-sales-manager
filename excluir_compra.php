<?php
include 'auth.php';
include 'config.php';

// Exclusão de compras: somente administradores.
restringirAdmin();

// Bloqueia qualquer acesso que não seja POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: compra.php?erro=metodo');
    exit;
}

// Validação do token CSRF.
$token = $_POST['csrf_token'] ?? '';
$tokenSessao = $_SESSION['csrf_exclusao_compra'] ?? '';

if (
    !is_string($token) ||
    !is_string($tokenSessao) ||
    $token === '' ||
    $tokenSessao === '' ||
    !hash_equals($tokenSessao, $token)
) {
    header('Location: compra.php?erro=seguranca');
    exit;
}

// Validação do código recebido.
$cod = $_POST['cod'] ?? '';

if (!is_string($cod) || $cod === '' || strlen($cod) > 60) {
    header('Location: compra.php?erro=dados');
    exit;
}

// Identifica compras antigas (AVULSO) ou lotes novos.
if (preg_match('/^AVULSO-([1-9][0-9]*)$/D', $cod, $matches)) {
    $idAntigo = filter_var(
        $matches[1],
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($idAntigo === false) {
        header('Location: compra.php?erro=dados');
        exit;
    }

    $sqlBusca = "SELECT idCompra, StatusCompra
                 FROM compra
                 WHERE idCompra = ?
                 FOR UPDATE";

    $sqlDelete = "DELETE FROM compra WHERE idCompra = ?";
    $tipoParametro = 'i';
    $parametro = $idAntigo;
} else {
    if (!preg_match('/^[A-Za-z0-9_-]{1,50}$/D', $cod)) {
        header('Location: compra.php?erro=dados');
        exit;
    }

    $sqlBusca = "SELECT idCompra, StatusCompra
                 FROM compra
                 WHERE codigo_controle = ?
                 FOR UPDATE";

    $sqlDelete = "DELETE FROM compra WHERE codigo_controle = ?";
    $tipoParametro = 's';
    $parametro = $cod;
}

$transacaoIniciada = false;

try {
    $conn->begin_transaction();
    $transacaoIniciada = true;

    // Bloqueia os registros durante a validação.
    $busca = $conn->prepare($sqlBusca);
    $busca->bind_param($tipoParametro, $parametro);
    $busca->execute();

    $resultado = $busca->get_result();

    if ($resultado->num_rows === 0) {
        throw new RuntimeException('Compra não localizada.');
    }

    // Compras recebidas ou canceladas não podem ser excluídas.
    while ($compra = $resultado->fetch_assoc()) {
        if ($compra['StatusCompra'] !== 'Pendente') {
            throw new RuntimeException('Compra não pode ser excluída.');
        }
    }

    $busca->close();

    // Exclui somente os registros pendentes.
    $exclusao = $conn->prepare($sqlDelete);
    $exclusao->bind_param($tipoParametro, $parametro);
    $exclusao->execute();

    if ($exclusao->affected_rows < 1) {
        throw new RuntimeException('Nenhum registro foi excluído.');
    }

    $exclusao->close();

    $conn->commit();
    $transacaoIniciada = false;

    header('Location: compra.php?exclusao=sucesso');
    exit;

} catch (Throwable $e) {
    if ($transacaoIniciada) {
        $conn->rollback();
    }

    error_log('Falha na exclusão de compra: ' . $e->getMessage());

    header('Location: compra.php?erro=exclusao');
    exit;
}