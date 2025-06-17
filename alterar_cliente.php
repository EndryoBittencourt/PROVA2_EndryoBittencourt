<?php
session_start();
require 'conexao.php';

// Verifica se o usuário tem permissão (ADM, Secretaria ou Cliente)
if (!isset($_SESSION['perfil']) || ($_SESSION['perfil'] != 1 && $_SESSION['perfil'] != 2 && $_SESSION['perfil'] != 4)) {
    echo "<script>alert('Acesso negado!'); window.location.href='principal.php';</script>";
    exit();
}

$cliente = null;
$clientes_lista = []; // Renomeado para evitar conflito com $cliente

// Busca todos os clientes para exibir na lista
$sql_all = "SELECT id_cliente, nome_cliente, email, telefone, endereco FROM cliente ORDER BY nome_cliente ASC";
$stmt_all = $pdo->prepare($sql_all);
$stmt_all->execute();
$clientes_lista = $stmt_all->fetchAll(PDO::FETCH_ASSOC);

// Se a requisição for AJAX para buscar um cliente específico para edição
if (isset($_GET['action']) && $_GET['action'] == 'get_cliente_data' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT id_cliente, nome_cliente, endereco, telefone, email FROM cliente WHERE id_cliente = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $cliente_data = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($cliente_data);
    exit(); // Termina o script após enviar os dados JSON
}

