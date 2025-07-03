<?php
session_start();

if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "beauty_saloon_schedules");
if ($conn->connect_error) { die("Erro de conexão: " . $conn->connect_error); }

$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : null;
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : null;

$agendamentos = [];
$total_agendamentos_periodo = 0;
$faturamento_periodo = 0;

if ($data_inicio && $data_fim) {
    $sql_resumo = "SELECT COUNT(a.id) AS total, SUM(CASE WHEN a.status = 'concluido' THEN s.price ELSE 0 END) AS faturamento FROM agendamentos a JOIN services s ON a.id_servico = s.id WHERE a.data BETWEEN ? AND ?";
    $stmt_resumo = $conn->prepare($sql_resumo);
    $stmt_resumo->bind_param("ss", $data_inicio, $data_fim);
    $stmt_resumo->execute();
    $result_resumo = $stmt_resumo->get_result()->fetch_assoc();
    $total_agendamentos_periodo = $result_resumo['total'];
    $faturamento_periodo = $result_resumo['faturamento'] ? $result_resumo['faturamento'] : 0;
    $stmt_resumo->close();

    $sql_detalhado = "SELECT a.id, a.data, a.hora, a.status, u.email AS cliente_email, s.name AS servico_nome, s.price FROM agendamentos AS a JOIN users AS u ON a.id_cliente = u.id JOIN services AS s ON a.id_servico = s.id WHERE a.data BETWEEN ? AND ? ORDER BY a.data ASC, a.hora ASC";
    $stmt_detalhado = $conn->prepare($sql_detalhado);
    $stmt_detalhado->bind_param("ss", $data_inicio, $data_fim);
    $stmt_detalhado->execute();
    $agendamentos = $stmt_detalhado->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_detalhado->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatórios - Painel Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/print.css" media="print">
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
                        <li><a href="index.php">Painel Principal</a></li>
                        <li><a href="../logout.php" class="btn btn-danger">Sair</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="container">
        <h1>Relatório de Agendamentos</h1>
        <p>Selecione um período para gerar um relatório de agendamentos e faturamento.</p>

        <form method="GET" action="" class="filter-form" style="background-color: #f9f9f9; padding: 20px; border-radius: 8px;">
            <div class="form-grid">
                <div class="form-group">
                    <label for="data_inicio">Data de Início:</label>
                    <input type="text" id="data_inicio" name="data_inicio" required value="<?= htmlspecialchars($data_inicio ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="data_fim">Data de Fim:</label>
                    <input type="text" id="data_fim" name="data_fim" required value="<?= htmlspecialchars($data_fim ?? '') ?>">
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top: 20px;">Gerar Relatório</button>
        </form>

        <hr>

        <div id="report-content">
            <?php if ($data_inicio && $data_fim): ?>
                <h2>Relatório para o período de <?= date('d/m/Y', strtotime($data_inicio)) ?> a <?= date('d/m/Y', strtotime($data_fim)) ?></h2>
                <div class="dashboard-menu" style="margin-top: 20px; margin-bottom: 20px;">
                    <div class="dashboard-card stat-card">
                        <div class="stat-card-number"><?= $total_agendamentos_periodo ?></div>
                        <div class="stat-card-label">Total de Agendamentos</div>
                    </div>
                    <div class="dashboard-card stat-card">
                        <div class="stat-card-number">R$ <?= number_format($faturamento_periodo, 2, ',', '.') ?></div>
                        <div class="stat-card-label">Faturamento (Concluídos)</div>
                    </div>
                </div>

                <h3>Detalhes dos Agendamentos</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Hora</th>
                            <th>Cliente</th>
                            <th>Serviço</th>
                            <th>Valor (R$)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($agendamentos) > 0): ?>
                            <?php foreach ($agendamentos as $agendamento): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($agendamento['data'])) ?></td>
                                    <td><?= date('H:i', strtotime($agendamento['hora'])) ?></td>
                                    <td><?= htmlspecialchars($agendamento['cliente_email']) ?></td>
                                    <td><?= htmlspecialchars($agendamento['servico_nome']) ?></td>
                                    <td><?= number_format($agendamento['price'], 2, ',', '.') ?></td>
                                    <td><span class="status-badge status-<?= $agendamento['status'] ?>"><?= $agendamento['status'] ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6">Nenhum agendamento encontrado para este período.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <div style="text-align: center; margin-top: 30px;" class="print-button">
                    <button onclick="window.print()" class="btn btn-success">Salvar Relatório como PDF</button>
                </div>

            <?php else: ?>
                <p>Nenhum período selecionado.</p>
            <?php endif; ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/pt.js"></script>
    <script>
        const config = { locale: "pt", dateFormat: "Y-m-d", altInput: true, altFormat: "d/m/Y", };
        flatpickr("#data_inicio", config);
        flatpickr("#data_fim", config);
    </script>
</body>
</html>