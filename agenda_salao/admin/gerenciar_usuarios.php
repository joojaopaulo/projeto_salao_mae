<?php
session_start();

// PASSO DE SEGURANÇA: Verifica se o usuário está logado e se é um administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Conexão com o banco de dados
$conn = new mysqli("localhost", "root", "", "beauty_saloon_schedules");
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// Inicializa variáveis de mensagem
$mensagem = "";
$tipo_mensagem = "";

// --- LÓGICA PARA ATUALIZAR ROLE (NOVO) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_role'])) {
    $user_id_to_update = $_POST['user_id'];
    $new_role = $_POST['new_role'];

    // Impede que o admin altere a própria role (importante!)
    if ($user_id_to_update == $_SESSION['usuario_id']) {
        $mensagem = "Atenção: Você não pode alterar sua própria permissão.";
        $tipo_mensagem = "erro";
    } else {
        $sql_update = "UPDATE users SET roles = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $new_role, $user_id_to_update);

        if ($stmt_update->execute()) {
            $mensagem = "Permissão do usuário atualizada com sucesso!";
            $tipo_mensagem = "sucesso";
        } else {
            $mensagem = "Erro ao atualizar a permissão.";
            $tipo_mensagem = "erro";
        }
        $stmt_update->close();
    }
}

// --- LÓGICA PARA ADICIONAR NOVO USUÁRIO ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_user'])) {
    $email = $_POST["email"];
    $senha = $_POST["senha"];
    $roles = $_POST["roles"];

    if (empty($email) || empty($senha) || empty($roles)) {
        $mensagem = "Por favor, preencha todos os campos para adicionar um usuário.";
        $tipo_mensagem = "erro";
    } else {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        
        $sql_check = "SELECT id FROM users WHERE email = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $mensagem = "Erro: Este e-mail já está cadastrado.";
            $tipo_mensagem = "erro";
        } else {
            $sql_insert = "INSERT INTO users (email, password, roles) VALUES (?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("sss", $email, $senha_hash, $roles);

            if ($stmt_insert->execute()) {
                $mensagem = "Usuário adicionado com sucesso!";
                $tipo_mensagem = "sucesso";
            } else {
                $mensagem = "Erro ao adicionar usuário: " . $stmt_insert->error;
                $tipo_mensagem = "erro";
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}

// --- LÓGICA PARA LISTAR USUÁRIOS ---
$sql_list = "SELECT id, email, roles FROM users ORDER BY id ASC";
$result_list = $conn->query($sql_list);

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Usuários - Painel Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>

    <header class="site-header">
    <div class="container">
        <div class="header-content">
            <a href="../index.php">
                <img src="../img/logo.png" alt="Logo do Salão" class="logo">
            </a>
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php">Painel Principal</a></li>
                    <li><a href="../logout.php" class="btn btn-danger">Sair</a></li>
                </ul>
            </nav>
        </div>
    </div>
</header>

    <main class="container">
        <h1>Gerenciar Usuários</h1>
        
        <?php if ($mensagem): ?>
            <p class="alert <?= $tipo_mensagem == 'sucesso' ? 'alert-success' : 'alert-danger' ?>">
                <?= htmlspecialchars($mensagem) ?>
            </p>
        <?php endif; ?>

        <hr>

        <h2>Adicionar Novo Usuário</h2>
        <form method="POST" action="gerenciar_usuarios.php">
            <label for="email">E-mail do Usuário:</label>
            <input type="email" id="email" name="email" required>
            <label for="senha">Senha Provisória:</label>
            <input type="password" id="senha" name="senha" required>
            <label for="roles">Tipo de Usuário:</label>
            <select id="roles" name="roles" required>
                <option value="cliente">Cliente</option>
                <option value="admin">Administrador</option>
            </select>
            <button type="submit" name="add_user" class="btn btn-primary" style="margin-top: 10px;">Adicionar Usuário</button>
        </form>

        <hr style="margin-top: 40px;">

        <h2>Usuários Cadastrados</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>E-mail</th>
                    <th>Permissão (Role)</th>
                    <th style="width: 120px;">Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_list && $result_list->num_rows > 0): ?>
                    <?php while($user = $result_list->fetch_assoc()): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td>
                                <form method="POST" action="gerenciar_usuarios.php" style="margin: 0;">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <select name="new_role" class="form-control">
                                        <option value="cliente" <?= $user['roles'] == 'cliente' ? 'selected' : '' ?>>
                                            Cliente
                                        </option>
                                        <option value="admin" <?= $user['roles'] == 'admin' ? 'selected' : '' ?>>
                                            Admin
                                        </option>
                                    </select>
                            </td>
                            <td>
                                    <button type="submit" name="update_role" class="btn btn-success">Salvar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">Nenhum usuário encontrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</body>
</html>
<?php
$conn->close();
?>