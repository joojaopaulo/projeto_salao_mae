<?php
// Adicionando verificação de segurança
session_start();
if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$mensagem = "";
$tipo_mensagem = ""; // Para controlar a cor do alerta

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli("localhost", "root", "", "beauty_saloon_schedules");

    if ($conn->connect_error) {
        die("Erro de conexão: " . $conn->connect_error);
    }

    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $duracao = $_POST['duracao'];

    $sql = "INSERT INTO services (name, description, price, duration) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdi", $nome, $descricao, $preco, $duracao);

    if ($stmt->execute()) {
        $mensagem = "Serviço cadastrado com sucesso!";
        $tipo_mensagem = "sucesso";
    } else {
        $mensagem = "Erro ao cadastrar: " . $stmt->error;
        $tipo_mensagem = "erro";
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Serviço</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <a href="service_list.php" style="float: right;">Voltar para a Lista</a>
        <h2>Cadastro de Serviço</h2>
        
        <form method="POST">
            <label for="nome">Nome do serviço:</label>
            <input type="text" id="nome" name="nome" required>

            <label for="descricao">Descrição:</label>
            <textarea id="descricao" name="descricao" required></textarea>

            <label for="preco">Preço:</label>
            <input type="number" step="0.01" id="preco" name="preco" required>
            
            <label for="duracao">Duração (em minutos):</label>
            <input type="number" id="duracao" name="duracao" required>

            <button type="submit">Cadastrar</button>
        </form>

        <?php if ($mensagem): ?>
            <?php if ($tipo_mensagem == 'sucesso'): ?>
                <p class="alert alert-success"><?= htmlspecialchars($mensagem) ?></p>
            <?php else: ?>
                <p class="alert alert-danger"><?= htmlspecialchars($mensagem) ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>