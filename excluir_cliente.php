<?php
session_start();
require 'conexao.php';

// Verifica se o usuário tem permissão de ADM
if ($_SESSION['perfil'] != 1) {
    echo "<script>alert('Acesso negado!'); window.location.href='principal.php';</script>";
    exit();
}

$clientes = [];

// Busca inicial ou após pesquisa
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['busca'])) {
    $busca = trim($_POST['busca']);
    if (is_numeric($busca)) {
        $sql = "SELECT * FROM cliente WHERE id_cliente = :busca ORDER BY nome_cliente ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':busca', $busca, PDO::PARAM_INT);
    } else {
        $sql = "SELECT * FROM cliente WHERE nome_cliente LIKE :busca_nome ORDER BY nome_cliente ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':busca_nome', "%$busca%", PDO::PARAM_STR);
    }
    $stmt->execute();
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Lista todos os clientes inicialmente
    $sql = "SELECT * FROM cliente ORDER BY nome_cliente ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Processa a exclusão
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_cliente = $_GET['id'];

    // --- START OF MODIFIED SECTION ---
    // Commented out the contract check because the 'contrato' table doesn't exist
    /*
    $sql_check_associates = "SELECT COUNT(*) FROM contrato WHERE id_cliente = :id_cliente";
    $stmt_check = $pdo->prepare($sql_check_associates);
    $stmt_check->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
    $stmt_check->execute();
    $count_associates = $stmt_check->fetchColumn();

    if ($count_associates > 0) {
        echo "<script>alert('Não é possível excluir o cliente, pois ele possui contratos associados. Exclua os contratos primeiro.'); window.location.href='excluir_cliente.php';</script>";
    } else {
        // Original deletion logic if no contracts found
    */
        $sql_delete = "DELETE FROM cliente WHERE id_cliente = :id_cliente";
        $stmt_delete = $pdo->prepare($sql_delete);
        $stmt_delete->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);

        if ($stmt_delete->execute()) {
            echo "<script>alert('Cliente excluído com sucesso!'); window.location.href='excluir_cliente.php';</script>";
        } else {
            echo "<script>alert('Erro ao excluir cliente.'); window.location.href='excluir_cliente.php';</script>";
        }
    /*
    } // End of else for $count_associates > 0
    */
    // --- END OF MODIFIED SECTION ---
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Excluir Cliente - Sistema de Gerenciamento</title>
    <link rel="stylesheet" href="Endryostyles.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
</head>
<body>
    <div class="container">
        <h2>Excluir Cliente</h2>

        <form action="excluir_cliente.php" method="post" class="grid-form">
            <div class="full-width">
                <label for="busca">Buscar por ID ou Nome:</label>
                <div style="display: flex; gap: 10px;">
                    <input type="text" id="busca" name="busca" placeholder="Digite ID ou Nome do cliente">
                    <button type="submit" class="btn-search">Buscar</button>
                </div>
            </div>
        </form>

        <?php if (!empty($clientes)): ?>
            <table class="styled-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cliente): ?>
                        <tr>
                            <td data-label="ID"><?= htmlspecialchars($cliente['id_cliente']) ?></td>
                            <td data-label="Nome"><?= htmlspecialchars($cliente['nome_cliente']) ?></td>
                            <td data-label="E-mail"><?= htmlspecialchars($cliente['email']) ?></td>
                            <td data-label="Ações">
                                <a href="excluir_cliente.php?id=<?= $cliente['id_cliente'] ?>" class="btn-delete" onclick="return confirm('Tem certeza que deseja excluir este cliente?')">Excluir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-results">Nenhum cliente encontrado.</p>
        <?php endif; ?>
        <a href="principal.php" class="btn-back">Voltar</a>
    </div>
</body>
</html>