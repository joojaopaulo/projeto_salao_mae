<?php
session_start();

// Segurança: Apenas admins podem acessar
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

// Conexão com o banco de dados
$conn = new mysqli("localhost", "root", "", "beauty_saloon_schedules");
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// --- CONSULTAS PARA OS CARDS DE RESUMO ---
$result_total = $conn->query("SELECT COUNT(id) AS total FROM services");
$total_servicos = $result_total->fetch_assoc()['total'];

$result_avg_price = $conn->query("SELECT AVG(price) AS media FROM services");
$preco_medio = $result_avg_price->fetch_assoc()['media'];
$preco_medio = $preco_medio ? $preco_medio : 0;

$result_mais_caro = $conn->query("SELECT name, price FROM services ORDER BY price DESC LIMIT 1");
$servico_mais_caro = $result_mais_caro->fetch_assoc();

$sql_popular = "SELECT s.name, COUNT(a.id) AS total_agendamentos 
                FROM agendamentos a 
                JOIN services s ON a.id_servico = s.id 
                WHERE MONTH(a.data) = MONTH(CURDATE()) AND YEAR(a.data) = YEAR(CURDATE()) AND a.status != 'cancelado' 
                GROUP BY s.name 
                ORDER BY total_agendamentos DESC 
                LIMIT 1";
$result_mais_popular = $conn->query($sql_popular);
$servico_mais_popular = $result_mais_popular->fetch_assoc();

// --- Consulta principal para a lista de serviços ---
$lista_servicos = $conn->query("SELECT * FROM services ORDER BY name ASC");

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Serviços - Painel Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        <h1>Gerenciar Serviços</h1>
        
        <div class="dashboard-menu" style="margin-top: 20px; margin-bottom: 20px;">
            <div class="dashboard-card stat-card">
                <div class="stat-card-number"><?= $total_servicos ?></div>
                <div class="stat-card-label">Serviços Totais</div>
            </div>
             <div class="dashboard-card stat-card">
                <div class="stat-card-number">R$ <?= number_format($preco_medio, 2, ',', '.') ?></div>
                <div class="stat-card-label">Preço Médio</div>
            </div>
             <div class="dashboard-card stat-card">
                <div class="stat-card-number" style="font-size: 1.5rem;"><?= $servico_mais_caro ? htmlspecialchars($servico_mais_caro['name']) : 'N/A' ?></div>
                <div class="stat-card-label">Serviço Mais Caro</div>
            </div>
             <div class="dashboard-card stat-card">
                <div class="stat-card-number" style="font-size: 1.5rem;"><?= $servico_mais_popular ? htmlspecialchars($servico_mais_popular['name']) : 'N/A' ?></div>
                <div class="stat-card-label">Mais Popular do Mês</div>
            </div>
        </div>
        <hr>
        
        <a href="service_form.php" class="btn btn-success" style="margin-bottom: 20px;">Cadastrar Novo Serviço</a>

        <div class="item-grid">
            <?php if ($lista_servicos && $lista_servicos->num_rows > 0): ?>
                <?php while($service = $lista_servicos->fetch_assoc()): ?>
                    <div class="item-card">
                        <div class="item-card-header">
                            <h3><?= htmlspecialchars($service['name']) ?></h3>
                        </div>
                        <div class="item-card-body">
                            <p><?= htmlspecialchars($service['description']) ?></p>
                            <div class="item-card-details">
                                <span>Preço: R$ <?= number_format($service['price'], 2, ',', '.') ?></span>
                                <span>Duração: <?= htmlspecialchars($service['duration']) ?> min</span>
                            </div>
                        </div>
                        <div class="item-card-actions">
                            <a href="service_form.php?id=<?= $service['id'] ?>" class="btn btn-primary">Editar</a>
                            <a href="service_delete.php?id=<?= $service['id'] ?>" 
                               class="btn btn-danger" 
                               onclick="return confirm('Tem certeza que deseja excluir este serviço? Esta ação não pode ser desfeita.');">
                               Excluir
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>Nenhum serviço cadastrado ainda.</p>
            <?php endif; ?>
        </div>
        </main>

</body>
</html>
<?php
$conn->close();
?>