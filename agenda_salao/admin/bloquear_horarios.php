<?php
session_start();

// Segurança: Apenas admins podem acessar
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Conexão com o banco de dados
$conn = new mysqli("localhost", "root", "", "beauty_saloon_schedules");
if ($conn->connect_error) { die("Erro de conexão: " . $conn->connect_error); }

$mensagem = "";
$tipo_mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Se a ação for para ADICIONAR um bloqueio
    if (isset($_POST['add_block'])) {
        $date = $_POST['date'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        $reason = !empty($_POST['reason']) ? $_POST['reason'] : 'Bloqueado';

        // CORREÇÃO: Alterado para o nome da sua tabela (horarios_bloqueados) e colunas (data, hora_inicio, hora_fim, motivo)
        $sql = "INSERT INTO horarios_bloqueados (data, hora_inicio, hora_fim, motivo) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        // O bind_param continua o mesmo, pois as variáveis PHP não mudaram
        $stmt->bind_param("ssss", $date, $start_time, $end_time, $reason);

        if ($stmt->execute()) {
            $mensagem = "Horário bloqueado com sucesso!";
            $tipo_mensagem = "sucesso";
        } else {
            $mensagem = "Erro ao bloquear horário: " . $stmt->error;
            $tipo_mensagem = "erro";
        }
        $stmt->close();
    }

    // Se a ação for para REMOVER um bloqueio
    if (isset($_POST['remove_block'])) {
        $block_id_to_remove = $_POST['block_id'];
        
        // CORREÇÃO: Alterado para o nome da sua tabela
        $sql = "DELETE FROM horarios_bloqueados WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $block_id_to_remove);

        if ($stmt->execute()) {
            $mensagem = "Bloqueio removido com sucesso!";
            $tipo_mensagem = "sucesso";
        } else {
            $mensagem = "Erro ao remover bloqueio: " . $stmt->error;
            $tipo_mensagem = "erro";
        }
        $stmt->close();
    }
}

// CORREÇÃO: Alterado para o nome da sua tabela
$lista_bloqueios = $conn->query("SELECT * FROM horarios_bloqueados ORDER BY data, hora_inicio ASC");

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Horários Bloqueados</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/material_blue.css">
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
        <h1>Gerenciar Horários Bloqueados</h1>
        <?php if ($mensagem): ?>
            <p class="alert <?= $tipo_mensagem == 'sucesso' ? 'alert-success' : 'alert-danger' ?>"><?= htmlspecialchars($mensagem) ?></p>
        <?php endif; ?>
        <hr>
        <h2>Adicionar Novo Bloqueio</h2>
        
        <form method="POST" action="">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="date">Data:</label>
                    <input type="text" id="date" name="date" required placeholder="Selecione a data...">
                </div>
                <div class="form-group">
                    <label for="start_time">De:</label>
                    <input type="time" id="start_time" name="start_time" required>
                </div>
                <div class="form-group">
                    <label for="end_time">Até:</label>
                    <input type="time" id="end_time" name="end_time" required>
                </div>
                <div class="form-group full-width">
                    <label for="reason">Motivo (opcional):</label>
                    <input type="text" id="reason" name="reason" placeholder="Ex: Almoço, Reunião, etc.">
                </div>
            </div>
            <button type="submit" name="add_block" class="btn btn-primary" style="margin-top: 20px;">Bloquear Horário</button>
        </form>

        <hr style="margin-top: 40px;">
        <h2>Horários Já Bloqueados</h2>
        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Início</th>
                    <th>Fim</th>
                    <th>Motivo</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($lista_bloqueios && $lista_bloqueios->num_rows > 0): ?>
                    <?php while($block = $lista_bloqueios->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars(date("d/m/Y", strtotime($block['data']))) ?></td>
                            <td><?= htmlspecialchars(date("H:i", strtotime($block['hora_inicio']))) ?></td>
                            <td><?= htmlspecialchars(date("H:i", strtotime($block['hora_fim']))) ?></td>
                            <td><?= htmlspecialchars($block['motivo']) ?></td>
                            <td>
                                <form method="POST" action="" style="margin: 0;">
                                    <input type="hidden" name="block_id" value="<?= $block['id'] ?>">
                                    <button type="submit" name="remove_block" class="btn btn-danger">Remover</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5">Nenhum horário bloqueado encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/pt.js"></script>
    <script>
        flatpickr("#date", {
            locale: "pt",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d/m/Y",
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>