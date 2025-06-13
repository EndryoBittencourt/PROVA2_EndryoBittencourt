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

        // VALIDAÇÃO SERVER-SIDE: Remover números do nome antes de salvar
        $nome = preg_replace('/[0-9]+/', '', $nome);

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
    <link rel="stylesheet" href="EndryoStyles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="container"> <h2>Alterar Cliente</h2>
        
        <form action="alterar_cliente.php" method="POST" class="search-form">
            <div>
                <label for="busca">Pesquisar Cliente (ID ou Nome):</label>
                <input type="text" id="busca" name="busca">
            </div>
            <div class="form-buttons">
                <button type="submit" class="btn-search">Buscar</button>
            </div>
        </form>
        
        <?php if (!empty($clientes)): ?>
            <table class="styled-table"> <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $c): ?>
                        <tr>
                            <td data-label="ID"><?= htmlspecialchars($c['id_cliente']) ?></td>
                            <td data-label="Nome"><?= htmlspecialchars($c['nome_cliente']) ?></td>
                            <td data-label="Ação"><a href="alterar_cliente.php?id=<?= $c['id_cliente'] ?>">Selecionar</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <?php if ($cliente): ?>
            <h3>Editar Cliente</h3>
            <form action="alterar_cliente.php" method="POST" class="grid-form"> <input type="hidden" name="id_cliente" value="<?= $cliente['id_cliente'] ?>">
                
                <div>
                    <label for="nome_cliente">Nome:</label>
                    <input type="text" id="nome_cliente" name="nome_cliente" value="<?= htmlspecialchars($cliente['nome_cliente']) ?>" required>
                </div>
                
                <div class="full-width"> <label for="endereco">Endereço:</label>
                    <input type="text" id="endereco" name="endereco" value="<?= htmlspecialchars($cliente['endereco']) ?>">
                </div>
                
                <div>
                    <label for="telefone">Telefone:</label>
                    <input type="text" id="telefone" name="telefone" value="<?= htmlspecialchars($cliente['telefone']) ?>">
                </div>
                
                <div>
                    <label for="email">E-mail:</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($cliente['email']) ?>" required>
                </div>
                
                <div class="form-buttons"> <button type="submit">Salvar</button>
                    <button type="reset">Cancelar</button>
                </div>
            </form>
        <?php elseif (isset($_GET['id'])): ?>
            <p class="no-results">Cliente não encontrado.</p> <?php endif; ?>
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