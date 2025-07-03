<?php
session_start();

// Segurança: Apenas admins podem acessar
if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
    die("Acesso negado.");
}

// --- CONEXÃO COM O BANCO ---
$conn = new mysqli("localhost", "root", "", "beauty_saloon_schedules");
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// --- MODO DE EDIÇÃO OU CRIAÇÃO ---
$is_editing = isset($_GET['id']) && !empty($_GET['id']);
$service_id = $is_editing ? $_GET['id'] : null;

// Inicializa as variáveis do serviço
$service = [
    'name' => '',
    'description' => '',
    'price' => '',
    'duration' => ''
];
$page_title = "Cadastrar Novo Serviço";
$button_text = "Cadastrar Serviço";

// Se estiver editando, busca os dados do serviço
if ($is_editing) {
    $page_title = "Editar Serviço";
    $button_text = "Salvar Alterações";
    
    $stmt_select = $conn->prepare("SELECT * FROM services WHERE id = ?");
    $stmt_select->bind_param("i", $service_id);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    $service = $result->fetch_assoc();
    $stmt_select->close();

    if (!$service) {
        header("Location: service_list.php");
        exit;
    }
}

// --- PROCESSAMENTO DO FORMULÁRIO (PARA ADICIONAR OU EDITAR) ---
$mensagem = "";
$tipo_mensagem = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $duracao = $_POST['duracao'];
    
    if ($is_editing) {
        // Lógica de UPDATE
        $stmt = $conn->prepare("UPDATE services SET name = ?, description = ?, price = ?, duration = ? WHERE id = ?");
        $stmt->bind_param("ssdii", $nome, $descricao, $preco, $duracao, $service_id);
        $acao = "atualizado";
    } else {
        // Lógica de INSERT
        $stmt = $conn->prepare("INSERT INTO services (name, description, price, duration) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssdi", $nome, $descricao, $preco, $duracao);
        $acao = "cadastrado";
    }
    
    if ($stmt->execute()) {
        $mensagem = "Serviço {$acao} com sucesso!";
        $tipo_mensagem = "sucesso";
        // Se for uma nova inserção, atualiza os dados para exibir no form (ou redireciona)
        if (!$is_editing) {
             header("Location: service_list.php"); // Redireciona para a lista após cadastrar
             exit;
        }
    } else {
        $mensagem = "Erro ao {$acao}: " . $stmt->error;
        $tipo_mensagem = "erro";
    }
    $stmt->close();
}
$conn->close();

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?></title>
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
        <a href="service_list.php" style="display: block; margin-bottom: 20px;">&larr; Voltar para a Lista de Serviços</a>
        <h2><?= $page_title ?></h2>
        
        <?php if ($mensagem): ?>
            <p class="alert <?= $tipo_mensagem == 'sucesso' ? 'alert-success' : 'alert-danger' ?>"><?= htmlspecialchars($mensagem) ?></p>
        <?php endif; ?>

        <form method="POST" action="">
             <label for="nome">Nome do serviço:</label>
            <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($service['name']) ?>" required>
            
            <label for="descricao">Descrição:</label>
            <textarea id="descricao" name="descricao" required><?= htmlspecialchars($service['description']) ?></textarea>
            
            <label for="preco">Preço (R$):</label>
            <input type="number" step="0.01" id="preco" name="preco" value="<?= htmlspecialchars($service['price']) ?>" required>
            
            <label for="duracao">Duração (em minutos):</label>
            <input type="number" id="duracao" name="duracao" value="<?= htmlspecialchars($service['duration']) ?>" required>

            <button type="submit" class="btn btn-primary" style="margin-top: 10px;"><?= $button_text ?></button>
        </form>
    </main>

</body>
</html>