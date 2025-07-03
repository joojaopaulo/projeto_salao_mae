<?php
session_start();
$erro = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli("localhost", "root", "", "beauty_saloon_schedules");

    if ($conn->connect_error) { die("Erro de conexão: " . $conn->connect_error); }

    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($usuario = $resultado->fetch_assoc()) {
        if (password_verify($senha, $usuario['password'])) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_tipo'] = $usuario['roles'];
            $_SESSION['usuario_nome'] = $usuario['email'];

            if ($usuario['roles'] == 'admin') {
                header("Location: admin/index.php");
            } else {
                header("Location: user/index.php");
            }
            exit();
        } else {
            $erro = "Senha incorreta!";
        }
    } else {
        $erro = "Usuário não encontrado!";
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - Salão de Beleza</title>
    <link rel="stylesheet" href="css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="login-body">
    <div class="login-container">
        
        <a href="index.php">
            <img src="img/logo.png" alt="Logo do Salão" class="login-logo">
        </a>

        <h2>Acesse sua Conta</h2>
        
        <form method="post">
            <label for="email" class="sr-only">E-mail:</label>
            <input type="email" id="email" name="email" placeholder="Seu e-mail" required>

            <label for="senha" class="sr-only">Senha:</label>
            <input type="password" id="senha" name="senha" placeholder="Sua senha" required>

            <button type="submit" class="btn btn-primary">Entrar</button>
        </form>

        <?php if ($erro): ?>
            <p class="alert alert-danger" style="margin-top: 15px;"><?= htmlspecialchars($erro) ?></p>
        <?php endif; ?>

        <div class="login-links">
            <a href="create_user.php">Criar uma conta</a> |
            <a href="esqueceu_senha.php">Esqueceu sua senha?</a>
        </div>
    </div>
</body>
</html>