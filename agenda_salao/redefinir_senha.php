<?php
session_start();
$erro = '';
$sucesso = '';

// Se o usuário tentar acessar esta página sem passar pela anterior, redireciona.
if (!isset($_SESSION['email_para_redefinir'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    if ($nova_senha !== $confirmar_senha) {
        $erro = "As senhas não coincidem!";
    } elseif (strlen($nova_senha) < 4) {
        $erro = "A senha precisa ter pelo menos 4 caracteres.";
    } else {
        // As senhas coincidem, vamos atualizar no banco.
        $conn = new mysqli("localhost", "root", "", "beauty_saloon_schedules");
        if ($conn->connect_error) { die("Erro de conexão: " . $conn->connect_error); }

        $email = $_SESSION['email_para_redefinir'];
        $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $senha_hash, $email);
        
        if ($stmt->execute()) {
            $sucesso = "Senha redefinida com sucesso! Você já pode fazer login com a nova senha.";
            // Limpa a sessão para não poder usar a página novamente
            unset($_SESSION['email_para_redefinir']);
        } else {
            $erro = "Ocorreu um erro ao atualizar a senha.";
        }
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Redefinir Senha</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h2>Crie sua Nova Senha</h2>
        
        <?php if ($sucesso): ?>
            <p class="alert alert-success"><?= htmlspecialchars($sucesso) ?></p>
            <a href="login.php" class="btn btn-primary">Ir para o Login</a>
        <?php else: ?>
            <form method="POST">
                <label for="nova_senha">Nova Senha:</label>
                <input type="password" id="nova_senha" name="nova_senha" required>

                <label for="confirmar_senha">Confirme a Nova Senha:</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" required>

                <button type="submit">Redefinir Senha</button>
            </form>
            <?php if ($erro): ?>
                <p class="alert alert-danger"><?= htmlspecialchars($erro) ?></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>