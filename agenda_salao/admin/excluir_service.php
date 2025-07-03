<?php
session_start();

// 1. VERIFICAÇÕES DE SEGURANÇA
// Garante que o usuário é um admin e que um ID foi passado
if (!isset($_SESSION['usuario_tipo']) || $_SESSION['usuario_tipo'] !== 'admin') {
    die("Acesso negado.");
}
if (!isset($_GET['id'])) {
    header("Location: service_list.php");
    exit;
}

$service_id = $_GET['id'];

// 2. CONEXÃO E EXECUÇÃO DO DELETE
$conn = new mysqli("localhost", "root", "", "beauty_saloon_schedules");
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

$stmt = $conn->prepare("DELETE FROM services WHERE id = ?");
$stmt->bind_param("i", $service_id);
$stmt->execute();
$stmt->close();
$conn->close();

// 3. REDIRECIONAMENTO
// Após deletar, volta para a lista de serviços
header("Location: service_list.php?exclusao=sucesso");
exit;