<?php
$mensagem = "";
$tipo_mensagem = ""; // Inicializa sem tipo

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli("localhost", "root", "", "beauty_saloon_schedules");

    if ($conn->connect_error) {
        die("Erro na conexão: " . $conn->connect_error);
    }

    $email = $_POST["email"];
    $senha = $_POST["senha"];
    // O tipo de usuário agora é fixo como 'cliente' no formulário
    $tipo = "cliente"; 

    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Checar se o e-mail já existe
    $sql_check = "SELECT id FROM users WHERE email = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        $mensagem = "Erro: Este e-mail já está cadastrado.";
        $tipo_mensagem = "erro";
    } else {
        // Se não existe, insere o novo usuário
        $sql_insert = "INSERT INTO users (email, password, roles) VALUES (?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("sss", $email, $senha_hash, $tipo);

        if ($stmt_insert->execute()) {
            $mensagem = "Usuário cadastrado com sucesso! Você já pode fazer o login.";
            $tipo_mensagem = "sucesso";
        } else {
            $mensagem = "Erro ao cadastrar: " . $stmt_insert->error;
            $tipo_mensagem = "erro";
        }
        $stmt_insert->close();
    }
    $stmt_check->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Usuário - Salão de Beleza</title>
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="login-body">
    <div class="login-container">

        <a href="index.php">
            <img src="img/logo.png" alt="Logo do Salão" class="login-logo">
        </a>

        <h2>Crie sua Conta</h2>
        
        <?php if ($mensagem && $tipo_mensagem == 'sucesso'): ?>
            <p class="alert alert-success"><?= htmlspecialchars($mensagem) ?></p>
            <a href="login.php" class="btn btn-primary" style="width:100%; margin-bottom: 20px;">Fazer Login</a>
        <?php else: ?>
            <form method="POST" action="">
                <input type="email" id="email" name="email" placeholder="Seu melhor e-mail" required>
                <input type="password" id="senha" name="senha" placeholder="Crie uma senha" required>
                
                <button type="submit" class="btn btn-primary">Cadastrar</button>
            </form>

            <?php if ($mensagem && $tipo_mensagem == 'erro'): ?>
                <p class="alert alert-danger" style="margin-top: 15px;"><?= htmlspecialchars($mensagem) ?></p>
            <?php endif; ?>
        <?php endif; ?>

        <div class="login-links">
            <a href="login.php">Já tem uma conta? Faça o login</a>
        </div>
    </div>
</body>
</html>