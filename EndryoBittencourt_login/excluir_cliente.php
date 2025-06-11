<?php
session_start();
require 'conexao.php';

// Verifica se o usuário tem permissão de ADM
if ($_SESSION['perfil'] != 1) {
    echo "<script>alert('Acesso negado!'); window.location.href='principal.php';</script>";
    exit();
}

// Lista todos os clientes
$sql = "SELECT * FROM cliente ORDER BY nome_cliente ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Processa a exclusão se receber ID via GET
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    
    $sql = "DELETE FROM cliente WHERE id_cliente = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        echo "<script>alert('Cliente excluído com sucesso!'); window.location.href='excluir_cliente.php';</script>";
    } else {
        echo "<script>alert('Erro ao excluir cliente!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Excluir Cliente</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Excluir Cliente</h2>
    
    <?php if (!empty($clientes)): ?>
        <table border="1">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>E-mail</th>
                <th>Ações</th>
            </tr>
            <?php foreach ($clientes as $cliente): ?>
                <tr>
                    <td><?= $cliente['id_cliente'] ?></td>
                    <td><?= htmlspecialchars($cliente['nome_cliente']) ?></td>
                    <td><?= htmlspecialchars($cliente['email']) ?></td>
                    <td>
                        <a href="excluir_cliente.php?id=<?= $cliente['id_cliente'] ?>" onclick="return confirm('Tem certeza?')">Excluir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Nenhum cliente cadastrado.</p>
    <?php endif; ?>
    <a href="principal.php">Voltar</a>
</body>
</html>