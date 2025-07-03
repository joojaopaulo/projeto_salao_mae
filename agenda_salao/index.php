<?php
session_start();
$conn = new mysqli("localhost", "root", "", "beauty_saloon_schedules");
if ($conn->connect_error) { 
    die("Erro de conexão com o banco de dados: " . $conn->connect_error); 
}

$sql = "SELECT name, description, price, duration FROM services ORDER BY name ASC";
$result = $conn->query($sql);

if (!$result) {
    // Em um caso real, você poderia registrar esse erro em um log.
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salão de Beleza - Bem-vindo!</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <a href="index.php">
                    <img src="img/logo.png" alt="Logo do Salão" class="logo">
                </a>
                <nav class="main-nav">
                    <ul>
                        <li><a href="#servicos">Serviços</a></li>
                        <li><a href="https://wa.me/5555999272404" target="_blank" rel="noopener noreferrer">Contato</a></li>
                        <?php if (isset($_SESSION['usuario_id'])): ?>
                            <li><a href="<?php echo $_SESSION['usuario_tipo'] == 'admin' ? 'admin/index.php' : 'user/index.php'; ?>">Meu Painel</a></li>
                            <li><a href="logout.php" class="btn btn-danger">Sair</a></li>
                        <?php else: ?>
                            <li><a href="login.php" class="btn btn-primary">Agendar Agora</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container">
                <h1>Realce sua beleza, renove sua autoestima.</h1>
                <p>Oferecemos os melhores serviços para cuidar de você. Agende seu horário de forma rápida e fácil.</p>
                <a href="<?php echo isset($_SESSION['usuario_id']) ? 'user/index.php' : 'login.php'; ?>" class="btn btn-success" style="font-size: 1.2em; padding: 15px 30px;">Agendar Meu Horário</a>
            </div>
        </section>

        <section id="servicos" class="services-section">
            <div class="container">
                <h2>Nossos Serviços</h2>
                <div class="service-list">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($service = $result->fetch_assoc()): ?>
                            <div class="service-card">
                                <?php
                                    $base_image_name = str_replace(' ', '_', strtolower($service['name']));
                                    $possible_extensions = ['.jpg', '.png', '.jpeg', '.webp'];
                                    $image_path = '';
                                    foreach ($possible_extensions as $ext) {
                                        $temp_path = 'img/services/' . $base_image_name . $ext;
                                        if (file_exists($temp_path)) {
                                            $image_path = $temp_path;
                                            break;
                                        }
                                    }
                                ?>
                                <?php if ($image_path): ?>
                                    <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($service['name']) ?>">
                                <?php endif; ?>
                                
                                <h3><?= htmlspecialchars($service['name']) ?></h3>
                                <p><?= htmlspecialchars($service['description']) ?></p>
                                <p><strong>Preço:</strong> R$ <?= number_format($service['price'], 2, ',', '.') ?></p>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>Nenhum serviço disponível no momento.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <footer id="contato" class="site-footer">
        <div class="container">
            <p>&copy; 2025 Salão de Beleza. Todos os direitos reservados.</p>
            <p>Rua João Carlos Machado, 251 - Bairro Centro - Cidade Iraí-RS</p>
        </div>
    </footer>

    <?php $conn->close(); // A conexão é fechada aqui, uma única vez e no lugar certo. ?>
</body>
</html> 