<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "beauty_saloon_schedules");
if ($conn->connect_error) { die("Erro de conexão: " . $conn->connect_error); }
$sql = "SELECT id, name, description, price, duration FROM services ORDER BY name ASC";
$result = $conn->query($sql);
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel do Cliente - Agendamento</title>
    <link rel="stylesheet" href="../css/style.css">
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
                        <li><a href="meus_agendamentos.php">Meus Agendamentos</a></li>
                        <li><a href="../logout.php" class="btn btn-danger">Sair</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="container">
        <h1>Painel do Cliente</h1>
        <p>Bem-vindo(a), <?= htmlspecialchars($_SESSION['usuario_nome']) ?>!</p>
        
        <hr>

        <h2>Nossos Serviços</h2>
        <p>Escolha um serviço abaixo para ver os horários disponíveis.</p>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="service-list">
                <?php while ($service = $result->fetch_assoc()): ?>
                    <div class="service-card">
                        <h3><?= htmlspecialchars($service['name']) ?></h3>
                        <p><?= htmlspecialchars($service['description']) ?></p>
                        <p>
                            <strong>Preço:</strong> R$ <?= number_format($service['price'], 2, ',', '.') ?>
                        </p>
                        <a class="btn btn-success" href="agendar.php?service_id=<?= $service['id'] ?>">Agendar</a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>Nenhum serviço disponível no momento.</p>
        <?php endif; ?>
    </main>

</body>
</html>