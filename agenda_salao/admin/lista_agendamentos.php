<?php
session_start();

// Segurança: Apenas admins podem acessar
if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "beauty_saloon_schedules");
if ($conn->connect_error) { die("Erro de conexão: " . $conn->connect_error); }

$mensagem = '';
$tipo_mensagem = '';

// Lógica para alterar o status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alterar_status'])) {
    $agendamento_id = $_POST['agendamento_id'];
    $novo_status = $_POST['novo_status'];

    $stmt_update = $conn->prepare("UPDATE agendamentos SET status = ? WHERE id = ?");
    $stmt_update->bind_param("si", $novo_status, $agendamento_id);
    if ($stmt_update->execute()) {
        $mensagem = "Status atualizado com sucesso!";
        $tipo_mensagem = "sucesso";
    } else {
        $mensagem = "Erro ao atualizar o status.";
        $tipo_mensagem = "erro";
    }
    $stmt_update->close();
}

if(isset($_GET['sucesso']) && $_GET['sucesso'] == 1 && empty($mensagem)) {
    $mensagem = "Status atualizado com sucesso!";
    $tipo_mensagem = "sucesso";
}

// --- CONSULTAS SQL PARA OS CARDS DE RESUMO ---
$result_hoje = $conn->query("SELECT COUNT(id) AS total FROM agendamentos WHERE data = CURDATE() AND status != 'cancelado'");
$agendamentos_hoje = $result_hoje->fetch_assoc()['total'];

$result_faturamento = $conn->query("SELECT SUM(s.price) AS total FROM agendamentos a JOIN services s ON a.id_servico = s.id WHERE a.status = 'concluido' AND MONTH(a.data) = MONTH(CURDATE()) AND YEAR(a.data) = YEAR(CURDATE())");
$faturamento_mes = $result_faturamento->fetch_assoc()['total'];
$faturamento_mes = $faturamento_mes ? $faturamento_mes : 0;

$result_pendentes = $conn->query("SELECT COUNT(id) AS total FROM agendamentos WHERE status = 'agendado'");
$agendamentos_pendentes = $result_pendentes->fetch_assoc()['total'];

$result_clientes = $conn->query("SELECT COUNT(id) AS total FROM users WHERE roles = 'cliente'");
$total_clientes = $result_clientes->fetch_assoc()['total'];


// --- Consulta para listar os agendamentos ---
$sql = "SELECT a.id, a.data, a.hora, a.status, u.email AS cliente_email, s.name AS servico_nome
        FROM agendamentos AS a
        JOIN users AS u ON a.id_cliente = u.id
        JOIN services AS s ON a.id_servico = s.id
        ORDER BY a.data DESC, a.hora ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Agendamentos</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <a href="../index.php"><img src="../img/logo.png" alt="Logo do Salão" class="logo"></a>
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
        <h1>Gerenciar Agendamentos</h1>

        <?php if($mensagem): ?>
            <p class="alert <?= $tipo_mensagem == 'sucesso' ? 'alert-success' : 'alert-danger' ?>"><?= htmlspecialchars($mensagem) ?></p>
        <?php endif; ?>

        <div class="dashboard-menu" style="margin-top: 20px; margin-bottom: 20px;">
            <div class="dashboard-card stat-card">
                <div class="stat-card-number"><?= $agendamentos_hoje ?></div>
                <div class="stat-card-label">Agendamentos Hoje</div>
            </div>
             <div class="dashboard-card stat-card">
                <div class="stat-card-number">R$ <?= number_format($faturamento_mes, 2, ',', '.') ?></div>
                <div class="stat-card-label">Faturamento do Mês</div>
            </div>
             <div class="dashboard-card stat-card">
                <div class="stat-card-number"><?= $agendamentos_pendentes ?></div>
                <div class="stat-card-label">Agendamentos Pendentes</div>
            </div>
             <div class="dashboard-card stat-card">
                <div class="stat-card-number"><?= $total_clientes ?></div>
                <div class="stat-card-label">Total de Clientes</div>
            </div>
        </div>

        <hr>
        <h2>Todos os Agendamentos</h2>

        <div class="scheduling-grid">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($agendamento = $result->fetch_assoc()): ?>
                    <div class="scheduling-card">
                        <div class="scheduling-card-header">
                            <h4><?= htmlspecialchars($agendamento['servico_nome']) ?></h4>
                        </div>
                        <div class="scheduling-card-body">
                            <div class="scheduling-card-info">
                                <span>Cliente: <?= htmlspecialchars($agendamento['cliente_email']) ?></span>
                                <span>Data: <?= date('d/m/Y', strtotime($agendamento['data'])) ?></span>
                            </div>
                            <div class="scheduling-card-info">
                                <span>Hora: <?= date('H:i', strtotime($agendamento['hora'])) ?></span>
                            </div>
                            <div class="scheduling-card-status">
                                <span class="status-badge status-<?= htmlspecialchars($agendamento['status']) ?>">
                                    <?= htmlspecialchars($agendamento['status']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="scheduling-card-actions">
                            <form method="POST" action="lista_agendamentos.php">
                                <input type="hidden" name="agendamento_id" value="<?= $agendamento['id'] ?>">
                                <select name="novo_status" class="form-control" style="width: auto;">
                                    <option value="agendado" <?= $agendamento['status'] == 'agendado' ? 'selected' : '' ?>>Agendado</option>
                                    <option value="concluido" <?= $agendamento['status'] == 'concluido' ? 'selected' : '' ?>>Concluído</option>
                                    <option value="cancelado" <?= $agendamento['status'] == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                                </select>
                                <button type="submit" name="alterar_status" class="btn btn-primary btn-sm">Salvar</button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Nenhum agendamento encontrado.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
<?php $conn->close(); ?>