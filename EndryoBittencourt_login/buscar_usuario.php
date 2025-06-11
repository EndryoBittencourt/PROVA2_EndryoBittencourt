<?php
session_start();
require_once 'conexao.php';

// Verifica se o usuário está logado e tem perfil de adm (1) ou secretaria (2)
if (!isset($_SESSION['perfil']) || ($_SESSION['perfil'] != 1 && $_SESSION['perfil'] != 2)) {
    echo "<script>alert('Acesso negado!'); window.location.href='principal.php';</script>";
    exit;
}

$usuarios = []; // Inicializa o array para armazenar resultados

// Se o formulário foi enviado e a busca não está vazia
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['busca'])) {
    $busca = trim($_POST['busca']);

    // Verifica se a busca é um número (ID) ou texto (nome)
    if (is_numeric($busca)) {
        $sql = "SELECT * FROM usuario WHERE id_usuario = :busca ORDER BY nome ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':busca', $busca, PDO::PARAM_INT);
    } else {
        $sql = "SELECT * FROM usuario WHERE nome LIKE :busca_nome ORDER BY nome ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':busca_nome', "%$busca%", PDO::PARAM_STR);
    }

    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-be">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar usuario</title>
    <link rel = "stylesheet" href = "styles.css">
</head>
<body>
    <h2> Lista de usuários </h2> 
    <!-- FORMULARIO PARA BUSCAR USUARIOS -->
    <form action = "buscar_usuario.php" method = "POST">
        <label for = "busca"> Digite o ID ou NOME (opcional) </label>
        <input type = "text" id= "busca" name = "busca" required>
        <button type = "submit"> Pesquisar </button>
</form>

    <?php if (!empty($usuarios)) : ?>
        <table border = "1">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Email</th>
                <th>Perfil</th>
                <th>Ações</th>
            </tr>
            <?php foreach ($usuarios as $usuario) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($usuario['id_usuario']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                    <td><?php echo htmlspecialchars($usuario['id_perfil']); ?></td>
                    <td>
                        <a href="alterar_usuario.php?id=<?= htmlspecialchars($usuario['id_perfil']); ?>">Alterar</a>
                        <a href="excluir_usuario.php?id=<?= htmlspecialchars($usuario['id_perfil']); ?>"onclick = "return confirm('Tem certeza que deseja excluir este usuário?')">Excluir</a>
                        
                    </td>
                </tr>
    <?php endforeach; ?>
    </table>
    <?php else : ?>
        <p>Nenhum usuário encontrado.</p>
    <?php endif; ?>
    <br>
    <a href="principal.php">Voltar</a>
    
                



</body>
</html>