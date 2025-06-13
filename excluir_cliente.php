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
    <link rel="stylesheet" href="EndryoStyles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container">
        <h2>Excluir Cliente</h2>
        
        <form action="excluir_cliente.php" method="POST" class="search-form">
            <div>
                <label for="busca">Pesquisar Cliente (ID ou Nome):</label>
                <input type="text" id="busca" name="busca" placeholder="Digite ID ou nome...">
            </div>
            <div class="form-buttons">
                <button type="submit" class="btn-search">Buscar</button>
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