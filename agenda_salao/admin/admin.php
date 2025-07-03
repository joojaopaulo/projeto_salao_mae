<?php
session_start();

// Verifica se está logado e é admin
if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

echo "<h1>Painel do Administrador</h1>";
echo "<p>Bem-vindo, " . htmlspecialchars($_SESSION['usuario_nome']) . "</p>";
echo '<p><a href="../logout.php">Sair</a></p>';

// Conexão com o banco
$conn = new mysqli("localhost", "root", "", "beauty_saloon_schedules");
if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Busca agendamentos (ajuste os nomes de tabela e campos conforme seu BD)
$sql = "SELECT id, cliente_nome, servico, data_hora FROM agendamentos ORDER BY data_hora DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo "<h2>Agendamentos</h2>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>ID</th><th>Cliente</th><th>Serviço</th><th>Data e Hora</th></tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['cliente_nome']) . "</td>";
        echo "<td>" . htmlspecialchars($row['servico']) . "</td>";
        echo "<td>" . $row['data_hora'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Nenhum agendamento encontrado.</p>";
}

$conn->close();