// Processa o formulário de alteração
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verifica se é uma busca ou uma alteração
    if (isset($_POST['busca_termo'])) {
        // Processa a busca
        $termoBusca = trim($_POST['busca_termo']);
        $clientes_lista = []; // Reseta a lista para exibir apenas os resultados da busca

        if (is_numeric($termoBusca)) {
            $sql = "SELECT id_cliente, nome_cliente, email, telefone, endereco FROM cliente WHERE id_cliente = :busca ORDER BY nome_cliente ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':busca', $termoBusca, PDO::PARAM_INT);
        } else {
            $sql = "SELECT id_cliente, nome_cliente, email, telefone, endereco FROM cliente WHERE nome_cliente LIKE :busca_nome ORDER BY nome_cliente ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':busca_nome', "%$termoBusca%", PDO::PARAM_STR);
        }
        $stmt->execute();
        $clientes_lista = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } else {
        // Processa a alteração de dados do cliente
        $id = $_POST['id_cliente'] ?? '';
        $nome = $_POST['nome_cliente'] ?? '';
        $endereco = $_POST['endereco'] ?? '';
        $telefone = $_POST['telefone'] ?? '';
        $email = $_POST['email'] ?? '';

        // VALIDAÇÃO SERVER-SIDE: Remover números do nome antes de salvar
        $nome = preg_replace('/[0-9]+/', '', $nome);

        if (empty($id) || empty($nome) || empty($email)) {
            echo "<script>alert('ID, Nome e email são obrigatórios!');</script>";
        } else {
            $sql = "UPDATE cliente SET nome_cliente = :nome, endereco = :endereco, telefone = :telefone, email = :email WHERE id_cliente = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':endereco', $endereco);
            $stmt->bindParam(':telefone', $telefone);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                echo "<script>alert('Cliente atualizado com sucesso!'); window.location.href='alterar_cliente.php';</script>";
            } else {
                echo "<script>alert('Erro ao atualizar cliente.');</script>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Cliente - Sistema de Gerenciamento</title>
    <link rel="stylesheet" href="Endryostyles.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
</head>
<body>
    <div class="container">
        <h2>Alterar Cliente</h2>

        <div id="client-list-section">
            <form id="search-form" action="alterar_cliente.php" method="post" class="grid-form">
                <div class="full-width">
                    <label for="busca_cliente">Buscar Cliente (ID ou Nome):</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" id="busca_cliente" name="busca_termo" placeholder="Digite ID ou Nome do cliente">
                        <button type="submit" class="btn-search">Buscar</button>
                    </div>
                </div>
            </form>

            <?php if (!empty($clientes_lista)): ?>
                <table class="styled-table" id="clients-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Telefone</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clientes_lista as $cliente_item): ?>
                            <tr>
                                <td data-label="ID"><?= htmlspecialchars($cliente_item['id_cliente']) ?></td>
                                <td data-label="Nome"><?= htmlspecialchars($cliente_item['nome_cliente']) ?></td>
                                <td data-label="E-mail"><?= htmlspecialchars($cliente_item['email']) ?></td>
                                <td data-label="Telefone"><?= htmlspecialchars($cliente_item['telefone']) ?></td>
                                <td data-label="Ações">
                                    <a href="#" class="btn-alterar-item" data-id="<?= $cliente_item['id_cliente'] ?>">Alterar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-results">Nenhum cliente encontrado.</p>
            <?php endif; ?>
        </div>

        <div id="edit-client-section" style="display: none;">
            <h3>Editar Cliente</h3>
            <form action="alterar_cliente.php" method="post" class="grid-form">
                <input type="hidden" name="id_cliente" id="edit_id_cliente">

                <div>
                    <label for="edit_nome_cliente">Nome:</label>
                    <input type="text" id="edit_nome_cliente" name="nome_cliente" required>
                </div>

                <div class="full-width">
                    <label for="edit_endereco">Endereço:</label>
                    <input type="text" id="edit_endereco" name="endereco">
                </div>

                <div>
                    <label for="edit_telefone">Telefone:</label>
                    <input type="text" id="edit_telefone" name="telefone">
                </div>

                <div>
                    <label for="edit_email">E-mail:</label>
                    <input type="email" id="edit_email" name="email" required>
                </div>

                <div class="form-buttons">
                    <button type="submit">Salvar Alterações</button>
                    <button type="button" id="btn-cancel-edit" class="btn-back">Voltar para Lista</button>
                </div>
            </form>
        </div>
        <a href="principal.php" class="btn-back" id="main-back-button">Voltar ao Início</a>
    </div>

    <script>
    $(document).ready(function(){
        // Máscara de telefone
        $('#edit_telefone').mask('(00) 00000-0000');

        // Normalização de input para nome e email (edição)
        $('#edit_email').on('input', function() {
            $(this).val($(this).val().toLowerCase());
        });
        $('#edit_nome_cliente').on('input', function() {
            var inputVal = $(this).val();
            inputVal = inputVal.replace(/[0-9]/g, '');
            if (inputVal.length > 0) {
                $(this).val(inputVal.replace(/\b\w/g, function(l){ return l.toUpperCase(); }));
            } else {
                $(this).val('');
            }
        });

        // Manipula o clique no botão "Alterar" da tabela
        $('#clients-table').on('click', '.btn-alterar-item', function(e) {
            e.preventDefault();
            var clientId = $(this).data('id');

            // Esconde a seção da lista de clientes
            $('#client-list-section').hide();
            // Esconde o botão "Voltar ao Início" principal
            $('#main-back-button').hide();

            // Faz a requisição AJAX para obter os dados do cliente
            $.ajax({
                url: 'alterar_cliente.php',
                type: 'GET',
                data: { action: 'get_cliente_data', id: clientId },
                dataType: 'json',
                success: function(data) {
                    if (data) {
                        // Preenche o formulário com os dados recebidos
                        $('#edit_id_cliente').val(data.id_cliente);
                        $('#edit_nome_cliente').val(data.nome_cliente);
                        $('#edit_endereco').val(data.endereco);
                        $('#edit_telefone').val(data.telefone);
                        $('#edit_email').val(data.email);

                        // Mostra a seção de edição
                        $('#edit-client-section').fadeIn(500);
                    } else {
                        alert('Dados do cliente não encontrados.');
                        // Volta para a lista se não encontrar dados
                        $('#client-list-section').fadeIn(500);
                        $('#main-back-button').show();
                    }
                },
                error: function() {
                    alert('Erro ao carregar dados do cliente.');
                    // Volta para a lista em caso de erro
                    $('#client-list-section').fadeIn(500);
                    $('#main-back-button').show();
                }
            });
        });

        // Manipula o clique no botão "Voltar para Lista" no formulário de edição
        $('#btn-cancel-edit').on('click', function() {
            // Esconde a seção de edição
            $('#edit-client-section').hide();
            // Mostra a seção da lista de clientes
            $('#client-list-section').fadeIn(500);
            // Mostra o botão "Voltar ao Início" principal
            $('#main-back-button').show();
            // Limpa o formulário de edição (opcional)
            $('#edit-client-section form')[0].reset();
        });
    });
    </script>
</body>
</html>