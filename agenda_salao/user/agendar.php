<?php
session_start();

if (!isset($_SESSION['usuario_id'])) { header("Location: ../login.php"); exit; }
if (!isset($_GET['service_id'])) { header("Location: index.php?error=serviconaoselecionado"); exit; }

$service_id = $_GET['service_id'];
$user_id = $_SESSION['usuario_id'];
$data_selecionada = isset($_GET['data']) ? $_GET['data'] : null;
$mensagem_sucesso = '';
$mensagem_erro = '';

$conn = new mysqli("localhost", "root", "", "beauty_saloon_schedules");
if ($conn->connect_error) { die("Erro de conexão: " . $conn->connect_error); }

$stmt_service = $conn->prepare("SELECT name, duration FROM services WHERE id = ?");
$stmt_service->bind_param("i", $service_id);
$stmt_service->execute();
$result_service = $stmt_service->get_result();
if ($result_service->num_rows == 0) { header("Location: index.php?error=servicoraro"); exit; }
$service = $result_service->fetch_assoc();
$stmt_service->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_horario'])) {
    $horario_agendado = $_POST['horario'];
    $data_agendada = $_POST['data'];
    $stmt_insert = $conn->prepare("INSERT INTO agendamentos (id_cliente, id_servico, data, hora, status) VALUES (?, ?, ?, ?, 'agendado')");
    $stmt_insert->bind_param("iiss", $user_id, $service_id, $data_agendada, $horario_agendado);
    if ($stmt_insert->execute()) {
        $mensagem_sucesso = "Seu horário foi agendado com sucesso para o dia " . date('d/m/Y', strtotime($data_agendada)) . " às " . substr($horario_agendado, 0, 5) . "!";
    } else {
        $mensagem_erro = "Ocorreu um erro ao tentar agendar.";
    }
    $stmt_insert->close();
}

function gerarHorariosDisponiveis($conn, $data, $duracao_servico) {
    $horario_abertura = new DateTime('09:00'); $horario_fechamento = new DateTime('18:00'); $horario_almoco_inicio = new DateTime('12:00'); $horario_almoco_fim = new DateTime('13:00');
    $stmt_agendamentos = $conn->prepare("SELECT hora, s.duration FROM agendamentos a JOIN services s ON a.id_servico = s.id WHERE a.data = ? AND a.status != 'cancelado'"); $stmt_agendamentos->bind_param("s", $data); $stmt_agendamentos->execute(); $result_agendamentos = $stmt_agendamentos->get_result();
    $horarios_ocupados = []; while ($row = $result_agendamentos->fetch_assoc()) { $inicio = new DateTime($row['hora']); $duracao = $row['duration']; $fim = (clone $inicio)->add(new DateInterval("PT{$duracao}M")); $horarios_ocupados[] = ['inicio' => $inicio, 'fim' => $fim]; } $stmt_agendamentos->close();
    $stmt_bloqueados = $conn->prepare("SELECT hora_inicio, hora_fim FROM horarios_bloqueados WHERE data = ?"); $stmt_bloqueados->bind_param("s", $data); $stmt_bloqueados->execute(); $result_bloqueados = $stmt_bloqueados->get_result();
    $periodos_bloqueados = []; while ($row = $result_bloqueados->fetch_assoc()) { $periodos_bloqueados[] = [ 'inicio' => new DateTime($row['hora_inicio']), 'fim' => new DateTime($row['hora_fim']) ]; } $stmt_bloqueados->close();
    $intervalo = new DateInterval("PT{$duracao_servico}M"); $horarios_disponiveis = []; $horario_atual = clone $horario_abertura;
    while ($horario_atual < $horario_fechamento) {
        $fim_horario_atual = (clone $horario_atual)->add($intervalo); if ($fim_horario_atual > $horario_fechamento) break;
        $disponivel = true;
        if (($horario_atual >= $horario_almoco_inicio && $horario_atual < $horario_almoco_fim) || ($fim_horario_atual > $horario_almoco_inicio && $fim_horario_atual <= $horario_almoco_fim)) { $disponivel = false; }
        if ($disponivel) { foreach ($horarios_ocupados as $ocupado) { if (($horario_atual >= $ocupado['inicio'] && $horario_atual < $ocupado['fim']) || ($fim_horario_atual > $ocupado['inicio'] && $fim_horario_atual <= $ocupado['fim'])) { $disponivel = false; break; } } }
        if ($disponivel) { foreach ($periodos_bloqueados as $bloqueio) { if (($horario_atual >= $bloqueio['inicio'] && $horario_atual < $bloqueio['fim']) || ($fim_horario_atual > $bloqueio['inicio'] && $fim_horario_atual <= $bloqueio['fim'])) { $disponivel = false; break; } } }
        if ($disponivel) { $horarios_disponiveis[] = $horario_atual->format('H:i'); }
        $horario_atual->add($intervalo);
    } return $horarios_disponiveis;
}

