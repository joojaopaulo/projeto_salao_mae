<?php
session_start();
$erro = '';

// Lógica para encontrar o e-mail no banco de dados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli("localhost", "root", "", "beauty_saloon_schedules");
    if ($conn->connect_error) { die("Erro de conexão: " . $conn->connect_error); }

    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Email encontrado! Guardamos na sessão e redirecionamos para a próxima etapa.
        $_SESSION['email_para_redefinir'] = $email;
        header("Location: redefinir_senha.php");
        exit();
    } else {
        $erro = "Nenhum usuário encontrado com este e-mail.";
    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Senha</title>
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="login-body">

    <div class="login-container">
        <a href="index.php">
            <img src="img/logo.png" alt="Logo do Salão" class="login-logo">
        </a>

        <h2>Recuperar Senha</h2>
        <p style="font-size: 0.9em; color: #666; margin-top: -10px; margin-bottom: 20px;">
            Insira seu e-mail para iniciar a redefinição.
        </p>

        <form method="POST" action="">
            <input type="email" id="email" name="email" placeholder="Seu e-mail cadastrado" required>
            <button type="submit" class="btn btn-primary">Prosseguir</button>
        </form>

        <?php if ($erro): ?>
            <p class="alert alert-danger" style="margin-top: 15px;"><?= htmlspecialchars($erro) ?></p>
        <?php endif; ?>

        <div class="login-links">
            <a href="login.php">Lembrou a senha? Voltar para o Login</a>
        </div>
    </div>

</body>
</html>     