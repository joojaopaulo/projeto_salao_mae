<?php
session_start();

// Verifica se o usuário é admin
if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel do Administrador</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
        <h1>Painel do Administrador</h1>
        <p>Bem-vindo(a), <?= htmlspecialchars($_SESSION['usuario_nome']) ?>!</p>
        
        <hr>
        
        <h3>Menu de Gerenciamento</h3>

        <div class="dashboard-menu">
    <a href="lista_agendamentos.php" class="dashboard-card">
        <i class="fas fa-calendar-alt card-icon"></i>
        <span class="card-title">Gerenciar Agendamentos</span>
    </a>
    
    <a href="service_list.php" class="dashboard-card">
        <i class="fas fa-cut card-icon"></i>
        <span class="card-title">Gerenciar Serviços</span>
    </a>

    <a href="bloquear_horarios.php" class="dashboard-card">
        <i class="fas fa-clock card-icon"></i>
        <span class="card-title">Gerenciar Horários</span>
    </a>

    <a href="gerenciar_usuarios.php" class="dashboard-card">
        <i class="fas fa-users-cog card-icon"></i>
        <span class="card-title">Gerenciar Usuários</span>
    </a>
    
    <a href="relatorios.php" class="dashboard-card">
        <i class="fas fa-chart-bar card-icon"></i>
        <span class="card-title">Gerar Relatórios</span>
    </a>
</div>
    </main>

</body>
</html>