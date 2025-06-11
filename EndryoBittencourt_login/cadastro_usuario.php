<?php
session_start();
require_once 'conexao.php';

// Verifica se o usuário está logado e tem perfil de administrador (supondo que perfil 1 seja admin)
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] != 1) {
    echo "Acesso negado";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $id_perfil = $_POST['id_perfil'] ?? '';

    // Verifica se os campos obrigatórios estão preenchidos
    if (empty($nome) || empty($email) || empty($senha) || empty($id_perfil)) {
        echo "<script>alert('Todos os campos são obrigatórios.');</script>";
    } else {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuario (nome, email, senha, id_perfil) VALUES (:nome, :email, :senha, :id_perfil)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':senha', $senha_hash);
        $stmt->bindParam(':id_perfil', $id_perfil);

        if ($stmt->execute()) {
            echo "<script>alert('Usuário cadastrado com sucesso!');</script>";
        } else {
            echo "<script>alert('Erro ao cadastrar usuário!');</script>";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="pt-be">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar usuario</title>
    <link rel = "stylesheet" href = "styles.css">
</head>
<body>
    <h2> Cadastrar usuário </h2> 
    <form action = "cadastro_usuario.php" method = "POST">

    <label for="nome">Nome:</label><br>
    <input type="text" id="nome" name="nome" required><br><br>

    <label for="email">Email:</label><br>
    <input type="email" id="email" name="email" required><br><br>

    <label for="senha">Senha:</label><br>
    <input type="password" id="senha" name="senha" required><br><br>

    <label for="id_perfil">Perfil:</label><br>
    <select id ="id_perfil" name="id_perfil" required>
        <option value="">Selecione um perfil</option>
        <option value="1">Administrador</option>
        <option value="2">Secretária</option>
        <option value="3">Almoxarife</option>
        <option value="4"> Cliente</option>
</select>
<Br><BR>
        <button type = "submit">Cadastrar</button>
        <button type = "reset"> Cancelar </button>
</form>
<a href = "principal.php"> Voltar</a>

</body>
</html>
