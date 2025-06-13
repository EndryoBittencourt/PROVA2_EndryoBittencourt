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

    // VALIDAÇÃO SERVER-SIDE: Remover números do nome antes de salvar
    $nome = preg_replace('/[0-9]+/', '', $nome);

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
        
        $sql_func = "SELECT id_funcionario FROM funcionario WHERE email = :email";
        $stmt_func = $pdo->prepare($sql_func);
        $stmt_func->bindParam(':email', $_SESSION['email']);
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
    <link rel="stylesheet" href="EndryoStyles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container"> <h2>Cadastrar Cliente</h2>
        <form action="cadastro_cliente.php" method="POST" class="grid-form"> <div>
                <label for="nome_cliente">Nome:</label>
                <input type="text" id="nome_cliente" name="nome_cliente" required>
            </div>

            <div class="full-width"> <label for="endereco">Endereço:</label>
                <input type="text" id="endereco" name="endereco">
            </div>

            <div>
                <label for="telefone">Telefone:</label>
                <input type="text" id="telefone" name="telefone">
            </div>

            <div>
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-buttons"> <button type="submit">Cadastrar</button>
                <button type="reset">Limpar</button>
            </div>
        </form>
        <a href="principal.php" class="btn-back">Voltar</a>
    </div>

    <script>
    $(document).ready(function(){
        $('#telefone').mask('(00) 00000-0000');
        $('#email').on('input', function() {
            $(this).val($(this).val().toLowerCase());
        });
        $('#nome_cliente').on('input', function() {
            var inputVal = $(this).val();
            inputVal = inputVal.replace(/[0-9]/g, '');
            if (inputVal.length > 0) {
                $(this).val(inputVal.replace(/\b\w/g, function(l){ return l.toUpperCase(); }));
            } else {
                $(this).val('');
            }
        });
    });
    </script>
</body>
</html>