$horarios_disponiveis = [];
if ($data_selecionada && !$mensagem_sucesso) { $horarios_disponiveis = gerarHorariosDisponiveis($conn, $data_selecionada, $service['duration']); }
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Agendar Horário</title>
    <link rel="stylesheet" href="../css/style.css">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <a href="../index.php"><img src="../img/logo.png" alt="Logo do Salão" class="logo"></a>
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
        <h1>Agendar Horário</h1>
        <p><strong>Serviço:</strong> <?= htmlspecialchars($service['name']) ?> (Duração: <?= $service['duration'] ?> minutos)</p>
        <hr>

        <?php if ($mensagem_sucesso): ?>
            <p class="alert alert-success"><?= htmlspecialchars($mensagem_sucesso) ?></p>
            <a href="meus_agendamentos.php" class="btn btn-primary">Ver meus agendamentos</a>
            <a href="index.php" class="btn">Agendar outro serviço</a>
        <?php elseif ($mensagem_erro): ?>
            <p class="alert alert-danger"><?= htmlspecialchars($mensagem_erro) ?></p>
        <?php else: ?>
            <form method="GET" action="agendar.php" style="display: flex; align-items: flex-end; gap: 15px; background-color: #f9f9f9; padding: 20px; border-radius: 8px;">
                <input type="hidden" name="service_id" value="<?= $service_id ?>">
                <div style="flex-grow: 1;">
                    <label for="data">Escolha uma data para o agendamento:</label>
                    <input type="text" id="data" name="data" value="<?= htmlspecialchars($data_selecionada) ?>" required placeholder="Clique para escolher a data...">
                </div>
                <button type="submit" class="btn btn-primary">Verificar Horários</button>
            </form>

            <?php if ($data_selecionada): ?>
                <hr><h3>Horários disponíveis para <?= date('d/m/Y', strtotime($data_selecionada)) ?>:</h3>
                <?php if (count($horarios_disponiveis) > 0): ?>
                    <form method="POST" action="agendar.php?service_id=<?= $service_id ?>">
                        <input type="hidden" name="data" value="<?= $data_selecionada ?>">
                        <input type="hidden" name="confirmar_horario" value="1">
                        <div class="time-slot-list">
                            <?php foreach ($horarios_disponiveis as $horario): ?>
                                <button type="submit" name="horario" value="<?= $horario ?>" class="time-slot-btn">
                                    <div class="time-slot-card">
                                        <span class="time"><?= $horario ?></span>
                                        <span class="status">Agendar</span>
                                    </div>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </form>
                <?php else: ?>
                    <p class="alert alert-danger" style="margin-top: 20px;">Não há horários disponíveis para esta data.</p>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/pt.js"></script>
    <script>
        flatpickr("#data", {
            locale: "pt", // Traduz para Português
            dateFormat: "Y-m-d", // Formato que o PHP entende
            altInput: true, // Cria um campo de input visual amigável
            altFormat: "d/m/Y", // Formato que o usuário vê
            minDate: "today", // Impede a seleção de datas passadas
        });
    </script>
</body>
</html>