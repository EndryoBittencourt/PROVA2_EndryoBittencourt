<?php
session_start();
require 'conexao.php';

// Verifica se o usuário tem permissão (ADM, Secretaria ou Cliente)
if (!isset($_SESSION['perfil']) || ($_SESSION['perfil'] != 1 && $_SESSION['perfil'] != 2 && $_SESSION['perfil'] != 4)) {
    echo "<script>alert('Acesso negado!'); window.location.href='principal.php';</script>";
    exit();
}

$cliente = null;
$clientes = [];

// Busca todos os clientes para a barra de pesquisa
$sql_all = "SELECT * FROM cliente ORDER BY nome_cliente ASC";
$stmt_all = $pdo->prepare($sql_all);
$stmt_all->execute();
$clientes = $stmt_all->fetchAll(PDO::FETCH_ASSOC);

// Se receber ID via GET, busca o cliente específico
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM cliente WHERE id_cliente = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Processa o formulário de alteração
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['busca'])) {
        // Processa a busca
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
        // Processa a atualização do cliente
        $id = $_POST['id_cliente'];
        $nome = $_POST['nome_cliente'];
        $endereco = $_POST['endereco'];
        $telefone = $_POST['telefone'];
        $email = $_POST['email'];

        $sql = "UPDATE cliente SET nome_cliente = :nome, endereco = :endereco, 
                telefone = :telefone, email = :email WHERE id_cliente = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':endereco', $endereco);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':email', $email);

        if ($stmt->execute()) {
            echo "<script>alert('Cliente atualizado com sucesso!'); window.location.href='buscar_cliente.php';</script>";
        } else {
            echo "<script>alert('Erro ao atualizar cliente!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Alterar Cliente</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Alterar Cliente</h2>
    
    <!-- Barra de pesquisa -->
    <form action="alterar_cliente.php" method="POST">
        <label for="busca">Pesquisar Cliente (ID ou Nome):</label>
        <input type="text" id="busca" name="busca">
        <button type="submit">Buscar</button>
    </form>
    
    <!-- Lista de clientes -->
    <?php if (!empty($clientes)): ?>
        <table border="1">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Ação</th>
            </tr>
            <?php foreach ($clientes as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['id_cliente']) ?></td>
                    <td><?= htmlspecialchars($c['nome_cliente']) ?></td>
                    <td><a href="alterar_cliente.php?id=<?= $c['id_cliente'] ?>">Selecionar</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
    
    <!-- Formulário de edição -->
    <?php if ($cliente): ?>
        <h3>Editar Cliente</h3>
        <form action="alterar_cliente.php" method="POST">
            <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
            
            <label for="nome_cliente">Nome:</label>
            <input type="text" id="nome_cliente" name="nome_cliente" value="<?= htmlspecialchars($cliente['nome_cliente']) ?>" required><br>
            
            <label for="endereco">Endereço:</label>
            <input type="text" id="endereco" name="endereco" value="<?= htmlspecialchars($cliente['endereco']) ?>"><br>
            
            <label for="telefone">Telefone:</label>
            <input type="text" id="telefone" name="telefone" value="<?= htmlspecialchars($cliente['telefone']) ?>"><br>
            
            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($cliente['email']) ?>" required><br>
            
            <button type="submit">Salvar</button>
            <button type="reset">Cancelar</button>
        </form>
    <?php elseif (isset($_GET['id'])): ?>
        <p>Cliente não encontrado.</p>
    <?php endif; ?>
    <a href="principal.php">Voltar</a>
</body>
</html>