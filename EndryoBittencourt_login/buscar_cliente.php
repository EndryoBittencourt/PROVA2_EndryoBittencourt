<?php
session_start();
require_once 'conexao.php';

// Verifica se o usuário tem permissão (ADM, Secretaria ou Cliente)
if (!isset($_SESSION['perfil']) || ($_SESSION['perfil'] != 1 && $_SESSION['perfil'] != 2 && $_SESSION['perfil'] != 4)) {
    echo "<script>alert('Acesso negado!'); window.location.href='principal.php';</script>";
    exit;
}

$clientes = [];

// Busca todos os clientes inicialmente
$sql = "SELECT * FROM cliente ORDER BY nome_cliente ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Se houver uma busca, filtra os resultados
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
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Buscar Cliente</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Buscar Cliente</h2>
    <form action="buscar_cliente.php" method="POST">
        <label for="busca">Digite o ID ou Nome:</label>
        <input type="text" id="busca" name="busca">
        <button type="submit">Buscar</button>
    </form>

    <?php if (!empty($clientes)): ?>
        <table border="1">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Endereço</th>
                <th>Telefone</th>
                <th>E-mail</th>
                <th>Ações</th>
            </tr>
            <?php foreach ($clientes as $cliente): ?>
                <tr>
                    <td><?= htmlspecialchars($cliente['id_cliente']) ?></td>
                    <td><?= htmlspecialchars($cliente['nome_cliente']) ?></td>
                    <td><?= htmlspecialchars($cliente['endereco']) ?></td>
                    <td><?= htmlspecialchars($cliente['telefone']) ?></td>
                    <td><?= htmlspecialchars($cliente['email']) ?></td>
                    <td>
                        <a href="alterar_cliente.php?id=<?= $cliente['id_cliente'] ?>">Alterar</a>
                        <?php if ($_SESSION['perfil'] == 1): ?>
                            <a href="excluir_cliente.php?id=<?= $cliente['id_cliente'] ?>" onclick="return confirm('Tem certeza?')">Excluir</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Nenhum cliente encontrado.</p>
    <?php endif; ?>
    <a href="principal.php">Voltar</a>
</body>
</html>