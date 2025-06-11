<?php
session_start();
require_once 'conexao.php';

// Verifica se o usuário tem permissão (ADM ou Secretaria)
if (!isset($_SESSION['perfil']) || ($_SESSION['perfil'] != 1 && $_SESSION['perfil'] != 2 && $_SESSION['perfil'] != 4)) {
    echo "<script>alert('Acesso negado!'); window.location.href='principal.php';</script>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome_cliente'] ?? '';
    $endereco = $_POST['endereco'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $email = $_POST['email'] ?? '';

    if (empty($nome) || empty($email)) {
        echo "<script>alert('Nome e email são obrigatórios!');</script>";
    } else {
        $sql = "INSERT INTO cliente (nome_cliente, endereco, telefone, email, id_funcionario_responsavel) 
                VALUES (:nome, :endereco, :telefone, :email, :id_funcionario)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':endereco', $endereco);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':email', $email);
        // Primeiro verifica se o usuário logado tem um funcionário associado
$sql_func = "SELECT id_funcionario FROM funcionario WHERE email = :email";
$stmt_func = $pdo->prepare($sql_func);
$stmt_func->bindParam(':email', $_SESSION['email']); // Assumindo que o email está na session
$stmt_func->execute();
$funcionario = $stmt_func->fetch(PDO::FETCH_ASSOC);

$id_funcionario = $funcionario ? $funcionario['id_funcionario'] : null;
$stmt->bindParam(':id_funcionario', $id_funcionario, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo "<script>alert('Cliente cadastrado com sucesso!');</script>";
        } else {
            echo "<script>alert('Erro ao cadastrar cliente!');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Cliente</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Cadastrar Cliente</h2>
    <form action="cadastro_cliente.php" method="POST">
        <label for="nome_cliente">Nome:</label>
        <input type="text" id="nome_cliente" name="nome_cliente" required><br>

        <label for="endereco">Endereço:</label>
        <input type="text" id="endereco" name="endereco"><br>

        <label for="telefone">Telefone:</label>
        <input type="text" id="telefone" name="telefone"><br>

        <label for="email">E-mail:</label>
        <input type="email" id="email" name="email" required><br>

        <button type="submit">Cadastrar</button>
        <button type="reset">Limpar</button>
    </form>
    <a href="principal.php">Voltar</a>
</body>
</html>