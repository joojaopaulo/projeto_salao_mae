<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

$user_id = $_SESSION['usuario_id'];
$mensagem_sucesso = '';

$conn = new mysqli("localhost", "root", "", "beauty_saloon_schedules");
if ($conn->connect_error) { die("Erro de conexão: " . $conn->connect_error); }

if (isset($_GET['cancelar_id'])) {
    $agendamento_id = $_GET['cancelar_id'];
    $stmt = $conn->prepare("UPDATE agendamentos SET status = 'cancelado' WHERE id = ? AND id_cliente = ?");
    $stmt->bind_param("ii", $agendamento_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: meus_agendamentos.php?status=cancelado");
    exit;
}

if (isset($_GET['status']) && $_GET['status'] == 'cancelado') {
    $mensagem_sucesso = "Agendamento cancelado com sucesso!";
}

$sql = "SELECT a.id, s.name AS servico_nome, s.description AS servico_descricao, a.data, a.hora, a.status
        FROM agendamentos AS a
        JOIN services AS s ON a.id_servico = s.id
        WHERE a.id_cliente = ?
        ORDER BY a.data DESC, a.hora ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Meus Agendamentos</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .agendamento-info {
            border-top: 1px solid #e0e0e0;
            margin-top: 15px;
            padding-top: 15px;
            font-weight: bold;
        }
    </style>
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
                        <li><a href="index.php">Agendar Novo</a></li>
                        <li><a href="../logout.php" class="btn btn-danger">Sair</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="container">
        <h1>Meus Agendamentos</h1>

        <?php if($mensagem_sucesso): ?>
            <p class="alert alert-success"><?= $mensagem_sucesso ?></p>
        <?php endif; ?>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="service-list">
                <?php while ($agendamento = $result->fetch_assoc()): ?>
                    <div class="service-card">
                        <h3><?= htmlspecialchars($agendamento['servico_nome']) ?></h3>
                        <p><?= htmlspecialchars($agendamento['servico_descricao']) ?></p>
                        
                        <div class="agendamento-info">
                            <p><strong>Data:</strong> <?= date('d/m/Y', strtotime($agendamento['data'])) ?> às <?= htmlspecialchars(substr($agendamento['hora'], 0, 5)) ?></p>
                            <p><strong>Status:</strong> <span class="status-badge status-<?= htmlspecialchars($agendamento['status']) ?>"><?= ucfirst($agendamento['status']) ?></span></p>
                        </div>

                        <?php if ($agendamento['status'] == 'agendado'): ?>
                            <a href="meus_agendamentos.php?cancelar_id=<?= $agendamento['id'] ?>" class="btn btn-danger" style="margin-top: 15px;" onclick="return confirm('Tem certeza que deseja cancelar este agendamento?')">
                                Cancelar Agendamento
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p>Você ainda não possui agendamentos.</p>
        <?php endif; ?>
    </main>

</body>
</html>